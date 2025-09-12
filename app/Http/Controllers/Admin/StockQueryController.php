<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransferQuery;
use App\Models\StockTransfer;
use App\Models\Branch;
use App\Models\User;
use App\Services\StockQueryService;
use App\Services\FinancialImpactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockQueryController extends Controller
{
    protected $queryService;
    protected $financialService;

    public function __construct(StockQueryService $queryService, FinancialImpactService $financialService)
    {
        $this->queryService = $queryService;
        $this->financialService = $financialService;
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Display listing of stock queries
     */
    public function index(Request $request)
    {
        $query = StockTransferQuery::with([
            'stockTransfer.toBranch', 'stockTransferItem.product', 
            'raisedBy', 'assignedTo', 'responses'
        ])->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('query_type')) {
            $query->where('query_type', $request->query_type);
        }

        if ($request->filled('branch_id')) {
            $query->whereHas('stockTransfer', function ($q) use ($request) {
                $q->where('to_branch_id', $request->branch_id);
            });
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('financial_impact_min')) {
            $query->where('financial_impact', '>=', $request->financial_impact_min);
        }

        $queries = $query->paginate(20);

        // Get filter options
        $branches = Branch::active()->get();
        $admins = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        })->get();

        // Get summary statistics
        $stats = $this->queryService->getQueryStatistics();

        return view('admin.stock-queries.index', compact('queries', 'branches', 'admins', 'stats'));
    }

    /**
     * Show specific query details
     */
    public function show(StockTransferQuery $stockQuery)
    {
        $stockQuery->load([
            'stockTransfer.toBranch', 'stockTransfer.items.product',
            'stockTransferItem.product', 'raisedBy', 'assignedTo', 
            'responses.user', 'financialImpacts'
        ]);

        // Get similar queries for reference
        $similarQueries = $this->getSimilarQueries($stockQuery);

        return view('admin.stock-queries.show', compact('stockQuery', 'similarQueries'));
    }

    /**
     * Assign query to admin
     */
    public function assign(Request $request, StockTransferQuery $stockQuery)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $admin = User::find($request->assigned_to);
        
        if (!$admin->hasRole(['admin', 'super_admin'])) {
            return back()->with('error', 'Can only assign to admin users.');
        }

        try {
            $result = $this->queryService->assignQuery($stockQuery, $admin);

            if ($result && $request->filled('notes')) {
                $this->queryService->addResponse(
                    $stockQuery,
                    Auth::user(),
                    "Assignment Notes: " . $request->notes,
                    'status_update',
                    null,
                    true
                );
            }

            return back()->with('success', 'Query assigned successfully to ' . $admin->name . '.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign query: ' . $e->getMessage());
        }
    }

    /**
     * Add response to query
     */
    public function addResponse(Request $request, StockTransferQuery $stockQuery)
    {
        $request->validate([
            'message' => 'required|string',
            'response_type' => 'required|in:comment,status_update,resolution',
            'is_internal' => 'boolean',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        try {
            $attachments = null;
            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store("query-responses/{$stockQuery->id}", 'public');
                    $attachments[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'uploaded_at' => now()->toISOString(),
                    ];
                }
            }

            $this->queryService->addResponse(
                $stockQuery,
                Auth::user(),
                $request->message,
                $request->response_type,
                $attachments,
                $request->boolean('is_internal')
            );

            return back()->with('success', 'Response added successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add response: ' . $e->getMessage());
        }
    }

    /**
     * Resolve query
     */
    public function resolve(Request $request, StockTransferQuery $stockQuery)
    {
        $request->validate([
            'resolution' => 'required|string',
            'recovery_amount' => 'nullable|numeric|min:0',
            'financial_adjustment' => 'nullable|numeric',
            'is_recoverable' => 'boolean',
        ]);

        try {
            $result = $this->queryService->resolveQuery(
                $stockQuery,
                $request->resolution,
                Auth::user(),
                $request->recovery_amount
            );

            if ($result) {
                // Update financial impact if adjustment provided
                if ($request->filled('financial_adjustment')) {
                    $stockQuery->updateFinancialImpact($request->financial_adjustment);
                }

                // Update recoverability if specified
                if ($request->has('is_recoverable')) {
                    $stockQuery->financialImpacts()->update([
                        'is_recoverable' => $request->boolean('is_recoverable')
                    ]);
                }

                return back()->with('success', 'Query resolved successfully.');
            } else {
                return back()->with('error', 'Failed to resolve query.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resolve query: ' . $e->getMessage());
        }
    }

    /**
     * Close query
     */
    public function close(Request $request, StockTransferQuery $stockQuery)
    {
        $request->validate([
            'closure_notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->queryService->closeQuery(
                $stockQuery, 
                Auth::user(), 
                $request->closure_notes
            );

            if ($result) {
                return redirect()->route('admin.stock-queries.index')
                               ->with('success', 'Query closed successfully.');
            } else {
                return back()->with('error', 'Failed to close query.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to close query: ' . $e->getMessage());
        }
    }

    /**
     * Escalate query
     */
    public function escalate(Request $request, StockTransferQuery $stockQuery)
    {
        $request->validate([
            'escalation_reason' => 'required|string|max:500',
            'escalate_to' => 'nullable|exists:users,id',
        ]);

        try {
            $result = $this->queryService->escalateQuery(
                $stockQuery,
                Auth::user(),
                $request->escalation_reason
            );

            if ($result && $request->filled('escalate_to')) {
                $escalateTo = User::find($request->escalate_to);
                $this->queryService->assignQuery($stockQuery, $escalateTo);
            }

            return back()->with('success', 'Query escalated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to escalate query: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions on queries
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:assign,priority,status',
            'query_ids' => 'required|array|min:1',
            'query_ids.*' => 'exists:stock_transfer_queries,id',
            'assigned_to' => 'required_if:action,assign|nullable|exists:users,id',
            'priority' => 'required_if:action,priority|nullable|in:low,medium,high,critical',
            'status' => 'required_if:action,status|nullable|in:open,in_progress,resolved,closed',
        ]);

        try {
            $queries = StockTransferQuery::whereIn('id', $request->query_ids)->get();
            $successCount = 0;

            foreach ($queries as $query) {
                switch ($request->action) {
                    case 'assign':
                        $admin = User::find($request->assigned_to);
                        if ($admin && $admin->hasRole(['admin', 'super_admin'])) {
                            if ($this->queryService->assignQuery($query, $admin)) {
                                $successCount++;
                            }
                        }
                        break;

                    case 'priority':
                        if ($query->update(['priority' => $request->priority])) {
                            $successCount++;
                            $query->addResponse(
                                "Priority changed to " . ucfirst($request->priority),
                                'status_update',
                                null,
                                true
                            );
                        }
                        break;

                    case 'status':
                        if ($query->update(['status' => $request->status])) {
                            $successCount++;
                            $query->addResponse(
                                "Status changed to " . ucfirst($request->status),
                                'status_update',
                                null,
                                true
                            );
                        }
                        break;
                }
            }

            return back()->with('success', "Bulk action completed successfully on {$successCount} queries.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to perform bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Query analytics dashboard
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 'month');
        $branchId = $request->get('branch_id');

        // Get comprehensive analytics
        $analytics = [
            'overview' => $this->queryService->getQueryStatistics($branchId),
            'trends' => $this->queryService->generateTrendsReport($branchId, $this->getPeriodDays($period)),
            'financial_impact' => $this->financialService->getBranchFinancialSummary($branchId),
            'performance_metrics' => $this->getPerformanceMetrics($branchId),
            'root_cause_analysis' => $this->getRootCauseAnalysis($branchId),
        ];

        $branches = Branch::active()->get();

        return view('admin.stock-queries.analytics', compact('analytics', 'branches', 'period'));
    }

    /**
     * Generate query report
     */
    public function report(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'report_type' => 'required|in:summary,detailed,financial,trends',
            'format' => 'required|in:html,pdf,excel',
        ]);

        $branchId = $request->branch_id;
        $startDate = $request->date_from;
        $endDate = $request->date_to;

        switch ($request->report_type) {
            case 'summary':
                $data = $this->queryService->getQueryStatistics($branchId, $startDate, $endDate);
                break;
            case 'detailed':
                $data = $this->getDetailedQueryReport($branchId, $startDate, $endDate);
                break;
            case 'financial':
                $data = $this->financialService->generateTransferImpactReport($branchId, $startDate, $endDate);
                break;
            case 'trends':
                $data = $this->queryService->generateTrendsReport($branchId, 90);
                break;
        }

        if ($request->format === 'html') {
            return view('admin.stock-queries.report', compact('data', 'request'));
        } elseif ($request->format === 'pdf') {
            return $this->generatePdfReport($data, $request);
        } else {
            return $this->generateExcelReport($data, $request);
        }
    }

    /**
     * Auto-resolve queries based on rules
     */
    public function autoResolve(Request $request)
    {
        $request->validate([
            'rules' => 'required|array',
            'rules.*.type' => 'required|in:low_impact,old_queries,duplicate',
            'rules.*.enabled' => 'boolean',
            'rules.*.threshold' => 'nullable|numeric',
        ]);

        try {
            $resolvedCount = 0;

            foreach ($request->rules as $rule) {
                if (!$rule['enabled']) continue;

                switch ($rule['type']) {
                    case 'low_impact':
                        $threshold = $rule['threshold'] ?? 100;
                        $queries = StockTransferQuery::where('status', 'open')
                                                   ->where('financial_impact', '<', $threshold)
                                                   ->where('created_at', '<', now()->subDays(7))
                                                   ->get();

                        foreach ($queries as $query) {
                            $this->queryService->resolveQuery(
                                $query,
                                "Auto-resolved: Low financial impact (< {$threshold})",
                                Auth::user()
                            );
                            $resolvedCount++;
                        }
                        break;

                    case 'old_queries':
                        $days = $rule['threshold'] ?? 30;
                        $queries = StockTransferQuery::where('status', 'open')
                                                   ->where('priority', 'low')
                                                   ->where('created_at', '<', now()->subDays($days))
                                                   ->get();

                        foreach ($queries as $query) {
                            $this->queryService->resolveQuery(
                                $query,
                                "Auto-resolved: Old query (> {$days} days) with low priority",
                                Auth::user()
                            );
                            $resolvedCount++;
                        }
                        break;
                }
            }

            return back()->with('success', "Auto-resolved {$resolvedCount} queries based on configured rules.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to auto-resolve queries: ' . $e->getMessage());
        }
    }

    /**
     * Get similar queries for reference
     */
    protected function getSimilarQueries(StockTransferQuery $query): \Illuminate\Database\Eloquent\Collection
    {
        return StockTransferQuery::where('id', '!=', $query->id)
                                ->where('query_type', $query->query_type)
                                ->where('status', 'resolved')
                                ->whereHas('stockTransfer', function ($q) use ($query) {
                                    $q->where('to_branch_id', $query->stockTransfer->to_branch_id);
                                })
                                ->with(['stockTransfer', 'assignedTo'])
                                ->orderBy('resolved_at', 'desc')
                                ->limit(5)
                                ->get();
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

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(?int $branchId): array
    {
        $query = StockTransferQuery::query();
        
        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        $queries = $query->get();
        $resolvedQueries = $queries->where('status', 'resolved');

        return [
            'resolution_rate' => $queries->count() > 0 ? 
                ($resolvedQueries->count() / $queries->count()) * 100 : 0,
            'average_resolution_time' => $resolvedQueries->avg(function ($query) {
                return $query->resolved_at ? 
                    $query->created_at->diffInHours($query->resolved_at) : null;
            }),
            'escalation_rate' => $queries->count() > 0 ? 
                ($queries->where('status', 'escalated')->count() / $queries->count()) * 100 : 0,
            'first_response_time' => $queries->avg(function ($query) {
                $firstResponse = $query->responses()->orderBy('created_at')->first();
                return $firstResponse ? 
                    $query->created_at->diffInHours($firstResponse->created_at) : null;
            }),
            'customer_satisfaction' => $this->calculateCustomerSatisfaction($queries),
        ];
    }

    /**
     * Get root cause analysis
     */
    protected function getRootCauseAnalysis(?int $branchId): array
    {
        $query = StockTransferQuery::query();
        
        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        $queries = $query->get();

        return [
            'top_causes' => $queries->groupBy('query_type')
                                  ->map(fn($group) => $group->count())
                                  ->sortDesc()
                                  ->take(5)
                                  ->toArray(),
            'recurring_issues' => $this->identifyRecurringIssues($queries),
            'seasonal_patterns' => $this->identifySeasonalPatterns($queries),
            'branch_comparison' => $this->getBranchComparison($queries),
        ];
    }

    /**
     * Calculate customer satisfaction score
     */
    protected function calculateCustomerSatisfaction($queries): float
    {
        // Simple satisfaction score based on resolution time and escalation
        $totalScore = 0;
        $count = 0;

        foreach ($queries->where('status', 'resolved') as $query) {
            $score = 100; // Start with perfect score
            
            // Deduct points for long resolution time
            if ($query->resolved_at) {
                $resolutionHours = $query->created_at->diffInHours($query->resolved_at);
                if ($resolutionHours > 24) $score -= 20;
                if ($resolutionHours > 72) $score -= 20;
            }
            
            // Deduct points for escalation
            if ($query->status === 'escalated') $score -= 30;
            
            // Deduct points for multiple responses (indicates complexity/confusion)
            if ($query->responses->count() > 5) $score -= 10;
            
            $totalScore += max(0, $score);
            $count++;
        }

        return $count > 0 ? $totalScore / $count : 0;
    }

    /**
     * Identify recurring issues
     */
    protected function identifyRecurringIssues($queries): array
    {
        // Group by product and query type to find recurring issues
        $recurring = [];
        
        $productQueries = $queries->whereNotNull('stock_transfer_item_id')
                                ->groupBy(function ($query) {
                                    return $query->stockTransferItem->product_id . '_' . $query->query_type;
                                })
                                ->filter(fn($group) => $group->count() >= 3);

        foreach ($productQueries as $key => $group) {
            $parts = explode('_', $key);
            $productId = $parts[0];
            $queryType = $parts[1];
            
            $recurring[] = [
                'product_id' => $productId,
                'product_name' => $group->first()->stockTransferItem->product->name ?? 'Unknown',
                'query_type' => $queryType,
                'occurrences' => $group->count(),
                'total_impact' => $group->sum('financial_impact'),
                'latest_occurrence' => $group->max('created_at'),
            ];
        }

        return collect($recurring)->sortByDesc('occurrences')->take(10)->toArray();
    }

    /**
     * Identify seasonal patterns
     */
    protected function identifySeasonalPatterns($queries): array
    {
        $monthlyData = $queries->groupBy(function ($query) {
            return $query->created_at->format('m');
        })->map(function ($group, $month) {
            return [
                'month' => $month,
                'month_name' => now()->month($month)->format('F'),
                'count' => $group->count(),
                'avg_impact' => $group->avg('financial_impact'),
            ];
        })->sortBy('month');

        return $monthlyData->toArray();
    }

    /**
     * Get branch comparison data
     */
    protected function getBranchComparison($queries): array
    {
        return $queries->groupBy(function ($query) {
            return $query->stockTransfer->to_branch_id;
        })->map(function ($group) {
            $branch = $group->first()->stockTransfer->toBranch;
            return [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'query_count' => $group->count(),
                'avg_resolution_time' => $group->where('status', 'resolved')->avg(function ($query) {
                    return $query->resolved_at ? 
                        $query->created_at->diffInHours($query->resolved_at) : null;
                }),
                'total_financial_impact' => $group->sum('financial_impact'),
                'resolution_rate' => $group->count() > 0 ? 
                    ($group->where('status', 'resolved')->count() / $group->count()) * 100 : 0,
            ];
        })->sortByDesc('query_count')->take(10)->toArray();
    }

    /**
     * Get detailed query report
     */
    protected function getDetailedQueryReport(?int $branchId, ?string $startDate, ?string $endDate): array
    {
        $query = StockTransferQuery::with([
            'stockTransfer.toBranch', 'stockTransferItem.product',
            'raisedBy', 'assignedTo', 'responses', 'financialImpacts'
        ]);

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $queries = $query->get();

        return [
            'queries' => $queries->toArray(),
            'summary' => $this->queryService->getQueryStatistics($branchId, $startDate, $endDate),
            'performance' => $this->getPerformanceMetrics($branchId),
            'financial_analysis' => [
                'total_impact' => $queries->sum('financial_impact'),
                'recoverable_amount' => $queries->flatMap->financialImpacts
                                              ->where('is_recoverable', true)
                                              ->sum('amount'),
                'recovered_amount' => $queries->flatMap->financialImpacts->sum('recovered_amount'),
                'by_type' => $queries->groupBy('query_type')
                                   ->map(fn($group) => $group->sum('financial_impact'))
                                   ->toArray(),
            ],
        ];
    }

    /**
     * Generate PDF report (placeholder)
     */
    protected function generatePdfReport(array $data, Request $request)
    {
        // This would use a PDF library like DomPDF or wkhtmltopdf
        // For now, return error message
        return back()->with('error', 'PDF export functionality not yet implemented.');
    }

    /**
     * Generate Excel report (placeholder)
     */
    protected function generateExcelReport(array $data, Request $request)
    {
        // This would use a library like PhpSpreadsheet
        // For now, return error message
        return back()->with('error', 'Excel export functionality not yet implemented.');
    }
}