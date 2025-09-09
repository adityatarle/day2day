<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Discrepancy;
use App\Models\DiscrepancyLine;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\Transfer;
use App\Models\TransferLine;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function createTransfer(int $fromBranchId, int $toBranchId, ?int $toSubbranchId, array $lines, ?string $notes = null): Transfer
    {
        return DB::transaction(function () use ($fromBranchId, $toBranchId, $toSubbranchId, $lines, $notes) {
            $transfer = Transfer::create([
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'to_subbranch_id' => $toSubbranchId,
                'status' => 'draft',
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $line) {
                TransferLine::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $line['product_id'],
                    'batch_number' => $line['batch_number'] ?? null,
                    'expected_qty' => $line['expected_qty'],
                    'expected_weight_kg' => $line['expected_weight_kg'] ?? null,
                    'expiry_date' => $line['expiry_date'] ?? null,
                    'standard_cost' => $line['standard_cost'] ?? null,
                ]);
            }

            return $transfer->load('lines');
        });
    }

    public function approveTransfer(Transfer $transfer): Transfer
    {
        $transfer->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return $transfer;
    }

    public function dispatchTransfer(Transfer $transfer, array $shipmentData): Shipment
    {
        return DB::transaction(function () use ($transfer, $shipmentData) {
            // Reduce stock from source as transfer_out movements (reserve/dispatch)
            foreach ($transfer->lines as $line) {
                $product = Product::find($line->product_id);
                $currentStock = $product->getCurrentStock($transfer->from_branch_id);
                $newStock = $currentStock - $line->expected_qty;
                if ($newStock < 0) {
                    throw new \Exception('Insufficient stock for dispatch');
                }
                $product->updateBranchStock($transfer->from_branch_id, $newStock);

                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id' => $transfer->from_branch_id,
                    'movement_type' => 'transfer_out',
                    'quantity' => -$line->expected_qty,
                    'reference_type' => 'transfer',
                    'reference_id' => $transfer->id,
                    'user_id' => auth()->id(),
                    'movement_date' => now(),
                    'notes' => 'Dispatch to branch ID ' . $transfer->to_branch_id,
                ]);
            }

            $shipment = Shipment::create([
                'transfer_id' => $transfer->id,
                'transporter_name' => $shipmentData['transporter_name'] ?? null,
                'vehicle_no' => $shipmentData['vehicle_no'] ?? null,
                'lr_no' => $shipmentData['lr_no'] ?? null,
                'seal_no' => $shipmentData['seal_no'] ?? null,
                'gross_weight_kg' => $shipmentData['gross_weight_kg'] ?? null,
                'tare_weight_kg' => $shipmentData['tare_weight_kg'] ?? null,
                'net_weight_kg' => $shipmentData['net_weight_kg'] ?? null,
                'dispatch_ts' => $shipmentData['dispatch_ts'] ?? now(),
                'documents' => $shipmentData['documents'] ?? [],
            ]);

            $transfer->update([
                'status' => 'in_transit',
                'dispatched_by' => auth()->id(),
            ]);

            return $shipment;
        });
    }

    public function markDelivered(Transfer $transfer): Transfer
    {
        $transfer->update([
            'status' => 'delivered_pending_confirm',
            'delivered_marked_by' => auth()->id(),
        ]);

        return $transfer;
    }

    public function receiveTransfer(Transfer $transfer, array $receiptData, float $tolerancePercent = 1.0): Receipt
    {
        return DB::transaction(function () use ($transfer, $receiptData, $tolerancePercent) {
            $receipt = Receipt::create([
                'transfer_id' => $transfer->id,
                'received_branch_id' => $transfer->to_branch_id,
                'received_subbranch_id' => $transfer->to_subbranch_id,
                'arrival_ts' => $receiptData['arrival_ts'] ?? now(),
                'reweigh_gross_kg' => $receiptData['reweigh_gross_kg'] ?? null,
                'reweigh_tare_kg' => $receiptData['reweigh_tare_kg'] ?? null,
                'reweigh_net_kg' => $receiptData['reweigh_net_kg'] ?? null,
                'within_tolerance' => false,
                'tolerance_percent' => $tolerancePercent,
                'accepted_by' => auth()->id(),
            ]);

            // For each line, add stock to destination and check tolerance by quantity
            foreach ($transfer->lines as $line) {
                $product = Product::find($line->product_id);
                $destStock = $product->getCurrentStock($transfer->to_branch_id);

                // Received quantity defaults to expected if not provided per-line (simple version)
                $receivedQty = $receiptData['lines'][$line->id]['received_qty'] ?? $line->expected_qty;

                $product->updateBranchStock($transfer->to_branch_id, $destStock + $receivedQty);

                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id' => $transfer->to_branch_id,
                    'movement_type' => 'transfer_in',
                    'quantity' => $receivedQty,
                    'reference_type' => 'transfer',
                    'reference_id' => $transfer->id,
                    'user_id' => auth()->id(),
                    'movement_date' => now(),
                    'notes' => 'Received from branch ID ' . $transfer->from_branch_id,
                ]);

                // Variance handling
                $variance = $receivedQty - $line->expected_qty;
                $variancePct = $line->expected_qty > 0 ? (abs($variance) / $line->expected_qty) * 100 : 0;
                if ($variancePct <= $tolerancePercent) {
                    // Minor variance: record as adjustment
                    if ($variance != 0) {
                        StockMovement::create([
                            'product_id' => $product->id,
                            'branch_id' => $transfer->to_branch_id,
                            'movement_type' => 'adjustment',
                            'quantity' => $variance, // +/-
                            'reference_type' => 'transfer_variance',
                            'reference_id' => $transfer->id,
                            'user_id' => auth()->id(),
                            'movement_date' => now(),
                            'notes' => 'Within tolerance variance on receipt',
                        ]);
                    }
                } else {
                    // Create discrepancy record
                    $discrepancy = Discrepancy::firstOrCreate(
                        [
                            'transfer_id' => $transfer->id,
                            'status' => 'open',
                            'reason_category' => 'weight_diff',
                        ],
                        [
                            'notes' => 'Variance beyond tolerance',
                            'raised_by' => auth()->id(),
                        ]
                    );

                    DiscrepancyLine::create([
                        'discrepancy_id' => $discrepancy->id,
                        'product_id' => $product->id,
                        'qty_delta' => $variance,
                        'weight_delta_kg' => null,
                        'disposition' => 'adjust',
                        'notes' => 'Auto-created from receipt variance',
                    ]);
                }
            }

            $transfer->update([
                'status' => 'received',
                'received_by' => auth()->id(),
            ]);

            return $receipt->load('attachments');
        });
    }

    public function resolveDiscrepancy(Discrepancy $discrepancy, string $disposition = 'adjust'): Discrepancy
    {
        return DB::transaction(function () use ($discrepancy, $disposition) {
            $transfer = $discrepancy->transfer;
            foreach ($discrepancy->lines as $line) {
                $product = Product::find($line->product_id);
                $branchId = $transfer->to_branch_id;
                $currentStock = $product->getCurrentStock($branchId);

                if ($line->qty_delta !== null && $line->qty_delta != 0) {
                    if ($disposition === 'adjust') {
                        // Post adjustment
                        $product->updateBranchStock($branchId, $currentStock + $line->qty_delta);
                        StockMovement::create([
                            'product_id' => $product->id,
                            'branch_id' => $branchId,
                            'movement_type' => 'adjustment',
                            'quantity' => $line->qty_delta,
                            'reference_type' => 'discrepancy',
                            'reference_id' => $discrepancy->id,
                            'user_id' => auth()->id(),
                            'movement_date' => now(),
                            'notes' => 'Discrepancy resolution adjustment',
                        ]);
                    } elseif ($disposition === 'scrap') {
                        // Post wastage/loss
                        $product->updateBranchStock($branchId, $currentStock - abs($line->qty_delta));
                        StockMovement::create([
                            'product_id' => $product->id,
                            'branch_id' => $branchId,
                            'movement_type' => 'wastage',
                            'quantity' => -abs($line->qty_delta),
                            'reference_type' => 'discrepancy',
                            'reference_id' => $discrepancy->id,
                            'user_id' => auth()->id(),
                            'movement_date' => now(),
                            'notes' => 'Discrepancy scrapped quantity',
                        ]);
                    }
                }
            }

            $discrepancy->update([
                'status' => 'resolved',
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            // If all discrepancies resolved, mark transfer reconciled
            $openCount = Discrepancy::where('transfer_id', $discrepancy->transfer_id)
                ->whereIn('status', ['open', 'under_review', 'reopened'])
                ->count();
            if ($openCount === 0) {
                $discrepancy->transfer->update(['status' => 'reconciled']);
            }

            return $discrepancy->fresh('lines');
        });
    }
}

