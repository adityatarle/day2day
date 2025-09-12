<?php

namespace App\Services;

use App\Models\StockFinancialImpact;
use App\Models\StockTransfer;
use App\Models\StockTransferQuery;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FinancialImpactService
{
    /**
     * Create financial impact record
     */
    public function createImpact(array $impactData, User $user): StockFinancialImpact
    {
        try {
            $impact = StockFinancialImpact::create([
                'impactable_type' => $impactData['impactable_type'],
                'impactable_id' => $impactData['impactable_id'],
                'branch_id' => $impactData['branch_id'],
                'impact_type' => $impactData['impact_type'],
                'amount' => $impactData['amount'],
                'impact_category' => $impactData['impact_category'],
                'description' => $impactData['description'],
                'impact_date' => $impactData['impact_date'] ?? now()->toDateString(),
                'is_recoverable' => $impactData['is_recoverable'] ?? false,
                'recovered_amount' => $impactData['recovered_amount'] ?? 0,
                'recovery_notes' => $impactData['recovery_notes'] ?? null,
            ]);

            Log::info("Financial impact created", [
                'impact_id' => $impact->id,
                'type' => $impact->impact_type,
                'amount' => $impact->amount,
                'branch_id' => $impact->branch_id,
                'user_id' => $user->id,
            ]);

            return $impact;

        } catch (Exception $e) {
            Log::error("Failed to create financial impact", [
                'impact_data' => $impactData,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Record recovery for financial impact
     */
    public function recordRecovery(StockFinancialImpact $impact, float $recoveryAmount, string $notes, User $user): bool
    {
        try {
            $currentRecovered = $impact->recovered_amount ?? 0;
            $newRecovered = $currentRecovered + $recoveryAmount;
            
            // Ensure we don't recover more than the impact amount
            $maxRecovery = $impact->amount - $currentRecovered;
            $actualRecovery = min($recoveryAmount, $maxRecovery);

            $result = $impact->update([
                'recovered_amount' => $currentRecovered + $actualRecovery,
                'recovery_notes' => $impact->recovery_notes ? 
                    $impact->recovery_notes . "\n" . now()->format('Y-m-d') . ": " . $notes :
                    now()->format('Y-m-d') . ": " . $notes,
            ]);

            if ($result) {
                Log::info("Financial recovery recorded", [
                    'impact_id' => $impact->id,
                    'recovery_amount' => $actualRecovery,
                    'total_recovered' => $currentRecovered + $actualRecovery,
                    'user_id' => $user->id,
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to record financial recovery", [
                'impact_id' => $impact->id,
                'recovery_amount' => $recoveryAmount,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get financial impact summary for branch
     */
    public function getBranchFinancialSummary(int $branchId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StockFinancialImpact::where('branch_id', $branchId);

        if ($startDate && $endDate) {
            $query->whereBetween('impact_date', [$startDate, $endDate]);
        }

        $impacts = $query->get();

        return [
            'summary' => [
                'total_impacts' => $impacts->count(),
                'total_amount' => $impacts->sum('amount'),
                'total_recovered' => $impacts->sum('recovered_amount'),
                'net_impact' => $impacts->sum('amount') - $impacts->sum('recovered_amount'),
                'recovery_rate' => $this->calculateRecoveryRate($impacts),
            ],
            'by_category' => [
                'direct_loss' => $this->getImpactsByCategory($impacts, 'direct_loss'),
                'indirect_loss' => $this->getImpactsByCategory($impacts, 'indirect_loss'),
                'cost' => $this->getImpactsByCategory($impacts, 'cost'),
                'recovery' => $this->getImpactsByCategory($impacts, 'recovery'),
            ],
            'by_type' => $this->getImpactsByType($impacts),
            'recoverable_analysis' => [
                'recoverable_amount' => $impacts->where('is_recoverable', true)->sum('amount'),
                'recovered_from_recoverable' => $impacts->where('is_recoverable', true)->sum('recovered_amount'),
                'pending_recovery' => $impacts->where('is_recoverable', true)->sum(function ($impact) {
                    return max(0, $impact->amount - $impact->recovered_amount);
                }),
            ],
            'trends' => $this->getFinancialTrends($impacts),
        ];
    }

    /**
     * Get overall financial impact statistics
     */
    public function getOverallFinancialStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = StockFinancialImpact::query();

        if ($startDate && $endDate) {
            $query->whereBetween('impact_date', [$startDate, $endDate]);
        }

        $impacts = $query->with(['branch'])->get();

        return [
            'global_summary' => [
                'total_impacts' => $impacts->count(),
                'total_amount' => $impacts->sum('amount'),
                'total_recovered' => $impacts->sum('recovered_amount'),
                'net_impact' => $impacts->sum('amount') - $impacts->sum('recovered_amount'),
                'affected_branches' => $impacts->pluck('branch_id')->unique()->count(),
                'average_impact_per_branch' => $this->calculateAverageImpactPerBranch($impacts),
            ],
            'by_branch' => $this->getImpactsByBranch($impacts),
            'by_impact_type' => $this->getImpactsByType($impacts),
            'by_category' => $this->getImpactsByAllCategories($impacts),
            'recovery_analysis' => [
                'total_recoverable' => $impacts->where('is_recoverable', true)->sum('amount'),
                'total_recovered' => $impacts->sum('recovered_amount'),
                'recovery_efficiency' => $this->calculateRecoveryEfficiency($impacts),
                'pending_recoveries' => $this->getPendingRecoveries($impacts),
            ],
            'top_impact_sources' => $this->getTopImpactSources($impacts),
            'monthly_trends' => $this->getMonthlyTrends($impacts),
        ];
    }

    /**
     * Generate financial impact report for stock transfers
     */
    public function generateTransferImpactReport(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $transferQuery = StockTransfer::query();
        
        if ($branchId) {
            $transferQuery->where('to_branch_id', $branchId);
        }

        if ($startDate && $endDate) {
            $transferQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transfers = $transferQuery->with(['queries.financialImpacts', 'transportExpenses.financialImpacts'])->get();

        $totalTransferValue = $transfers->sum('total_value');
        $totalTransportCost = $transfers->sum('transport_cost');
        $totalQueryImpacts = $transfers->flatMap->queries->flatMap->financialImpacts->sum('amount');
        $totalTransportImpacts = $transfers->flatMap->transportExpenses->flatMap->financialImpacts->sum('amount');

        return [
            'transfer_summary' => [
                'total_transfers' => $transfers->count(),
                'total_transfer_value' => $totalTransferValue,
                'total_transport_cost' => $totalTransportCost,
                'total_query_impacts' => $totalQueryImpacts,
                'total_financial_impact' => $totalQueryImpacts + $totalTransportImpacts,
            ],
            'cost_analysis' => [
                'transport_cost_percentage' => $totalTransferValue > 0 ? ($totalTransportCost / $totalTransferValue) * 100 : 0,
                'query_impact_percentage' => $totalTransferValue > 0 ? ($totalQueryImpacts / $totalTransferValue) * 100 : 0,
                'total_cost_percentage' => $totalTransferValue > 0 ? (($totalTransportCost + $totalQueryImpacts) / $totalTransferValue) * 100 : 0,
                'average_transport_cost_per_transfer' => $transfers->avg('transport_cost'),
                'average_query_impact_per_transfer' => $transfers->count() > 0 ? $totalQueryImpacts / $transfers->count() : 0,
            ],
            'efficiency_metrics' => [
                'transfers_without_queries' => $transfers->filter(fn($t) => $t->queries->isEmpty())->count(),
                'clean_transfer_rate' => $transfers->count() > 0 ? ($transfers->filter(fn($t) => $t->queries->isEmpty())->count() / $transfers->count()) * 100 : 0,
                'average_queries_per_transfer' => $transfers->avg(fn($t) => $t->queries->count()),
                'most_problematic_transfers' => $this->getMostProblematicTransfers($transfers),
            ],
            'recovery_opportunities' => [
                'recoverable_query_impacts' => $transfers->flatMap->queries->flatMap->financialImpacts->where('is_recoverable', true)->sum('amount'),
                'already_recovered' => $transfers->flatMap->queries->flatMap->financialImpacts->sum('recovered_amount'),
                'pending_recovery_amount' => $this->calculatePendingRecoveryAmount($transfers),
                'recovery_recommendations' => $this->getRecoveryRecommendations($transfers),
            ],
        ];
    }

    /**
     * Get financial impact forecast based on trends
     */
    public function generateFinancialForecast(int $branchId, int $forecastDays = 30): array
    {
        // Get historical data for the last 90 days
        $historicalData = StockFinancialImpact::where('branch_id', $branchId)
            ->where('impact_date', '>=', now()->subDays(90))
            ->get();

        if ($historicalData->isEmpty()) {
            return [
                'forecast_available' => false,
                'message' => 'Insufficient historical data for forecasting',
            ];
        }

        // Calculate trends
        $dailyAverages = $this->calculateDailyAverages($historicalData);
        $trendSlope = $this->calculateTrendSlope($historicalData);
        
        // Generate forecast
        $forecast = [];
        $baseAmount = $dailyAverages['recent_average'];
        
        for ($i = 1; $i <= $forecastDays; $i++) {
            $forecastDate = now()->addDays($i)->toDateString();
            $trendAdjustment = $trendSlope * $i;
            $forecastAmount = max(0, $baseAmount + $trendAdjustment);
            
            $forecast[] = [
                'date' => $forecastDate,
                'predicted_impact' => round($forecastAmount, 2),
                'confidence_level' => $this->calculateConfidenceLevel($i, $historicalData->count()),
            ];
        }

        return [
            'forecast_available' => true,
            'forecast_period_days' => $forecastDays,
            'historical_analysis' => [
                'data_points' => $historicalData->count(),
                'daily_average' => $dailyAverages['overall_average'],
                'recent_trend' => $trendSlope > 0 ? 'increasing' : ($trendSlope < 0 ? 'decreasing' : 'stable'),
                'volatility' => $this->calculateVolatility($historicalData),
            ],
            'forecast_data' => $forecast,
            'summary' => [
                'total_predicted_impact' => array_sum(array_column($forecast, 'predicted_impact')),
                'average_daily_impact' => array_sum(array_column($forecast, 'predicted_impact')) / $forecastDays,
                'risk_level' => $this->assessRiskLevel($forecast),
            ],
            'recommendations' => $this->generateForecastRecommendations($forecast, $trendSlope),
        ];
    }

    /**
     * Calculate recovery rate percentage
     */
    protected function calculateRecoveryRate($impacts): float
    {
        $totalAmount = $impacts->sum('amount');
        $totalRecovered = $impacts->sum('recovered_amount');
        
        return $totalAmount > 0 ? ($totalRecovered / $totalAmount) * 100 : 0;
    }

    /**
     * Get impacts by category
     */
    protected function getImpactsByCategory($impacts, string $category): array
    {
        $categoryImpacts = $impacts->where('impact_category', $category);
        
        return [
            'count' => $categoryImpacts->count(),
            'total_amount' => $categoryImpacts->sum('amount'),
            'recovered_amount' => $categoryImpacts->sum('recovered_amount'),
            'net_amount' => $categoryImpacts->sum('amount') - $categoryImpacts->sum('recovered_amount'),
        ];
    }

    /**
     * Get impacts by type
     */
    protected function getImpactsByType($impacts): array
    {
        return $impacts->groupBy('impact_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'recovered_amount' => $group->sum('recovered_amount'),
                'net_amount' => $group->sum('amount') - $group->sum('recovered_amount'),
                'recovery_rate' => $this->calculateRecoveryRate($group),
            ];
        })->toArray();
    }

    /**
     * Get impacts by all categories
     */
    protected function getImpactsByAllCategories($impacts): array
    {
        return $impacts->groupBy('impact_category')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'recovered_amount' => $group->sum('recovered_amount'),
                'net_amount' => $group->sum('amount') - $group->sum('recovered_amount'),
            ];
        })->toArray();
    }

    /**
     * Get impacts by branch
     */
    protected function getImpactsByBranch($impacts): array
    {
        return $impacts->groupBy('branch_id')->map(function ($group) {
            $branch = $group->first()->branch;
            return [
                'branch_id' => $group->first()->branch_id,
                'branch_name' => $branch ? $branch->name : 'Unknown',
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'recovered_amount' => $group->sum('recovered_amount'),
                'net_amount' => $group->sum('amount') - $group->sum('recovered_amount'),
                'recovery_rate' => $this->calculateRecoveryRate($group),
            ];
        })->sortByDesc('total_amount')->values()->toArray();
    }

    /**
     * Calculate average impact per branch
     */
    protected function calculateAverageImpactPerBranch($impacts): float
    {
        $branchCount = $impacts->pluck('branch_id')->unique()->count();
        return $branchCount > 0 ? $impacts->sum('amount') / $branchCount : 0;
    }

    /**
     * Calculate recovery efficiency
     */
    protected function calculateRecoveryEfficiency($impacts): array
    {
        $recoverableImpacts = $impacts->where('is_recoverable', true);
        $totalRecoverable = $recoverableImpacts->sum('amount');
        $totalRecovered = $recoverableImpacts->sum('recovered_amount');
        
        return [
            'efficiency_percentage' => $totalRecoverable > 0 ? ($totalRecovered / $totalRecoverable) * 100 : 0,
            'total_recoverable' => $totalRecoverable,
            'total_recovered' => $totalRecovered,
            'pending_recovery' => $totalRecoverable - $totalRecovered,
        ];
    }

    /**
     * Get pending recoveries
     */
    protected function getPendingRecoveries($impacts): array
    {
        return $impacts->where('is_recoverable', true)
                      ->filter(function ($impact) {
                          return $impact->amount > $impact->recovered_amount;
                      })
                      ->map(function ($impact) {
                          return [
                              'impact_id' => $impact->id,
                              'branch_id' => $impact->branch_id,
                              'impact_type' => $impact->impact_type,
                              'total_amount' => $impact->amount,
                              'recovered_amount' => $impact->recovered_amount,
                              'pending_amount' => $impact->amount - $impact->recovered_amount,
                              'age_days' => now()->diffInDays($impact->impact_date),
                          ];
                      })
                      ->sortByDesc('pending_amount')
                      ->values()
                      ->toArray();
    }

    /**
     * Get top impact sources
     */
    protected function getTopImpactSources($impacts): array
    {
        return $impacts->groupBy('impactable_type')->map(function ($group, $type) {
            return [
                'source_type' => class_basename($type),
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'average_amount' => $group->avg('amount'),
            ];
        })->sortByDesc('total_amount')->values()->toArray();
    }

    /**
     * Get monthly trends
     */
    protected function getMonthlyTrends($impacts): array
    {
        return $impacts->groupBy(function ($impact) {
            return $impact->impact_date->format('Y-m');
        })->map(function ($group, $month) {
            return [
                'month' => $month,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'recovered_amount' => $group->sum('recovered_amount'),
                'net_amount' => $group->sum('amount') - $group->sum('recovered_amount'),
            ];
        })->sortBy('month')->values()->toArray();
    }

    /**
     * Get financial trends
     */
    protected function getFinancialTrends($impacts): array
    {
        $monthlyData = $this->getMonthlyTrends($impacts);
        
        if (count($monthlyData) < 2) {
            return ['trend' => 'insufficient_data'];
        }

        $recentAmount = end($monthlyData)['total_amount'];
        $previousAmount = prev($monthlyData)['total_amount'];
        
        $trendPercentage = $previousAmount > 0 ? (($recentAmount - $previousAmount) / $previousAmount) * 100 : 0;
        
        return [
            'trend' => $trendPercentage > 5 ? 'increasing' : ($trendPercentage < -5 ? 'decreasing' : 'stable'),
            'trend_percentage' => $trendPercentage,
            'monthly_data' => $monthlyData,
        ];
    }

    /**
     * Get most problematic transfers
     */
    protected function getMostProblematicTransfers($transfers): array
    {
        return $transfers->map(function ($transfer) {
            $queryImpacts = $transfer->queries->flatMap->financialImpacts->sum('amount');
            return [
                'transfer_number' => $transfer->transfer_number,
                'branch' => $transfer->toBranch->name,
                'query_count' => $transfer->queries->count(),
                'financial_impact' => $queryImpacts,
                'impact_percentage' => $transfer->total_value > 0 ? ($queryImpacts / $transfer->total_value) * 100 : 0,
                'date' => $transfer->created_at->format('Y-m-d'),
            ];
        })->sortByDesc('financial_impact')->take(10)->values()->toArray();
    }

    /**
     * Calculate pending recovery amount
     */
    protected function calculatePendingRecoveryAmount($transfers): float
    {
        return $transfers->flatMap->queries
                        ->flatMap->financialImpacts
                        ->where('is_recoverable', true)
                        ->sum(function ($impact) {
                            return max(0, $impact->amount - $impact->recovered_amount);
                        });
    }

    /**
     * Get recovery recommendations
     */
    protected function getRecoveryRecommendations($transfers): array
    {
        $recommendations = [];
        
        $pendingRecoveries = $transfers->flatMap->queries
                                     ->flatMap->financialImpacts
                                     ->where('is_recoverable', true)
                                     ->filter(function ($impact) {
                                         return $impact->amount > $impact->recovered_amount;
                                     });

        if ($pendingRecoveries->isNotEmpty()) {
            $recommendations[] = [
                'type' => 'pending_recoveries',
                'priority' => 'high',
                'message' => "There are {$pendingRecoveries->count()} pending recoveries worth " . number_format($pendingRecoveries->sum(fn($i) => $i->amount - $i->recovered_amount), 2),
                'action' => 'Review and initiate recovery processes',
            ];
        }

        $highImpactQueries = $transfers->flatMap->queries
                                     ->filter(function ($query) {
                                         return $query->financial_impact > 1000 && $query->status !== 'resolved';
                                     });

        if ($highImpactQueries->isNotEmpty()) {
            $recommendations[] = [
                'type' => 'high_impact_queries',
                'priority' => 'critical',
                'message' => "There are {$highImpactQueries->count()} unresolved queries with high financial impact",
                'action' => 'Prioritize resolution of high-impact queries',
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate daily averages for forecasting
     */
    protected function calculateDailyAverages($impacts): array
    {
        $dailyTotals = $impacts->groupBy(function ($impact) {
            return $impact->impact_date->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('amount');
        });

        $recentDays = 14; // Last 14 days for recent average
        $recentData = $dailyTotals->take(-$recentDays);

        return [
            'overall_average' => $dailyTotals->avg(),
            'recent_average' => $recentData->avg(),
            'daily_totals' => $dailyTotals->toArray(),
        ];
    }

    /**
     * Calculate trend slope for forecasting
     */
    protected function calculateTrendSlope($impacts): float
    {
        $dailyTotals = $impacts->groupBy(function ($impact) {
            return $impact->impact_date->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('amount');
        })->values();

        if ($dailyTotals->count() < 2) {
            return 0;
        }

        // Simple linear regression slope calculation
        $n = $dailyTotals->count();
        $x = range(1, $n);
        $y = $dailyTotals->toArray();
        
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(function($xi, $yi) { return $xi * $yi; }, $x, $y));
        $sumX2 = array_sum(array_map(function($xi) { return $xi * $xi; }, $x));
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        
        return $slope;
    }

    /**
     * Calculate confidence level for forecast
     */
    protected function calculateConfidenceLevel(int $dayOffset, int $historicalDataPoints): float
    {
        // Confidence decreases with time and increases with more historical data
        $baseConfidence = min(90, $historicalDataPoints * 2); // Max 90% base confidence
        $timeDecay = max(0, $baseConfidence - ($dayOffset * 2)); // Decrease by 2% per day
        
        return max(10, $timeDecay); // Minimum 10% confidence
    }

    /**
     * Calculate volatility of historical data
     */
    protected function calculateVolatility($impacts): float
    {
        $dailyTotals = $impacts->groupBy(function ($impact) {
            return $impact->impact_date->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('amount');
        })->values();

        if ($dailyTotals->count() < 2) {
            return 0;
        }

        $mean = $dailyTotals->avg();
        $variance = $dailyTotals->map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        })->avg();

        return sqrt($variance);
    }

    /**
     * Assess risk level based on forecast
     */
    protected function assessRiskLevel(array $forecast): string
    {
        $totalPredicted = array_sum(array_column($forecast, 'predicted_impact'));
        $averageDaily = $totalPredicted / count($forecast);
        
        if ($averageDaily > 1000) return 'high';
        if ($averageDaily > 500) return 'medium';
        return 'low';
    }

    /**
     * Generate forecast recommendations
     */
    protected function generateForecastRecommendations(array $forecast, float $trendSlope): array
    {
        $recommendations = [];
        
        if ($trendSlope > 10) {
            $recommendations[] = [
                'type' => 'increasing_trend',
                'priority' => 'high',
                'message' => 'Financial impacts are trending upward. Consider reviewing stock transfer processes.',
                'action' => 'Investigate causes of increasing financial impacts',
            ];
        }

        $highImpactDays = array_filter($forecast, fn($day) => $day['predicted_impact'] > 500);
        if (count($highImpactDays) > count($forecast) * 0.3) {
            $recommendations[] = [
                'type' => 'high_impact_forecast',
                'priority' => 'medium',
                'message' => 'Multiple days with high predicted financial impact.',
                'action' => 'Prepare contingency measures and increase monitoring',
            ];
        }

        return $recommendations;
    }
}