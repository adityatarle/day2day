<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Branch;
use App\Models\User;
use App\Models\StockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StockTransferService
{
    /**
     * Create a new stock transfer from admin to branch
     */
    public function createStockTransfer(array $transferData, array $items, User $admin): StockTransfer
    {
        DB::beginTransaction();

        try {
            // Create the stock transfer
            $transfer = StockTransfer::create([
                'to_branch_id' => $transferData['to_branch_id'],
                'from_branch_id' => $transferData['from_branch_id'] ?? null,
                'initiated_by' => $admin->id,
                'transport_vendor' => $transferData['transport_vendor'] ?? null,
                'vehicle_number' => $transferData['vehicle_number'] ?? null,
                'driver_name' => $transferData['driver_name'] ?? null,
                'driver_phone' => $transferData['driver_phone'] ?? null,
                'expected_delivery' => $transferData['expected_delivery'] ?? null,
                'dispatch_notes' => $transferData['dispatch_notes'] ?? null,
                'transport_cost' => $transferData['transport_cost'] ?? 0,
            ]);

            $totalValue = 0;

            // Add items to the transfer
            foreach ($items as $itemData) {
                $item = $transfer->items()->create([
                    'product_id' => $itemData['product_id'],
                    'batch_id' => $itemData['batch_id'] ?? null,
                    'quantity_sent' => $itemData['quantity_sent'],
                    'unit_price' => $itemData['unit_price'],
                    'unit_of_measurement' => $itemData['unit_of_measurement'] ?? 'kg',
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'item_notes' => $itemData['item_notes'] ?? null,
                ]);

                $totalValue += $item->total_value;

                // Create outgoing stock movement if from a branch
                if ($transfer->from_branch_id) {
                    $this->createStockMovement(
                        $itemData['product_id'],
                        $transfer->from_branch_id,
                        $itemData['batch_id'] ?? null,
                        'transfer_out',
                        $itemData['quantity_sent'],
                        $itemData['unit_price'],
                        "Stock transfer to {$transfer->toBranch->name}: {$transfer->transfer_number}",
                        $admin->id,
                        $transfer->id
                    );
                }
            }

            // Update total value
            $transfer->update(['total_value' => $totalValue]);

            DB::commit();

            Log::info("Stock transfer created", [
                'transfer_id' => $transfer->id,
                'transfer_number' => $transfer->transfer_number,
                'admin_id' => $admin->id,
                'to_branch_id' => $transfer->to_branch_id,
            ]);

            return $transfer;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to create stock transfer", [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
                'transfer_data' => $transferData,
            ]);
            throw $e;
        }
    }

    /**
     * Dispatch stock transfer (mark as in transit)
     */
    public function dispatchTransfer(StockTransfer $transfer, array $dispatchData = []): bool
    {
        try {
            $updateData = array_merge([
                'status' => 'in_transit',
                'dispatch_date' => now(),
            ], $dispatchData);

            $result = $transfer->update($updateData);

            if ($result) {
                Log::info("Stock transfer dispatched", [
                    'transfer_id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                ]);

                // Create alert for branch about incoming transfer
                StockAlert::create([
                    'branch_id' => $transfer->to_branch_id,
                    'stock_transfer_id' => $transfer->id,
                    'alert_type' => 'transfer_delay',
                    'severity' => 'info',
                    'title' => 'Incoming Stock Transfer',
                    'message' => "Stock transfer {$transfer->transfer_number} has been dispatched and is in transit.",
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to dispatch stock transfer", [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Mark transfer as delivered (by delivery person/driver)
     */
    public function markAsDelivered(StockTransfer $transfer, array $deliveryData = []): bool
    {
        try {
            $updateData = array_merge([
                'status' => 'delivered',
                'delivered_date' => now(),
            ], $deliveryData);

            $result = $transfer->update($updateData);

            if ($result) {
                Log::info("Stock transfer marked as delivered", [
                    'transfer_id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                ]);

                // Create alert for branch manager to confirm receipt
                StockAlert::create([
                    'branch_id' => $transfer->to_branch_id,
                    'stock_transfer_id' => $transfer->id,
                    'alert_type' => 'reconciliation_required',
                    'severity' => 'warning',
                    'title' => 'Confirm Stock Receipt',
                    'message' => "Stock transfer {$transfer->transfer_number} has been delivered. Please confirm receipt and perform reconciliation.",
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to mark transfer as delivered", [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Confirm receipt of stock transfer by branch manager
     */
    public function confirmReceipt(StockTransfer $transfer, array $receivedItems, User $branchManager): bool
    {
        DB::beginTransaction();

        try {
            // Update each item with received quantities
            foreach ($receivedItems as $itemData) {
                $item = $transfer->items()->find($itemData['item_id']);
                if ($item) {
                    $item->updateReceivedQuantity(
                        $itemData['quantity_received'],
                        $itemData['notes'] ?? null
                    );

                    // Update branch stock levels
                    $this->updateBranchStock(
                        $item->product_id,
                        $transfer->to_branch_id,
                        $itemData['quantity_received']
                    );
                }
            }

            // Update transfer status
            $transfer->update([
                'status' => 'confirmed',
                'confirmed_date' => now(),
            ]);

            DB::commit();

            Log::info("Stock transfer receipt confirmed", [
                'transfer_id' => $transfer->id,
                'transfer_number' => $transfer->transfer_number,
                'branch_manager_id' => $branchManager->id,
            ]);

            // Check for discrepancies and create alerts if needed
            $this->checkForDiscrepancies($transfer);

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to confirm stock transfer receipt", [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
                'branch_manager_id' => $branchManager->id,
            ]);
            return false;
        }
    }

    /**
     * Cancel stock transfer
     */
    public function cancelTransfer(StockTransfer $transfer, string $reason, User $user): bool
    {
        DB::beginTransaction();

        try {
            // If transfer was already dispatched from a branch, reverse stock movements
            if ($transfer->from_branch_id && $transfer->status !== 'pending') {
                foreach ($transfer->items as $item) {
                    $this->createStockMovement(
                        $item->product_id,
                        $transfer->from_branch_id,
                        $item->batch_id,
                        'transfer_in',
                        $item->quantity_sent,
                        $item->unit_price,
                        "Stock transfer cancellation: {$transfer->transfer_number} - {$reason}",
                        $user->id,
                        $transfer->id
                    );
                }
            }

            $transfer->update([
                'status' => 'cancelled',
                'delivery_notes' => $transfer->delivery_notes ? 
                    $transfer->delivery_notes . "\n\nCancellation Reason: " . $reason : 
                    "Cancellation Reason: " . $reason,
            ]);

            DB::commit();

            Log::info("Stock transfer cancelled", [
                'transfer_id' => $transfer->id,
                'transfer_number' => $transfer->transfer_number,
                'user_id' => $user->id,
                'reason' => $reason,
            ]);

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to cancel stock transfer", [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Create stock movement record
     */
    protected function createStockMovement(
        int $productId,
        int $branchId,
        ?int $batchId,
        string $type,
        float $quantity,
        float $unitPrice,
        string $notes,
        int $userId,
        int $stockTransferId
    ): void {
        StockMovement::create([
            'product_id' => $productId,
            'branch_id' => $branchId,
            'batch_id' => $batchId,
            'type' => $type,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'notes' => $notes,
            'user_id' => $userId,
            'stock_transfer_id' => $stockTransferId,
            'movement_date' => now(),
        ]);
    }

    /**
     * Update branch stock levels
     */
    protected function updateBranchStock(int $productId, int $branchId, float $quantity): void
    {
        $product = Product::find($productId);
        if ($product) {
            $currentStock = $product->getCurrentStock($branchId);
            $newStock = $currentStock + $quantity;
            $product->updateBranchStock($branchId, $newStock);
        }
    }

    /**
     * Check for discrepancies between sent and received quantities
     */
    protected function checkForDiscrepancies(StockTransfer $transfer): void
    {
        foreach ($transfer->items as $item) {
            $difference = $item->getQuantityDifference();
            
            if ($difference !== null && abs($difference) > 0.001) {
                // Create alert for significant discrepancies
                if (abs($difference) > ($item->quantity_sent * 0.05)) { // More than 5% difference
                    StockAlert::create([
                        'branch_id' => $transfer->to_branch_id,
                        'product_id' => $item->product_id,
                        'stock_transfer_id' => $transfer->id,
                        'alert_type' => 'quality_issue',
                        'severity' => abs($difference) > ($item->quantity_sent * 0.15) ? 'critical' : 'warning',
                        'title' => 'Stock Discrepancy Detected',
                        'message' => "Significant quantity difference detected for {$item->product->name}. Sent: {$item->quantity_sent}, Received: {$item->quantity_received}",
                    ]);
                }
            }
        }
    }

    /**
     * Get transfer statistics for a branch
     */
    public function getTransferStatistics(int $branchId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StockTransfer::where('to_branch_id', $branchId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transfers = $query->get();

        return [
            'total_transfers' => $transfers->count(),
            'pending_transfers' => $transfers->where('status', 'pending')->count(),
            'in_transit_transfers' => $transfers->where('status', 'in_transit')->count(),
            'delivered_transfers' => $transfers->where('status', 'delivered')->count(),
            'confirmed_transfers' => $transfers->where('status', 'confirmed')->count(),
            'cancelled_transfers' => $transfers->where('status', 'cancelled')->count(),
            'total_value' => $transfers->sum('total_value'),
            'total_transport_cost' => $transfers->sum('transport_cost'),
            'average_delivery_time' => $this->calculateAverageDeliveryTime($transfers),
            'overdue_transfers' => $transfers->filter(fn($t) => $t->isOverdue())->count(),
        ];
    }

    /**
     * Calculate average delivery time in days
     */
    protected function calculateAverageDeliveryTime($transfers): ?float
    {
        $deliveredTransfers = $transfers->filter(function ($transfer) {
            return $transfer->dispatch_date && $transfer->delivered_date;
        });

        if ($deliveredTransfers->isEmpty()) {
            return null;
        }

        $totalDays = $deliveredTransfers->sum(function ($transfer) {
            return $transfer->dispatch_date->diffInDays($transfer->delivered_date);
        });

        return round($totalDays / $deliveredTransfers->count(), 2);
    }

    /**
     * Get overdue transfers
     */
    public function getOverdueTransfers(?int $branchId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockTransfer::where('status', '!=', 'confirmed')
                              ->where('status', '!=', 'cancelled')
                              ->whereNotNull('expected_delivery')
                              ->where('expected_delivery', '<', now());

        if ($branchId) {
            $query->where('to_branch_id', $branchId);
        }

        return $query->with(['toBranch', 'items.product'])->get();
    }

    /**
     * Generate transfer performance report
     */
    public function generatePerformanceReport(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StockTransfer::query();

        if ($branchId) {
            $query->where('to_branch_id', $branchId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transfers = $query->with(['items', 'queries', 'transportExpenses'])->get();

        return [
            'summary' => [
                'total_transfers' => $transfers->count(),
                'total_value' => $transfers->sum('total_value'),
                'total_transport_cost' => $transfers->sum('transport_cost'),
                'average_items_per_transfer' => $transfers->avg(fn($t) => $t->items->count()),
            ],
            'status_breakdown' => [
                'pending' => $transfers->where('status', 'pending')->count(),
                'in_transit' => $transfers->where('status', 'in_transit')->count(),
                'delivered' => $transfers->where('status', 'delivered')->count(),
                'confirmed' => $transfers->where('status', 'confirmed')->count(),
                'cancelled' => $transfers->where('status', 'cancelled')->count(),
            ],
            'performance_metrics' => [
                'on_time_delivery_rate' => $this->calculateOnTimeDeliveryRate($transfers),
                'average_delivery_time_days' => $this->calculateAverageDeliveryTime($transfers),
                'query_rate' => $this->calculateQueryRate($transfers),
                'cancellation_rate' => $transfers->where('status', 'cancelled')->count() / max($transfers->count(), 1) * 100,
            ],
            'financial_impact' => [
                'total_queries_impact' => $transfers->flatMap->queries->sum('financial_impact'),
                'transport_cost_per_transfer' => $transfers->avg('transport_cost'),
                'value_per_transfer' => $transfers->avg('total_value'),
            ],
        ];
    }

    /**
     * Calculate on-time delivery rate
     */
    protected function calculateOnTimeDeliveryRate($transfers): float
    {
        $deliveredTransfers = $transfers->filter(function ($transfer) {
            return $transfer->expected_delivery && $transfer->delivered_date;
        });

        if ($deliveredTransfers->isEmpty()) {
            return 0;
        }

        $onTimeDeliveries = $deliveredTransfers->filter(function ($transfer) {
            return $transfer->delivered_date <= $transfer->expected_delivery;
        });

        return round(($onTimeDeliveries->count() / $deliveredTransfers->count()) * 100, 2);
    }

    /**
     * Calculate query rate (queries per transfer)
     */
    protected function calculateQueryRate($transfers): float
    {
        if ($transfers->isEmpty()) {
            return 0;
        }

        $totalQueries = $transfers->sum(fn($t) => $t->queries->count());
        return round($totalQueries / $transfers->count(), 2);
    }
}