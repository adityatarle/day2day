<?php

namespace App\Services;

use App\Models\TransportExpense;
use App\Models\StockTransfer;
use App\Models\StockFinancialImpact;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class TransportExpenseService
{
    /**
     * Add transport expense to stock transfer
     */
    public function addExpense(StockTransfer $transfer, array $expenseData, User $user): TransportExpense
    {
        DB::beginTransaction();

        try {
            $expense = TransportExpense::create([
                'stock_transfer_id' => $transfer->id,
                'expense_type' => $expenseData['expense_type'],
                'description' => $expenseData['description'],
                'amount' => $expenseData['amount'],
                'vendor_name' => $expenseData['vendor_name'] ?? null,
                'receipt_number' => $expenseData['receipt_number'] ?? null,
                'expense_date' => $expenseData['expense_date'] ?? now()->toDateString(),
                'payment_method' => $expenseData['payment_method'] ?? null,
                'receipts' => $expenseData['receipts'] ?? null,
                'notes' => $expenseData['notes'] ?? null,
            ]);

            // Update total transport cost in stock transfer
            $this->updateTransferTransportCost($transfer);

            // Create financial impact record
            $this->createFinancialImpact($expense, $transfer);

            DB::commit();

            Log::info("Transport expense added", [
                'expense_id' => $expense->id,
                'transfer_id' => $transfer->id,
                'amount' => $expense->amount,
                'type' => $expense->expense_type,
                'user_id' => $user->id,
            ]);

            return $expense;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to add transport expense", [
                'transfer_id' => $transfer->id,
                'expense_data' => $expenseData,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update transport expense
     */
    public function updateExpense(TransportExpense $expense, array $updateData, User $user): bool
    {
        DB::beginTransaction();

        try {
            $oldAmount = $expense->amount;
            $result = $expense->update($updateData);

            if ($result) {
                // Update transfer transport cost if amount changed
                if (isset($updateData['amount']) && $updateData['amount'] != $oldAmount) {
                    $this->updateTransferTransportCost($expense->stockTransfer);
                    
                    // Update financial impact
                    $this->updateFinancialImpact($expense, $oldAmount);
                }

                Log::info("Transport expense updated", [
                    'expense_id' => $expense->id,
                    'transfer_id' => $expense->stock_transfer_id,
                    'old_amount' => $oldAmount,
                    'new_amount' => $expense->amount,
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to update transport expense", [
                'expense_id' => $expense->id,
                'update_data' => $updateData,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete transport expense
     */
    public function deleteExpense(TransportExpense $expense, User $user): bool
    {
        DB::beginTransaction();

        try {
            $transfer = $expense->stockTransfer;
            $expenseId = $expense->id;
            $amount = $expense->amount;

            // Delete associated receipts
            if ($expense->receipts) {
                foreach ($expense->receipts as $receipt) {
                    Storage::disk('public')->delete($receipt['path']);
                }
            }

            // Delete financial impact record
            StockFinancialImpact::where('impactable_type', TransportExpense::class)
                               ->where('impactable_id', $expense->id)
                               ->delete();

            $result = $expense->delete();

            if ($result) {
                // Update transfer transport cost
                $this->updateTransferTransportCost($transfer);

                Log::info("Transport expense deleted", [
                    'expense_id' => $expenseId,
                    'transfer_id' => $transfer->id,
                    'amount' => $amount,
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete transport expense", [
                'expense_id' => $expense->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Upload receipt files for expense
     */
    public function uploadReceipts(TransportExpense $expense, array $files): array
    {
        $uploadedFiles = [];

        try {
            foreach ($files as $file) {
                $path = $file->store("transport-expenses/{$expense->id}/receipts", 'public');
                $uploadedFiles[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toISOString(),
                ];
            }

            // Update expense with new receipts
            $currentReceipts = $expense->receipts ?? [];
            $allReceipts = array_merge($currentReceipts, $uploadedFiles);

            $expense->update(['receipts' => $allReceipts]);

            Log::info("Receipts uploaded for transport expense", [
                'expense_id' => $expense->id,
                'file_count' => count($uploadedFiles),
            ]);

            return $uploadedFiles;

        } catch (Exception $e) {
            // Clean up uploaded files on error
            foreach ($uploadedFiles as $file) {
                Storage::disk('public')->delete($file['path']);
            }

            Log::error("Failed to upload receipts for transport expense", [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get expense statistics for transfer or branch
     */
    public function getExpenseStatistics(?int $transferId = null, ?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = TransportExpense::query();

        if ($transferId) {
            $query->where('stock_transfer_id', $transferId);
        }

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $expenses = $query->get();

        return [
            'total_expenses' => $expenses->count(),
            'total_amount' => $expenses->sum('amount'),
            'average_expense' => $expenses->avg('amount'),
            'expenses_by_type' => $this->getExpensesByType($expenses),
            'expenses_by_vendor' => $this->getExpensesByVendor($expenses),
            'monthly_breakdown' => $this->getMonthlyBreakdown($expenses),
            'top_vendors' => $this->getTopVendors($expenses, 10),
        ];
    }

    /**
     * Generate expense report for transfers
     */
    public function generateExpenseReport(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = TransportExpense::with(['stockTransfer.toBranch']);

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $expenses = $query->get();
        $transfers = $expenses->pluck('stockTransfer')->unique('id');

        return [
            'summary' => [
                'total_transfers' => $transfers->count(),
                'total_expenses' => $expenses->count(),
                'total_amount' => $expenses->sum('amount'),
                'average_per_transfer' => $transfers->count() > 0 ? $expenses->sum('amount') / $transfers->count() : 0,
                'average_per_expense' => $expenses->avg('amount'),
            ],
            'expense_breakdown' => [
                'by_type' => $this->getExpensesByType($expenses),
                'by_category' => $this->getExpensesByCategory($expenses),
                'by_payment_method' => $this->getExpensesByPaymentMethod($expenses),
            ],
            'transfer_analysis' => [
                'most_expensive_transfer' => $this->getMostExpensiveTransfer($transfers),
                'cost_per_km' => $this->calculateCostPerKm($transfers),
                'cost_efficiency' => $this->calculateCostEfficiency($transfers),
            ],
            'vendor_analysis' => [
                'top_vendors' => $this->getTopVendors($expenses, 5),
                'vendor_performance' => $this->getVendorPerformance($expenses),
            ],
            'trends' => [
                'monthly_trend' => $this->getMonthlyBreakdown($expenses),
                'weekly_average' => $this->getWeeklyAverage($expenses),
            ],
        ];
    }

    /**
     * Calculate transport cost efficiency metrics
     */
    public function calculateTransportMetrics(StockTransfer $transfer): array
    {
        $expenses = $transfer->transportExpenses;
        $totalExpenses = $expenses->sum('amount');
        $transferValue = $transfer->total_value;
        $totalWeight = $transfer->items->sum('quantity_sent');

        return [
            'total_transport_cost' => $totalExpenses,
            'cost_percentage_of_value' => $transferValue > 0 ? ($totalExpenses / $transferValue) * 100 : 0,
            'cost_per_kg' => $totalWeight > 0 ? $totalExpenses / $totalWeight : 0,
            'cost_breakdown' => $this->getExpensesByType($expenses),
            'efficiency_rating' => $this->calculateEfficiencyRating($totalExpenses, $transferValue, $totalWeight),
            'benchmark_comparison' => $this->getBenchmarkComparison($totalExpenses, $transferValue),
        ];
    }

    /**
     * Update transfer total transport cost
     */
    protected function updateTransferTransportCost(StockTransfer $transfer): void
    {
        $totalCost = $transfer->transportExpenses()->sum('amount');
        $transfer->update(['transport_cost' => $totalCost]);
    }

    /**
     * Create financial impact record for expense
     */
    protected function createFinancialImpact(TransportExpense $expense, StockTransfer $transfer): void
    {
        StockFinancialImpact::create([
            'impactable_type' => TransportExpense::class,
            'impactable_id' => $expense->id,
            'branch_id' => $transfer->to_branch_id,
            'impact_type' => 'transport_cost',
            'amount' => $expense->amount,
            'impact_category' => 'cost',
            'description' => "Transport expense: {$expense->getExpenseTypeDisplayName()} - {$expense->description}",
            'impact_date' => $expense->expense_date,
            'is_recoverable' => false,
        ]);
    }

    /**
     * Update financial impact when expense amount changes
     */
    protected function updateFinancialImpact(TransportExpense $expense, float $oldAmount): void
    {
        $impact = StockFinancialImpact::where('impactable_type', TransportExpense::class)
                                    ->where('impactable_id', $expense->id)
                                    ->first();

        if ($impact) {
            $impact->update(['amount' => $expense->amount]);
        }
    }

    /**
     * Get expenses grouped by type
     */
    protected function getExpensesByType($expenses): array
    {
        return $expenses->groupBy('expense_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'average_amount' => $group->avg('amount'),
            ];
        })->toArray();
    }

    /**
     * Get expenses grouped by category
     */
    protected function getExpensesByCategory($expenses): array
    {
        return $expenses->groupBy(function ($expense) {
            return $expense->getExpenseCategory();
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'average_amount' => $group->avg('amount'),
            ];
        })->toArray();
    }

    /**
     * Get expenses grouped by payment method
     */
    protected function getExpensesByPaymentMethod($expenses): array
    {
        return $expenses->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
            ];
        })->toArray();
    }

    /**
     * Get expenses grouped by vendor
     */
    protected function getExpensesByVendor($expenses): array
    {
        return $expenses->whereNotNull('vendor_name')
                       ->groupBy('vendor_name')
                       ->map(function ($group) {
                           return [
                               'count' => $group->count(),
                               'total_amount' => $group->sum('amount'),
                               'average_amount' => $group->avg('amount'),
                           ];
                       })->toArray();
    }

    /**
     * Get monthly breakdown of expenses
     */
    protected function getMonthlyBreakdown($expenses): array
    {
        return $expenses->groupBy(function ($expense) {
            return $expense->expense_date->format('Y-m');
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
            ];
        })->toArray();
    }

    /**
     * Get top vendors by spending
     */
    protected function getTopVendors($expenses, int $limit = 10): array
    {
        return $expenses->whereNotNull('vendor_name')
                       ->groupBy('vendor_name')
                       ->map(function ($group) {
                           return [
                               'vendor_name' => $group->first()->vendor_name,
                               'total_amount' => $group->sum('amount'),
                               'expense_count' => $group->count(),
                               'average_expense' => $group->avg('amount'),
                           ];
                       })
                       ->sortByDesc('total_amount')
                       ->take($limit)
                       ->values()
                       ->toArray();
    }

    /**
     * Get vendor performance metrics
     */
    protected function getVendorPerformance($expenses): array
    {
        return $expenses->whereNotNull('vendor_name')
                       ->groupBy('vendor_name')
                       ->map(function ($group) {
                           $vendor = $group->first()->vendor_name;
                           return [
                               'vendor_name' => $vendor,
                               'total_expenses' => $group->count(),
                               'total_amount' => $group->sum('amount'),
                               'average_amount' => $group->avg('amount'),
                               'expense_types' => $group->pluck('expense_type')->unique()->values(),
                               'reliability_score' => $this->calculateVendorReliability($group),
                           ];
                       })->toArray();
    }

    /**
     * Calculate vendor reliability score
     */
    protected function calculateVendorReliability($vendorExpenses): float
    {
        // Simple reliability score based on receipt completeness and expense consistency
        $withReceipts = $vendorExpenses->filter(fn($e) => $e->hasReceipts())->count();
        $total = $vendorExpenses->count();
        
        $receiptScore = $total > 0 ? ($withReceipts / $total) * 50 : 0;
        
        // Consistency score based on expense amount variance
        $amounts = $vendorExpenses->pluck('amount');
        $avgAmount = $amounts->avg();
        $variance = $amounts->map(fn($a) => abs($a - $avgAmount))->avg();
        $consistencyScore = $avgAmount > 0 ? max(0, 50 - (($variance / $avgAmount) * 50)) : 0;
        
        return round($receiptScore + $consistencyScore, 2);
    }

    /**
     * Get most expensive transfer
     */
    protected function getMostExpensiveTransfer($transfers): ?array
    {
        $mostExpensive = $transfers->sortByDesc(function ($transfer) {
            return $transfer->transportExpenses->sum('amount');
        })->first();

        if (!$mostExpensive) {
            return null;
        }

        return [
            'transfer_number' => $mostExpensive->transfer_number,
            'total_cost' => $mostExpensive->transportExpenses->sum('amount'),
            'branch' => $mostExpensive->toBranch->name,
            'date' => $mostExpensive->created_at->format('Y-m-d'),
        ];
    }

    /**
     * Calculate cost per kilometer (rough estimate)
     */
    protected function calculateCostPerKm($transfers): float
    {
        // This would need actual distance data, for now using estimated average
        $totalCost = $transfers->sum(fn($t) => $t->transportExpenses->sum('amount'));
        $estimatedKm = $transfers->count() * 100; // Assume 100km average per transfer
        
        return $estimatedKm > 0 ? $totalCost / $estimatedKm : 0;
    }

    /**
     * Calculate cost efficiency rating
     */
    protected function calculateCostEfficiency($transfers): array
    {
        $efficiencyScores = $transfers->map(function ($transfer) {
            $transportCost = $transfer->transportExpenses->sum('amount');
            $transferValue = $transfer->total_value;
            
            if ($transferValue <= 0) return 0;
            
            $costPercentage = ($transportCost / $transferValue) * 100;
            
            // Efficiency score: lower cost percentage = higher score
            return max(0, 100 - $costPercentage);
        });

        return [
            'average_efficiency' => $efficiencyScores->avg(),
            'best_efficiency' => $efficiencyScores->max(),
            'worst_efficiency' => $efficiencyScores->min(),
            'efficiency_distribution' => [
                'excellent' => $efficiencyScores->filter(fn($s) => $s >= 80)->count(),
                'good' => $efficiencyScores->filter(fn($s) => $s >= 60 && $s < 80)->count(),
                'average' => $efficiencyScores->filter(fn($s) => $s >= 40 && $s < 60)->count(),
                'poor' => $efficiencyScores->filter(fn($s) => $s < 40)->count(),
            ],
        ];
    }

    /**
     * Calculate efficiency rating for a single transfer
     */
    protected function calculateEfficiencyRating(float $transportCost, float $transferValue, float $totalWeight): string
    {
        if ($transferValue <= 0) return 'Unknown';
        
        $costPercentage = ($transportCost / $transferValue) * 100;
        
        if ($costPercentage <= 5) return 'Excellent';
        if ($costPercentage <= 10) return 'Good';
        if ($costPercentage <= 15) return 'Average';
        if ($costPercentage <= 25) return 'Poor';
        
        return 'Very Poor';
    }

    /**
     * Get benchmark comparison
     */
    protected function getBenchmarkComparison(float $transportCost, float $transferValue): array
    {
        $costPercentage = $transferValue > 0 ? ($transportCost / $transferValue) * 100 : 0;
        
        // Industry benchmarks (these could be configurable)
        $benchmarks = [
            'excellent' => 5,
            'good' => 10,
            'average' => 15,
            'industry_standard' => 12,
        ];

        return [
            'current_percentage' => $costPercentage,
            'benchmarks' => $benchmarks,
            'performance' => $costPercentage <= $benchmarks['excellent'] ? 'Above Average' :
                           ($costPercentage <= $benchmarks['good'] ? 'Good' :
                           ($costPercentage <= $benchmarks['average'] ? 'Average' : 'Below Average')),
        ];
    }

    /**
     * Get weekly average expenses
     */
    protected function getWeeklyAverage($expenses): float
    {
        if ($expenses->isEmpty()) return 0;
        
        $weeks = $expenses->pluck('expense_date')
                         ->map(fn($date) => $date->format('Y-W'))
                         ->unique()
                         ->count();
                         
        return $weeks > 0 ? $expenses->sum('amount') / $weeks : 0;
    }
}