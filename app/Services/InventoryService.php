<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Batch;
use App\Models\StockMovement;
use App\Models\LossTracking;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Auto-update stock after sale.
     */
    public function updateStockAfterSale(OrderItem $orderItem): bool
    {
        try {
            DB::beginTransaction();

            $product = $orderItem->product;
            $order = $orderItem->order;
            $branch = $order->branch;
            
            // Get current stock from product_branches pivot
            $currentStock = $product->getCurrentStock($branch->id);
            
            // Calculate new stock
            $newStock = $currentStock - $orderItem->quantity;
            
            // Update stock in product_branches pivot table
            $product->updateStock($branch->id, $newStock);
            
            // Create stock movement record
            StockMovement::create([
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'batch_id' => $this->getBatchForSale($product->id, $branch->id, $orderItem->quantity),
                'movement_type' => 'sale',
                'quantity' => -$orderItem->quantity, // Negative for outgoing
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'user_id' => auth()->id(),
                'movement_date' => now(),
                'notes' => "Stock reduced for order #{$order->order_number}",
            ]);

            // Check and update online availability based on threshold
            $this->checkAndUpdateOnlineAvailability($product, $branch->id, $newStock);

            // Record complimentary/adjustment loss if applicable
            if ($orderItem->actual_weight && $orderItem->billed_weight && 
                $orderItem->actual_weight > $orderItem->billed_weight) {
                
                $complimentaryQuantity = $orderItem->actual_weight - $orderItem->billed_weight;
                $this->recordComplimentaryLoss($product, $branch, $complimentaryQuantity, $order);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update stock after sale: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check and update online availability based on stock threshold.
     */
    public function checkAndUpdateOnlineAvailability(Product $product, int $branchId, float $currentStock): void
    {
        $isAvailableOnline = $currentStock > $product->stock_threshold;
        
        // Update online availability in product_branches pivot
        $product->branches()->updateExistingPivot($branchId, [
            'is_available_online' => $isAvailableOnline,
            'updated_at' => now(),
        ]);

        // Log the status change
        if (!$isAvailableOnline && $currentStock <= $product->stock_threshold) {
            \Log::info("Product {$product->name} marked as 'Sold Out' online at branch {$branchId}. Stock: {$currentStock}, Threshold: {$product->stock_threshold}");
        }
    }

    /**
     * Get the appropriate batch for sale (FIFO - First In, First Out).
     */
    private function getBatchForSale(int $productId, int $branchId, float $quantity): ?int
    {
        $batch = Batch::where('product_id', $productId)
                     ->where('branch_id', $branchId)
                     ->where('current_quantity', '>', 0)
                     ->where('status', 'active')
                     ->orderBy('purchase_date', 'asc') // FIFO
                     ->first();

        if ($batch && $batch->current_quantity >= $quantity) {
            // Update batch quantity
            $batch->update([
                'current_quantity' => $batch->current_quantity - $quantity
            ]);

            // Mark batch as finished if quantity becomes zero
            if ($batch->current_quantity <= 0) {
                $batch->update(['status' => 'finished']);
            }

            return $batch->id;
        }

        return null;
    }

    /**
     * Record complimentary/adjustment loss.
     */
    public function recordComplimentaryLoss(Product $product, Branch $branch, float $quantity, $order = null): void
    {
        LossTracking::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'batch_id' => $this->getBatchForSale($product->id, $branch->id, $quantity),
            'loss_type' => 'complimentary',
            'quantity_lost' => $quantity,
            'financial_loss' => $quantity * $product->selling_price,
            'reason' => $order ? "Complimentary quantity for order #{$order->order_number}" : 'Complimentary adjustment',
            'user_id' => auth()->id(),
            'reference_type' => $order ? 'order' : null,
            'reference_id' => $order ? $order->id : null,
        ]);

        // Update stock accordingly
        $currentStock = $product->getCurrentStock($branch->id);
        $product->updateStock($branch->id, $currentStock - $quantity);
    }

    /**
     * Record weight loss.
     */
    public function recordWeightLoss(Product $product, Branch $branch, float $initialWeight, float $currentWeight, string $reason = 'Storage weight loss'): void
    {
        $lostWeight = $initialWeight - $currentWeight;
        
        if ($lostWeight > 0) {
            LossTracking::create([
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'batch_id' => $this->getBatchForSale($product->id, $branch->id, $lostWeight),
                'loss_type' => 'weight_loss',
                'quantity_lost' => $lostWeight,
                'financial_loss' => $lostWeight * $product->selling_price,
                'reason' => $reason,
                'user_id' => auth()->id(),
                'initial_quantity' => $initialWeight,
                'final_quantity' => $currentWeight,
            ]);

            // Update stock
            $currentStock = $product->getCurrentStock($branch->id);
            $product->updateStock($branch->id, $currentStock - $lostWeight);
        }
    }

    /**
     * Record water loss.
     */
    public function recordWaterLoss(Product $product, Branch $branch, float $quantity, string $reason = 'Moisture loss'): void
    {
        LossTracking::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'batch_id' => $this->getBatchForSale($product->id, $branch->id, $quantity),
            'loss_type' => 'water_loss',
            'quantity_lost' => $quantity,
            'financial_loss' => $quantity * $product->selling_price,
            'reason' => $reason,
            'user_id' => auth()->id(),
        ]);

        // Update stock
        $currentStock = $product->getCurrentStock($branch->id);
        $product->updateStock($branch->id, $currentStock - $quantity);
    }

    /**
     * Record wastage loss.
     */
    public function recordWastageLoss(Product $product, Branch $branch, float $quantity, string $reason = 'Damaged/spoiled items'): void
    {
        LossTracking::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'batch_id' => $this->getBatchForSale($product->id, $branch->id, $quantity),
            'loss_type' => 'wastage',
            'quantity_lost' => $quantity,
            'financial_loss' => $quantity * $product->selling_price,
            'reason' => $reason,
            'user_id' => auth()->id(),
        ]);

        // Update stock
        $currentStock = $product->getCurrentStock($branch->id);
        $product->updateStock($branch->id, $currentStock - $quantity);
    }

    /**
     * Get low stock products across branches.
     */
    public function getLowStockProducts(int $branchId = null): array
    {
        $query = Product::with(['branches' => function ($query) use ($branchId) {
            $query->select('branches.id', 'branches.name', 'branches.code')
                  ->withPivot(['current_stock', 'is_available_online']);
            
            if ($branchId) {
                $query->where('branches.id', $branchId);
            }
        }]);

        $products = $query->active()->get();

        $lowStockProducts = [];
        
        foreach ($products as $product) {
            foreach ($product->branches as $branch) {
                $currentStock = $branch->pivot->current_stock;
                
                if ($currentStock <= $product->stock_threshold) {
                    $lowStockProducts[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'current_stock' => $currentStock,
                        'threshold' => $product->stock_threshold,
                        'status' => $currentStock == 0 ? 'out_of_stock' : 'low_stock',
                        'is_available_online' => $branch->pivot->is_available_online,
                    ];
                }
            }
        }

        return $lowStockProducts;
    }

    /**
     * Bulk update stock thresholds.
     */
    public function bulkUpdateThresholds(array $updates): bool
    {
        try {
            DB::beginTransaction();

            foreach ($updates as $update) {
                $product = Product::find($update['product_id']);
                if ($product) {
                    $product->update(['stock_threshold' => $update['threshold']]);
                    
                    // Check and update online availability for all branches
                    foreach ($product->branches as $branch) {
                        $this->checkAndUpdateOnlineAvailability(
                            $product, 
                            $branch->id, 
                            $branch->pivot->current_stock
                        );
                    }
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to bulk update thresholds: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get inventory valuation with cost allocation.
     */
    public function getInventoryValuation(int $branchId = null): array
    {
        $query = Product::with(['branches' => function ($query) use ($branchId) {
            $query->withPivot(['current_stock', 'selling_price']);
            
            if ($branchId) {
                $query->where('branches.id', $branchId);
            }
        }, 'expenseAllocations']);

        $products = $query->active()->get();

        $valuation = [];
        $totalStockValue = 0;
        $totalCostValue = 0;
        $totalAllocatedExpenses = 0;

        foreach ($products as $product) {
            foreach ($product->branches as $branch) {
                $currentStock = $branch->pivot->current_stock;
                $sellingPrice = $branch->pivot->selling_price;
                $costPerUnit = $product->getCostPerUnit($branch->id);
                
                $stockValue = $currentStock * $sellingPrice;
                $costValue = $currentStock * $costPerUnit;
                $allocatedExpenses = $product->getTotalAllocatedExpenses();

                $valuation[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'current_stock' => $currentStock,
                    'purchase_price' => $product->purchase_price,
                    'cost_per_unit' => $costPerUnit,
                    'selling_price' => $sellingPrice,
                    'stock_value' => $stockValue,
                    'cost_value' => $costValue,
                    'allocated_expenses' => $allocatedExpenses,
                    'profit_margin' => $stockValue - $costValue,
                    'profit_percentage' => $product->getProfitPercentage($branch->id),
                ];

                $totalStockValue += $stockValue;
                $totalCostValue += $costValue;
                $totalAllocatedExpenses += $allocatedExpenses;
            }
        }

        return [
            'products' => $valuation,
            'summary' => [
                'total_stock_value' => $totalStockValue,
                'total_cost_value' => $totalCostValue,
                'total_allocated_expenses' => $totalAllocatedExpenses,
                'total_profit_margin' => $totalStockValue - $totalCostValue,
                'overall_profit_percentage' => $totalCostValue > 0 ? 
                    round((($totalStockValue - $totalCostValue) / $totalCostValue) * 100, 2) : 0,
            ]
        ];
    }

    /**
     * Process batch expiry and automatic wastage.
     */
    public function processExpiredBatches(): int
    {
        $expiredBatches = Batch::where('expiry_date', '<', now())
                              ->where('current_quantity', '>', 0)
                              ->where('status', 'active')
                              ->get();

        $processedCount = 0;

        foreach ($expiredBatches as $batch) {
            try {
                DB::beginTransaction();

                // Record wastage for expired quantity
                LossTracking::create([
                    'product_id' => $batch->product_id,
                    'branch_id' => $batch->branch_id,
                    'batch_id' => $batch->id,
                    'loss_type' => 'wastage',
                    'quantity_lost' => $batch->current_quantity,
                    'financial_loss' => $batch->current_quantity * $batch->product->selling_price,
                    'reason' => 'Expired batch - automatic wastage',
                    'user_id' => 1, // System user
                ]);

                // Update product stock
                $product = $batch->product;
                $currentStock = $product->getCurrentStock($batch->branch_id);
                $product->updateStock($batch->branch_id, $currentStock - $batch->current_quantity);

                // Mark batch as expired
                $batch->update([
                    'current_quantity' => 0,
                    'status' => 'expired'
                ]);

                // Check online availability
                $this->checkAndUpdateOnlineAvailability(
                    $product, 
                    $batch->branch_id, 
                    $currentStock - $batch->current_quantity
                );

                DB::commit();
                $processedCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Failed to process expired batch {$batch->id}: " . $e->getMessage());
            }
        }

        return $processedCount;
    }

    /**
     * Get stock alerts (low stock, out of stock, expiring soon).
     */
    public function getStockAlerts(int $branchId = null): array
    {
        $alerts = [
            'low_stock' => $this->getLowStockProducts($branchId),
            'out_of_stock' => $this->getOutOfStockProducts($branchId),
            'expiring_soon' => $this->getExpiringBatches($branchId),
        ];

        return $alerts;
    }

    /**
     * Get out of stock products.
     */
    private function getOutOfStockProducts(int $branchId = null): array
    {
        $query = Product::with(['branches' => function ($query) use ($branchId) {
            $query->select('branches.id', 'branches.name', 'branches.code')
                  ->withPivot(['current_stock', 'is_available_online']);
            
            if ($branchId) {
                $query->where('branches.id', $branchId);
            }
        }]);

        $products = $query->active()->get();

        $outOfStockProducts = [];
        
        foreach ($products as $product) {
            foreach ($product->branches as $branch) {
                if ($branch->pivot->current_stock == 0) {
                    $outOfStockProducts[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'current_stock' => $branch->pivot->current_stock,
                        'is_available_online' => $branch->pivot->is_available_online,
                    ];
                }
            }
        }

        return $outOfStockProducts;
    }

    /**
     * Get batches expiring soon.
     */
    private function getExpiringBatches(int $branchId = null, int $days = 7): array
    {
        $query = Batch::with(['product', 'branch'])
                     ->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>', now())
                     ->where('current_quantity', '>', 0)
                     ->where('status', 'active');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->orderBy('expiry_date', 'asc')->get()->map(function ($batch) {
            return [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'product_id' => $batch->product_id,
                'product_name' => $batch->product->name,
                'branch_id' => $batch->branch_id,
                'branch_name' => $batch->branch->name,
                'current_quantity' => $batch->current_quantity,
                'expiry_date' => $batch->expiry_date,
                'days_to_expire' => now()->diffInDays($batch->expiry_date),
                'financial_impact' => $batch->current_quantity * $batch->product->selling_price,
            ];
        })->toArray();
    }

    /**
     * Transfer stock between branches.
     */
    public function transferStock(int $productId, int $fromBranchId, int $toBranchId, float $quantity, string $reason = 'Branch transfer'): bool
    {
        try {
            DB::beginTransaction();

            $product = Product::find($productId);
            $fromBranch = Branch::find($fromBranchId);
            $toBranch = Branch::find($toBranchId);

            // Check if source branch has sufficient stock
            $currentStock = $product->getCurrentStock($fromBranchId);
            if ($currentStock < $quantity) {
                throw new \Exception('Insufficient stock for transfer');
            }

            // Reduce stock from source branch
            $product->updateStock($fromBranchId, $currentStock - $quantity);

            // Add stock to destination branch
            $destinationStock = $product->getCurrentStock($toBranchId);
            $product->updateStock($toBranchId, $destinationStock + $quantity);

            // Create stock movements
            StockMovement::create([
                'product_id' => $productId,
                'branch_id' => $fromBranchId,
                'movement_type' => 'transfer_out',
                'quantity' => -$quantity,
                'reference_type' => 'branch_transfer',
                'reference_id' => $toBranchId,
                'user_id' => auth()->id(),
                'movement_date' => now(),
                'notes' => "Transfer to {$toBranch->name}: {$reason}",
            ]);

            StockMovement::create([
                'product_id' => $productId,
                'branch_id' => $toBranchId,
                'movement_type' => 'transfer_in',
                'quantity' => $quantity,
                'reference_type' => 'branch_transfer',
                'reference_id' => $fromBranchId,
                'user_id' => auth()->id(),
                'movement_date' => now(),
                'notes' => "Transfer from {$fromBranch->name}: {$reason}",
            ]);

            // Update online availability for both branches
            $this->checkAndUpdateOnlineAvailability($product, $fromBranchId, $currentStock - $quantity);
            $this->checkAndUpdateOnlineAvailability($product, $toBranchId, $destinationStock + $quantity);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to transfer stock: ' . $e->getMessage());
            return false;
        }
    }
}