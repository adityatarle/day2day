<?php

namespace App\Services;

use App\Models\StockReconciliation;
use App\Models\StockReconciliationItem;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\StockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StockReconciliationService
{
    /**
     * Create a new stock reconciliation
     */
    public function createReconciliation(
        StockTransfer $stockTransfer,
        array $items,
        User $performedBy,
        ?string $notes = null
    ): StockReconciliation {
        DB::beginTransaction();

        try {
            // Create the reconciliation record
            $reconciliation = StockReconciliation::create([
                'stock_transfer_id' => $stockTransfer->id,
                'branch_id' => $stockTransfer->to_branch_id,
                'performed_by' => $performedBy->id,
                'reconciliation_date' => now(),
                'notes' => $notes,
            ]);

            $hasSignificantVariances = false;

            // Add reconciliation items
            foreach ($items as $itemData) {
                $item = $reconciliation->items()->create([
                    'product_id' => $itemData['product_id'],
                    'batch_id' => $itemData['batch_id'] ?? null,
                    'system_quantity' => $itemData['system_quantity'],
                    'physical_quantity' => $itemData['physical_quantity'],
                    'reason' => $itemData['reason'] ?? null,
                ]);

                // Check for significant variances
                if ($item->isSignificantVariance()) {
                    $hasSignificantVariances = true;
                }
            }

            // Create alert if there are significant variances
            if ($hasSignificantVariances) {
                $this->createVarianceAlert($reconciliation);
            }

            DB::commit();

            Log::info("Stock reconciliation created", [
                'reconciliation_id' => $reconciliation->id,
                'stock_transfer_id' => $stockTransfer->id,
                'performed_by' => $performedBy->id,
                'items_count' => count($items),
                'has_significant_variances' => $hasSignificantVariances,
            ]);

            return $reconciliation;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to create stock reconciliation", [
                'stock_transfer_id' => $stockTransfer->id,
                'performed_by' => $performedBy->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Approve reconciliation
     */
    public function approveReconciliation(
        StockReconciliation $reconciliation,
        User $approver,
        ?string $notes = null
    ): bool {
        try {
            $result = $reconciliation->approve($approver, $notes);

            if ($result) {
                Log::info("Stock reconciliation approved", [
                    'reconciliation_id' => $reconciliation->id,
                    'approver_id' => $approver->id,
                    'total_variance_value' => $reconciliation->getTotalVarianceValue(),
                ]);

                // Create alert for significant financial impact
                if ($reconciliation->getTotalVarianceValue() > 1000) { // Configurable threshold
                    $this->createFinancialImpactAlert($reconciliation);
                }
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to approve stock reconciliation", [
                'reconciliation_id' => $reconciliation->id,
                'approver_id' => $approver->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Reject reconciliation
     */
    public function rejectReconciliation(
        StockReconciliation $reconciliation,
        User $rejector,
        string $reason
    ): bool {
        try {
            $result = $reconciliation->reject($rejector, $reason);

            if ($result) {
                Log::info("Stock reconciliation rejected", [
                    'reconciliation_id' => $reconciliation->id,
                    'rejector_id' => $rejector->id,
                    'reason' => $reason,
                ]);

                // Create alert for branch to redo reconciliation
                StockAlert::create([
                    'branch_id' => $reconciliation->branch_id,
                    'stock_transfer_id' => $reconciliation->stock_transfer_id,
                    'alert_type' => 'reconciliation_required',
                    'severity' => 'warning',
                    'title' => 'Reconciliation Rejected',
                    'message' => "Stock reconciliation has been rejected. Reason: {$reason}",
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to reject stock reconciliation", [
                'reconciliation_id' => $reconciliation->id,
                'rejector_id' => $rejector->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get reconciliation statistics for branch
     */
    public function getReconciliationStatistics(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StockReconciliation::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('reconciliation_date', [$startDate, $endDate]);
        }

        $reconciliations = $query->with(['items'])->get();

        return [
            'total_reconciliations' => $reconciliations->count(),
            'pending_reconciliations' => $reconciliations->where('status', 'pending')->count(),
            'approved_reconciliations' => $reconciliations->where('status', 'approved')->count(),
            'rejected_reconciliations' => $reconciliations->where('status', 'rejected')->count(),
            'total_variance_value' => $reconciliations->sum(fn($r) => $r->getTotalVarianceValue()),
            'significant_variances' => $reconciliations->filter(fn($r) => $r->hasSignificantVariances())->count(),
            'average_items_per_reconciliation' => $reconciliations->avg(fn($r) => $r->items->count()),
            'variance_breakdown' => $this->getVarianceBreakdown($reconciliations),
        ];
    }

    /**
     * Get variance breakdown by type
     */
    protected function getVarianceBreakdown($reconciliations): array
    {
        $breakdown = [
            'shortage' => ['count' => 0, 'value' => 0],
            'excess' => ['count' => 0, 'value' => 0],
            'none' => ['count' => 0, 'value' => 0],
        ];

        foreach ($reconciliations as $reconciliation) {
            foreach ($reconciliation->items as $item) {
                $type = $item->variance_type;
                $breakdown[$type]['count']++;
                $breakdown[$type]['value'] += abs($item->financial_impact);
            }
        }

        return $breakdown;
    }

    /**
     * Create alert for significant variances
     */
    protected function createVarianceAlert(StockReconciliation $reconciliation): void
    {
        $significantItems = $reconciliation->getSignificantVarianceItems();
        $itemCount = $significantItems->count();

        StockAlert::create([
            'branch_id' => $reconciliation->branch_id,
            'stock_transfer_id' => $reconciliation->stock_transfer_id,
            'alert_type' => 'reconciliation_required',
            'severity' => $itemCount > 3 ? 'critical' : 'warning',
            'title' => 'Significant Stock Variances Detected',
            'message' => "Stock reconciliation has {$itemCount} items with significant variances (>5%). Manager approval required.",
        ]);
    }

    /**
     * Create alert for high financial impact
     */
    protected function createFinancialImpactAlert(StockReconciliation $reconciliation): void
    {
        $totalImpact = $reconciliation->getTotalVarianceValue();

        StockAlert::create([
            'branch_id' => $reconciliation->branch_id,
            'stock_transfer_id' => $reconciliation->stock_transfer_id,
            'alert_type' => 'financial_impact',
            'severity' => $totalImpact > 5000 ? 'critical' : 'warning',
            'title' => 'High Financial Impact from Reconciliation',
            'message' => "Stock reconciliation approved with financial impact of ₹{$totalImpact}. Review recommended.",
        ]);
    }

    /**
     * Generate reconciliation accuracy report
     */
    public function generateAccuracyReport(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StockReconciliation::where('status', 'approved');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('reconciliation_date', [$startDate, $endDate]);
        }

        $reconciliations = $query->with(['items.product', 'branch'])->get();

        // Calculate accuracy metrics
        $totalItems = $reconciliations->flatMap->items->count();
        $accurateItems = $reconciliations->flatMap->items->filter(fn($item) => $item->variance_type === 'none')->count();
        $accuracyRate = $totalItems > 0 ? ($accurateItems / $totalItems) * 100 : 0;

        // Group by product to find most problematic items
        $productVariances = $reconciliations->flatMap->items
            ->groupBy('product_id')
            ->map(function ($items) {
                return [
                    'product' => $items->first()->product,
                    'total_reconciliations' => $items->count(),
                    'variance_count' => $items->filter(fn($item) => $item->variance_type !== 'none')->count(),
                    'total_variance_value' => $items->sum('financial_impact'),
                    'average_variance_percentage' => $items->avg('variance_percentage'),
                ];
            })
            ->sortByDesc('variance_count');

        // Group by branch for comparison
        $branchPerformance = $reconciliations->groupBy('branch_id')
            ->map(function ($branchReconciliations) {
                $items = $branchReconciliations->flatMap->items;
                $totalItems = $items->count();
                $accurateItems = $items->filter(fn($item) => $item->variance_type === 'none')->count();

                return [
                    'branch' => $branchReconciliations->first()->branch,
                    'total_reconciliations' => $branchReconciliations->count(),
                    'total_items' => $totalItems,
                    'accuracy_rate' => $totalItems > 0 ? ($accurateItems / $totalItems) * 100 : 0,
                    'total_variance_value' => $items->sum('financial_impact'),
                ];
            })
            ->sortByDesc('accuracy_rate');

        return [
            'summary' => [
                'total_reconciliations' => $reconciliations->count(),
                'total_items' => $totalItems,
                'overall_accuracy_rate' => round($accuracyRate, 2),
                'total_variance_value' => $reconciliations->sum(fn($r) => $r->getTotalVarianceValue()),
            ],
            'product_analysis' => $productVariances->take(10)->values(),
            'branch_performance' => $branchPerformance->values(),
            'trends' => $this->calculateAccuracyTrends($reconciliations),
        ];
    }

    /**
     * Calculate accuracy trends over time
     */
    protected function calculateAccuracyTrends($reconciliations): array
    {
        $trends = $reconciliations->groupBy(function ($reconciliation) {
            return $reconciliation->reconciliation_date->format('Y-m');
        })->map(function ($monthReconciliations, $month) {
            $items = $monthReconciliations->flatMap->items;
            $totalItems = $items->count();
            $accurateItems = $items->filter(fn($item) => $item->variance_type === 'none')->count();

            return [
                'month' => $month,
                'reconciliations_count' => $monthReconciliations->count(),
                'total_items' => $totalItems,
                'accuracy_rate' => $totalItems > 0 ? ($accurateItems / $totalItems) * 100 : 0,
                'variance_value' => $items->sum('financial_impact'),
            ];
        })->sortBy('month');

        return $trends->values()->toArray();
    }

    /**
     * Get pending reconciliations requiring approval
     */
    public function getPendingReconciliations(?int $branchId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockReconciliation::where('status', 'pending')
                                   ->with([
                                       'stockTransfer', 'branch', 'performedBy',
                                       'items.product'
                                   ])
                                   ->orderBy('created_at', 'asc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    /**
     * Auto-approve reconciliations with minimal variances
     */
    public function autoApproveMinimalVariances(): int
    {
        $approvedCount = 0;
        $pendingReconciliations = StockReconciliation::where('status', 'pending')
                                                    ->with(['items'])
                                                    ->get();

        foreach ($pendingReconciliations as $reconciliation) {
            // Auto-approve if no item has variance > 2% and total value < 500
            $hasSignificantVariance = $reconciliation->items->filter(function ($item) {
                return abs($item->variance_percentage) > 2 || abs($item->financial_impact) > 100;
            })->isNotEmpty();

            if (!$hasSignificantVariance && $reconciliation->getTotalVarianceValue() < 500) {
                $systemUser = User::where('email', 'system@example.com')->first();
                if ($systemUser && $reconciliation->approve($systemUser, 'Auto-approved: minimal variances')) {
                    $approvedCount++;

                    Log::info("Stock reconciliation auto-approved", [
                        'reconciliation_id' => $reconciliation->id,
                        'total_variance_value' => $reconciliation->getTotalVarianceValue(),
                    ]);
                }
            }
        }

        return $approvedCount;
    }
}