<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferQuery;
use App\Models\StockFinancialImpact;
use App\Models\TransportExpense;
use App\Models\Branch;
use App\Models\StockAlert;
use App\Services\StockTransferService;
use App\Services\StockQueryService;
use App\Services\FinancialImpactService;
use App\Services\TransportExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected $transferService;
    protected $queryService;
    protected $financialService;
    protected $expenseService;

    public function __construct(
        StockTransferService $transferService,
        StockQueryService $queryService,
        FinancialImpactService $financialService,
        TransportExpenseService $expenseService
    ) {
        $this->transferService = $transferService;
        $this->queryService = $queryService;
        $this->financialService = $financialService;
        $this->expenseService = $expenseService;
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Main admin dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $branchId = $request->get('branch_id');

        // Cache dashboard data for 5 minutes to improve performance
        $cacheKey = "admin_dashboard_{$period}_{$branchId}_" . Auth::id();
        
        $dashboardData = Cache::remember($cacheKey, 300, function () use ($period, $branchId) {
            return $this->getDashboardData($period, $branchId);
        });

        $branches = Branch::active()->get();
        $alerts = $this->getCriticalAlerts();

        return view('admin.dashboard.index', compact('dashboardData', 'branches', 'period', 'alerts'));
    }

    /**
     * Stock transfers overview
     */
    public function transfers(Request $request)
    {
        $period = $request->get('period', 'month');
        $branchId = $request->get('branch_id');

        $transferStats = $this->transferService->generatePerformanceReport($branchId, ...$this->getDateRange($period));
        $recentTransfers = $this->getRecentTransfers($branchId);
        $overdueTransfers = $this->transferService->getOverdueTransfers($branchId);
        
        $branches = Branch::active()->get();

        return view('admin.dashboard.transfers', compact(
            'transferStats', 'recentTransfers', 'overdueTransfers', 'branches', 'period'
        ));
    }

    /**
     * Queries overview
     */
    public function queries(Request $request)
    {
        $period = $request->get('period', 'month');
        $branchId = $request->get('branch_id');

        $queryStats = $this->queryService->getQueryStatistics($branchId, ...$this->getDateRange($period));
        $criticalQueries = $this->getCriticalQueries($branchId);
        $overdueQueries = $this->queryService->getOverdueQueries($branchId);
        $queryTrends = $this->queryService->generateTrendsReport($branchId, $this->getPeriodDays($period));

        $branches = Branch::active()->get();

        return view('admin.dashboard.queries', compact(
            'queryStats', 'criticalQueries', 'overdueQueries', 'queryTrends', 'branches', 'period'
        ));
    }

    /**
     * Financial overview
     */
    public function financial(Request $request)
    {
        $period = $request->get('period', 'month');
        $branchId = $request->get('branch_id');

        $dateRange = $this->getDateRange($period);
        
        $financialStats = $this->financialService->getOverallFinancialStatistics(...$dateRange);
        $transportStats = $this->expenseService->generateExpenseReport($branchId, ...$dateRange);
        $impactSummary = StockFinancialImpact::getImpactSummary($branchId, $period);
        $recoveryOpportunities = $this->getRecoveryOpportunities($branchId);

        $branches = Branch::active()->get();

        return view('admin.dashboard.financial', compact(
            'financialStats', 'transportStats', 'impactSummary', 'recoveryOpportunities', 'branches', 'period'
        ));
    }

    /**
     * Performance metrics
     */
    public function performance(Request $request)
    {
        $period = $request->get('period', 'month');
        
        $performanceData = [
            'kpis' => $this->getKPIs($period),
            'branch_performance' => $this->getBranchPerformanceComparison($period),
            'efficiency_metrics' => $this->getEfficiencyMetrics($period),
            'quality_metrics' => $this->getQualityMetrics($period),
            'trends' => $this->getPerformanceTrends($period),
        ];

        $branches = Branch::active()->get();

        return view('admin.dashboard.performance', compact('performanceData', 'branches', 'period'));
    }

    /**
     * Alerts and notifications
     */
    public function alerts(Request $request)
    {
        $severity = $request->get('severity');
        $type = $request->get('alert_type');
        $branchId = $request->get('branch_id');

        $query = StockAlert::with(['branch', 'stockTransfer'])
                          ->orderBy('created_at', 'desc');

        if ($severity) {
            $query->where('severity', $severity);
        }

        if ($type) {
            $query->where('alert_type', $type);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $alerts = $query->paginate(20);
        
        $alertStats = [
            'total' => StockAlert::count(),
            'unread' => StockAlert::where('is_read', false)->count(),
            'critical' => StockAlert::where('severity', 'critical')->where('is_resolved', false)->count(),
            'by_type' => StockAlert::groupBy('alert_type')->selectRaw('alert_type, count(*) as count')->pluck('count', 'alert_type'),
        ];

        $branches = Branch::active()->get();

        return view('admin.dashboard.alerts', compact('alerts', 'alertStats', 'branches'));
    }

    /**
     * Real-time monitoring
     */
    public function monitoring()
    {
        $realTimeData = [
            'active_transfers' => StockTransfer::whereIn('status', ['in_transit', 'delivered'])->count(),
            'pending_queries' => StockTransferQuery::where('status', 'open')->count(),
            'critical_alerts' => StockAlert::where('severity', 'critical')->where('is_resolved', false)->count(),
            'overdue_transfers' => $this->transferService->getOverdueTransfers()->count(),
            'recent_activities' => $this->getRecentActivities(),
            'system_health' => $this->getSystemHealth(),
        ];

        return view('admin.dashboard.monitoring', compact('realTimeData'));
    }

    /**
     * Generate comprehensive report
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:executive,operational,financial,performance',
            'period' => 'required|in:week,month,quarter,year',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'required|in:html,pdf',
        ]);

        $dateRange = $this->getDateRange($request->period);
        $branchId = $request->branch_id;

        switch ($request->report_type) {
            case 'executive':
                $data = $this->generateExecutiveReport($branchId, ...$dateRange);
                break;
            case 'operational':
                $data = $this->generateOperationalReport($branchId, ...$dateRange);
                break;
            case 'financial':
                $data = $this->generateFinancialReport($branchId, ...$dateRange);
                break;
            case 'performance':
                $data = $this->generatePerformanceReport($branchId, ...$dateRange);
                break;
        }

        if ($request->format === 'html') {
            return view('admin.dashboard.report', compact('data', 'request'));
        } else {
            // Generate PDF (placeholder)
            return back()->with('error', 'PDF export not yet implemented.');
        }
    }

    /**
     * API endpoint for dashboard widgets
     */
    public function widgetData(Request $request)
    {
        $widget = $request->get('widget');
        $period = $request->get('period', 'month');
        $branchId = $request->get('branch_id');

        switch ($widget) {
            case 'transfers_summary':
                $data = $this->transferService->getTransferStatistics($branchId, ...$this->getDateRange($period));
                break;
            case 'queries_summary':
                $data = $this->queryService->getQueryStatistics($branchId, ...$this->getDateRange($period));
                break;
            case 'financial_summary':
                $data = StockFinancialImpact::getImpactSummary($branchId, $period);
                break;
            case 'alerts_summary':
                $data = $this->getAlertsData($branchId);
                break;
            case 'performance_chart':
                $data = $this->getPerformanceChartData($period, $branchId);
                break;
            default:
                return response()->json(['error' => 'Invalid widget'], 400);
        }

        return response()->json($data);
    }

    /**
     * Get main dashboard data
     */
    protected function getDashboardData(string $period, ?int $branchId): array
    {
        $dateRange = $this->getDateRange($period);

        return [
            'summary' => [
                'transfers' => $this->transferService->getTransferStatistics($branchId, ...$dateRange),
                'queries' => $this->queryService->getQueryStatistics($branchId, ...$dateRange),
                'financial' => StockFinancialImpact::getImpactSummary($branchId, $period),
                'transport' => $this->expenseService->getExpenseStatistics(null, $branchId, ...$dateRange),
            ],
            'recent_activities' => $this->getRecentActivities($branchId),
            'critical_items' => [
                'overdue_transfers' => $this->transferService->getOverdueTransfers($branchId)->take(5),
                'critical_queries' => $this->getCriticalQueries($branchId, 5),
                'high_impact_issues' => $this->getHighImpactIssues($branchId, 5),
            ],
            'performance_indicators' => $this->getKPIs($period, $branchId),
            'trends' => [
                'transfers' => $this->getTransferTrends($period, $branchId),
                'queries' => $this->queryService->generateTrendsReport($branchId, $this->getPeriodDays($period)),
                'financial' => $this->getFinancialTrends($period, $branchId),
            ],
        ];
    }

    /**
     * Get critical alerts
     */
    protected function getCriticalAlerts(): \Illuminate\Database\Eloquent\Collection
    {
        return StockAlert::where('severity', 'critical')
                        ->where('is_resolved', false)
                        ->with(['branch', 'stockTransfer'])
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
    }

    /**
     * Get recent transfers
     */
    protected function getRecentTransfers(?int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockTransfer::with(['toBranch', 'fromBranch', 'items.product'])
                              ->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->where('to_branch_id', $branchId);
        }

        return $query->limit(10)->get();
    }

    /**
     * Get critical queries
     */
    protected function getCriticalQueries(?int $branchId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockTransferQuery::where('priority', 'critical')
                                  ->whereIn('status', ['open', 'in_progress'])
                                  ->with(['stockTransfer.toBranch', 'raisedBy'])
                                  ->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get recovery opportunities
     */
    protected function getRecoveryOpportunities(?int $branchId): array
    {
        $query = StockFinancialImpact::where('is_recoverable', true)
                                   ->whereRaw('amount > recovered_amount');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $opportunities = $query->with(['branch', 'impactable'])->get();

        return [
            'total_opportunities' => $opportunities->count(),
            'total_recoverable' => $opportunities->sum(fn($i) => $i->getRemainingRecoverableAmount()),
            'urgent_opportunities' => $opportunities->filter(fn($i) => $i->requiresUrgentAttention())->count(),
            'top_opportunities' => $opportunities->sortByDesc(fn($i) => $i->getRemainingRecoverableAmount())
                                                ->take(10)
                                                ->map(function ($impact) {
                                                    return [
                                                        'id' => $impact->id,
                                                        'branch' => $impact->branch->name,
                                                        'type' => $impact->getImpactTypeDisplayName(),
                                                        'remaining_amount' => $impact->getRemainingRecoverableAmount(),
                                                        'age_days' => $impact->getAgeInDays(),
                                                        'description' => $impact->description,
                                                    ];
                                                })
                                                ->values()
                                                ->toArray(),
        ];
    }

    /**
     * Get KPIs for the period
     */
    protected function getKPIs(string $period, ?int $branchId = null): array
    {
        $dateRange = $this->getDateRange($period);
        
        $transferStats = $this->transferService->getTransferStatistics($branchId, ...$dateRange);
        $queryStats = $this->queryService->getQueryStatistics($branchId, ...$dateRange);
        $financialStats = StockFinancialImpact::getImpactSummary($branchId, $period);

        return [
            'transfer_success_rate' => $transferStats['confirmed_transfers'] > 0 ? 
                ($transferStats['confirmed_transfers'] / $transferStats['total_transfers']) * 100 : 0,
            'query_resolution_rate' => $queryStats['total_queries'] > 0 ? 
                ($queryStats['resolved_queries'] / $queryStats['total_queries']) * 100 : 0,
            'on_time_delivery_rate' => $this->calculateOnTimeDeliveryRate($branchId, ...$dateRange),
            'cost_efficiency_ratio' => $this->calculateCostEfficiencyRatio($branchId, ...$dateRange),
            'recovery_rate' => $financialStats['recovery_rate'],
            'average_resolution_time' => $queryStats['average_resolution_time'] ?? 0,
            'transport_cost_percentage' => $this->calculateTransportCostPercentage($branchId, ...$dateRange),
        ];
    }

    /**
     * Get branch performance comparison
     */
    protected function getBranchPerformanceComparison(string $period): array
    {
        $branches = Branch::active()->get();
        $dateRange = $this->getDateRange($period);
        
        $comparison = [];
        
        foreach ($branches as $branch) {
            $transferStats = $this->transferService->getTransferStatistics($branch->id, ...$dateRange);
            $queryStats = $this->queryService->getQueryStatistics($branch->id, ...$dateRange);
            $financialStats = StockFinancialImpact::getImpactSummary($branch->id, $period);
            
            $comparison[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'total_transfers' => $transferStats['total_transfers'],
                'total_queries' => $queryStats['total_queries'],
                'query_rate' => $transferStats['total_transfers'] > 0 ? 
                    ($queryStats['total_queries'] / $transferStats['total_transfers']) * 100 : 0,
                'financial_impact' => $financialStats['net_impact'],
                'resolution_rate' => $queryStats['total_queries'] > 0 ? 
                    ($queryStats['resolved_queries'] / $queryStats['total_queries']) * 100 : 0,
                'performance_score' => $this->calculateBranchPerformanceScore($branch->id, $period),
            ];
        }

        return collect($comparison)->sortByDesc('performance_score')->toArray();
    }

    /**
     * Get efficiency metrics
     */
    protected function getEfficiencyMetrics(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'average_transfer_time' => $this->calculateAverageTransferTime(...$dateRange),
            'resource_utilization' => $this->calculateResourceUtilization(...$dateRange),
            'automation_rate' => $this->calculateAutomationRate(...$dateRange),
            'error_rate' => $this->calculateErrorRate(...$dateRange),
            'cost_per_transfer' => $this->calculateCostPerTransfer(...$dateRange),
        ];
    }

    /**
     * Get quality metrics
     */
    protected function getQualityMetrics(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'defect_rate' => $this->calculateDefectRate(...$dateRange),
            'customer_satisfaction' => $this->calculateCustomerSatisfaction(...$dateRange),
            'first_pass_yield' => $this->calculateFirstPassYield(...$dateRange),
            'rework_rate' => $this->calculateReworkRate(...$dateRange),
        ];
    }

    /**
     * Get performance trends
     */
    protected function getPerformanceTrends(string $period): array
    {
        $days = $this->getPeriodDays($period);
        $trends = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trends[] = [
                'date' => $date,
                'transfers' => StockTransfer::whereDate('created_at', $date)->count(),
                'queries' => StockTransferQuery::whereDate('created_at', $date)->count(),
                'financial_impact' => StockFinancialImpact::whereDate('impact_date', $date)->sum('amount'),
            ];
        }

        return $trends;
    }

    /**
     * Get recent activities
     */
    protected function getRecentActivities(?int $branchId = null): array
    {
        $activities = [];
        
        // Recent transfers
        $recentTransfers = StockTransfer::when($branchId, function ($q, $branchId) {
                                         $q->where('to_branch_id', $branchId);
                                     })
                                     ->with(['toBranch'])
                                     ->orderBy('created_at', 'desc')
                                     ->limit(5)
                                     ->get();

        foreach ($recentTransfers as $transfer) {
            $activities[] = [
                'type' => 'transfer',
                'title' => "Transfer {$transfer->transfer_number} to {$transfer->toBranch->name}",
                'status' => $transfer->status,
                'timestamp' => $transfer->created_at,
                'url' => route('admin.stock-transfers.show', $transfer),
            ];
        }

        // Recent queries
        $recentQueries = StockTransferQuery::when($branchId, function ($q, $branchId) {
                                              $q->whereHas('stockTransfer', function ($sq) use ($branchId) {
                                                  $sq->where('to_branch_id', $branchId);
                                              });
                                          })
                                          ->with(['stockTransfer.toBranch'])
                                          ->orderBy('created_at', 'desc')
                                          ->limit(5)
                                          ->get();

        foreach ($recentQueries as $query) {
            $activities[] = [
                'type' => 'query',
                'title' => "Query {$query->query_number}: {$query->title}",
                'status' => $query->status,
                'timestamp' => $query->created_at,
                'url' => route('admin.stock-queries.show', $query),
            ];
        }

        // Sort by timestamp
        usort($activities, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get system health metrics
     */
    protected function getSystemHealth(): array
    {
        return [
            'database_status' => $this->checkDatabaseHealth(),
            'storage_usage' => $this->getStorageUsage(),
            'active_users' => $this->getActiveUsersCount(),
            'response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getSystemErrorRate(),
        ];
    }

    /**
     * Get high impact issues
     */
    protected function getHighImpactIssues(?int $branchId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockFinancialImpact::where('amount', '>', 1000)
                                   ->where('is_resolved', false)
                                   ->with(['branch', 'impactable'])
                                   ->orderBy('amount', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get transfer trends
     */
    protected function getTransferTrends(string $period, ?int $branchId): array
    {
        $days = $this->getPeriodDays($period);
        $trends = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $query = StockTransfer::whereDate('created_at', $date);
            
            if ($branchId) {
                $query->where('to_branch_id', $branchId);
            }
            
            $trends[] = [
                'date' => $date,
                'count' => $query->count(),
                'value' => $query->sum('total_value'),
            ];
        }

        return $trends;
    }

    /**
     * Get financial trends
     */
    protected function getFinancialTrends(string $period, ?int $branchId): array
    {
        $days = $this->getPeriodDays($period);
        $trends = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $query = StockFinancialImpact::whereDate('impact_date', $date);
            
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            
            $trends[] = [
                'date' => $date,
                'total_impact' => $query->sum('amount'),
                'recovered' => $query->sum('recovered_amount'),
            ];
        }

        return $trends;
    }

    /**
     * Get date range for period
     */
    protected function getDateRange(string $period): array
    {
        switch ($period) {
            case 'week':
                return [now()->subWeek()->toDateString(), now()->toDateString()];
            case 'month':
                return [now()->subMonth()->toDateString(), now()->toDateString()];
            case 'quarter':
                return [now()->subQuarter()->toDateString(), now()->toDateString()];
            case 'year':
                return [now()->subYear()->toDateString(), now()->toDateString()];
            default:
                return [now()->subMonth()->toDateString(), now()->toDateString()];
        }
    }

    /**
     * Get period in days
     */
    protected function getPeriodDays(string $period): int
    {
        return match($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365,
            default => 30,
        };
    }

    // Additional helper methods would be implemented here for various calculations
    // These are placeholders for the actual implementation

    protected function calculateOnTimeDeliveryRate(?int $branchId, string $startDate, string $endDate): float
    {
        // Implementation for on-time delivery rate calculation
        return 0.0;
    }

    protected function calculateCostEfficiencyRatio(?int $branchId, string $startDate, string $endDate): float
    {
        // Implementation for cost efficiency ratio calculation
        return 0.0;
    }

    protected function calculateTransportCostPercentage(?int $branchId, string $startDate, string $endDate): float
    {
        // Implementation for transport cost percentage calculation
        return 0.0;
    }

    protected function calculateBranchPerformanceScore(int $branchId, string $period): float
    {
        // Implementation for branch performance score calculation
        return 0.0;
    }

    protected function calculateAverageTransferTime(string $startDate, string $endDate): float
    {
        // Implementation for average transfer time calculation
        return 0.0;
    }

    protected function calculateResourceUtilization(string $startDate, string $endDate): float
    {
        // Implementation for resource utilization calculation
        return 0.0;
    }

    protected function calculateAutomationRate(string $startDate, string $endDate): float
    {
        // Implementation for automation rate calculation
        return 0.0;
    }

    protected function calculateErrorRate(string $startDate, string $endDate): float
    {
        // Implementation for error rate calculation
        return 0.0;
    }

    protected function calculateCostPerTransfer(string $startDate, string $endDate): float
    {
        // Implementation for cost per transfer calculation
        return 0.0;
    }

    protected function calculateDefectRate(string $startDate, string $endDate): float
    {
        // Implementation for defect rate calculation
        return 0.0;
    }

    protected function calculateCustomerSatisfaction(string $startDate, string $endDate): float
    {
        // Implementation for customer satisfaction calculation
        return 0.0;
    }

    protected function calculateFirstPassYield(string $startDate, string $endDate): float
    {
        // Implementation for first pass yield calculation
        return 0.0;
    }

    protected function calculateReworkRate(string $startDate, string $endDate): float
    {
        // Implementation for rework rate calculation
        return 0.0;
    }

    protected function checkDatabaseHealth(): string
    {
        // Implementation for database health check
        return 'healthy';
    }

    protected function getStorageUsage(): array
    {
        // Implementation for storage usage check
        return ['used' => 0, 'total' => 0, 'percentage' => 0];
    }

    protected function getActiveUsersCount(): int
    {
        // Implementation for active users count
        return 0;
    }

    protected function getAverageResponseTime(): float
    {
        // Implementation for average response time calculation
        return 0.0;
    }

    protected function getSystemErrorRate(): float
    {
        // Implementation for system error rate calculation
        return 0.0;
    }

    protected function getAlertsData(?int $branchId): array
    {
        // Implementation for alerts data
        return [];
    }

    protected function getPerformanceChartData(string $period, ?int $branchId): array
    {
        // Implementation for performance chart data
        return [];
    }

    protected function generateExecutiveReport(?int $branchId, string $startDate, string $endDate): array
    {
        // Implementation for executive report generation
        return [];
    }

    protected function generateOperationalReport(?int $branchId, string $startDate, string $endDate): array
    {
        // Implementation for operational report generation
        return [];
    }

    protected function generateFinancialReport(?int $branchId, string $startDate, string $endDate): array
    {
        // Implementation for financial report generation
        return [];
    }

    protected function generatePerformanceReport(?int $branchId, string $startDate, string $endDate): array
    {
        // Implementation for performance report generation
        return [];
    }
}