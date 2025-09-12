<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferQuery;
use App\Models\StockFinancialImpact;
use App\Models\StockAlert;
use App\Models\User;
use App\Services\StockTransferService;
use App\Services\StockQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchDashboardController extends Controller
{
    protected $transferService;
    protected $queryService;

    public function __construct(StockTransferService $transferService, StockQueryService $queryService)
    {
        $this->transferService = $transferService;
        $this->queryService = $queryService;
        $this->middleware('auth');
        $this->middleware('role:branch_manager,admin,super_admin');
    }

    /**
     * Main branch dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        if (!$branchId && !$user->hasRole(['admin', 'super_admin'])) {
            abort(403, 'Access denied. No branch assigned.');
        }

        $period = $request->get('period', 'month');

        $dashboardData = [
            'summary' => $this->getDashboardSummary($branchId, $period),
            'pending_actions' => $this->getPendingActions($branchId),
            'recent_transfers' => $this->getRecentTransfers($branchId),
            'active_queries' => $this->getActiveQueries($branchId),
            'performance_metrics' => $this->getBranchPerformanceMetrics($branchId, $period),
            'alerts' => $this->getBranchAlerts($branchId),
        ];

        return view('branch.dashboard.index', compact('dashboardData', 'period'));
    }

    /**
     * Stock receipts management
     */
    public function stockReceipts(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        $status = $request->get('status', 'all');
        
        $query = StockTransfer::where('to_branch_id', $branchId)
                              ->with(['fromBranch', 'initiatedBy', 'items.product'])
                              ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $transfers = $query->paginate(15);

        $stats = [
            'pending_receipts' => StockTransfer::where('to_branch_id', $branchId)
                                             ->where('status', 'delivered')
                                             ->count(),
            'awaiting_confirmation' => StockTransfer::where('to_branch_id', $branchId)
                                                   ->where('status', 'delivered')
                                                   ->count(),
            'completed_today' => StockTransfer::where('to_branch_id', $branchId)
                                             ->where('status', 'confirmed')
                                             ->whereDate('confirmed_date', today())
                                             ->count(),
            'overdue_transfers' => $this->transferService->getOverdueTransfers($branchId)->count(),
        ];

        return view('branch.dashboard.stock-receipts', compact('transfers', 'stats', 'status'));
    }

    /**
     * Quick receipt confirmation
     */
    public function quickReceiptConfirmation(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        if ($stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if ($stockTransfer->status !== 'delivered') {
            return back()->with('error', 'Transfer is not in delivered status.');
        }

        $stockTransfer->load(['items.product', 'queries']);

        return view('branch.dashboard.quick-receipt', compact('stockTransfer'));
    }

    /**
     * Process quick receipt confirmation
     */
    public function processQuickReceipt(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        if ($stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'confirmation_type' => 'required|in:accept_all,partial,reject_all',
            'items' => 'required_if:confirmation_type,partial|array',
            'items.*.item_id' => 'required_if:confirmation_type,partial|exists:stock_transfer_items,id',
            'items.*.quantity_received' => 'required_if:confirmation_type,partial|numeric|min:0',
            'items.*.condition' => 'required_if:confirmation_type,partial|in:good,damaged,expired,partial',
            'items.*.notes' => 'nullable|string|max:500',
            'overall_notes' => 'nullable|string|max:1000',
        ]);

        try {
            switch ($request->confirmation_type) {
                case 'accept_all':
                    $items = $stockTransfer->items->map(function ($item) {
                        return [
                            'item_id' => $item->id,
                            'quantity_received' => $item->quantity_sent,
                            'notes' => 'Accepted in full - good condition',
                        ];
                    })->toArray();
                    break;

                case 'partial':
                    $items = $request->items;
                    // Check for any discrepancies and create queries if needed
                    $this->checkAndCreateQueries($stockTransfer, $items, $user);
                    break;

                case 'reject_all':
                    $items = $stockTransfer->items->map(function ($item) {
                        return [
                            'item_id' => $item->id,
                            'quantity_received' => 0,
                            'notes' => 'Rejected - ' . ($request->overall_notes ?? 'No reason provided'),
                        ];
                    })->toArray();
                    break;
            }

            $result = $this->transferService->confirmReceipt($stockTransfer, $items, $user);

            if ($result) {
                // Add overall notes if provided
                if ($request->filled('overall_notes')) {
                    $stockTransfer->update([
                        'delivery_notes' => $stockTransfer->delivery_notes ? 
                            $stockTransfer->delivery_notes . "\n\nBranch Notes: " . $request->overall_notes :
                            "Branch Notes: " . $request->overall_notes
                    ]);
                }

                return redirect()->route('branch.dashboard.stock-receipts')
                               ->with('success', 'Stock receipt confirmed successfully.');
            } else {
                return back()->with('error', 'Failed to confirm stock receipt.');
            }

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to process receipt: ' . $e->getMessage());
        }
    }

    /**
     * Quality inspection interface
     */
    public function qualityInspection(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        if ($stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $stockTransfer->load(['items.product.batches', 'queries']);

        return view('branch.dashboard.quality-inspection', compact('stockTransfer'));
    }

    /**
     * Process quality inspection
     */
    public function processQualityInspection(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        if ($stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:stock_transfer_items,id',
            'items.*.quality_rating' => 'required|in:excellent,good,acceptable,poor,rejected',
            'items.*.actual_weight' => 'required|numeric|min:0',
            'items.*.condition_notes' => 'nullable|string|max:500',
            'items.*.defects' => 'nullable|array',
            'items.*.photos.*' => 'image|max:10240', // 10MB max per image
            'inspector_notes' => 'nullable|string|max:1000',
        ]);

        try {
            foreach ($request->items as $itemData) {
                $item = $stockTransfer->items()->find($itemData['item_id']);
                if (!$item) continue;

                // Update item with inspection results
                $item->update([
                    'quantity_received' => $itemData['actual_weight'],
                    'item_notes' => ($item->item_notes ? $item->item_notes . "\n" : '') . 
                                   "Quality Inspection: " . ucfirst($itemData['quality_rating']) . 
                                   ($itemData['condition_notes'] ? " - " . $itemData['condition_notes'] : ''),
                ]);

                // Create queries for poor quality or significant weight differences
                if ($itemData['quality_rating'] === 'poor' || $itemData['quality_rating'] === 'rejected') {
                    $this->createQualityQuery($stockTransfer, $item, $itemData, $user);
                }

                $weightDifference = abs($itemData['actual_weight'] - $item->quantity_sent);
                if ($weightDifference > ($item->quantity_sent * 0.05)) { // More than 5% difference
                    $this->createWeightDifferenceQuery($stockTransfer, $item, $itemData, $user);
                }

                // Upload photos if provided
                if (isset($itemData['photos'])) {
                    $this->uploadInspectionPhotos($item, $itemData['photos']);
                }
            }

            // Update transfer status
            $stockTransfer->update([
                'status' => 'confirmed',
                'confirmed_date' => now(),
                'delivery_notes' => $stockTransfer->delivery_notes ? 
                    $stockTransfer->delivery_notes . "\n\nInspection Notes: " . $request->inspector_notes :
                    "Inspection Notes: " . $request->inspector_notes,
            ]);

            return redirect()->route('branch.dashboard.stock-receipts')
                           ->with('success', 'Quality inspection completed and stock receipt confirmed.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to process quality inspection: ' . $e->getMessage());
        }
    }

    /**
     * Branch queries management
     */
    public function queries(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        $status = $request->get('status', 'all');
        $priority = $request->get('priority', 'all');

        $query = StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                      $q->where('to_branch_id', $branchId);
                                  })
                                  ->with(['stockTransfer', 'stockTransferItem.product', 'assignedTo', 'responses'])
                                  ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        $queries = $query->paginate(15);

        $stats = [
            'open_queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                $q->where('to_branch_id', $branchId);
                              })->where('status', 'open')->count(),
            'in_progress' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                               $q->where('to_branch_id', $branchId);
                             })->where('status', 'in_progress')->count(),
            'resolved_today' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                  $q->where('to_branch_id', $branchId);
                                })->where('status', 'resolved')
                                  ->whereDate('resolved_at', today())
                                  ->count(),
            'critical_queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                    $q->where('to_branch_id', $branchId);
                                  })->where('priority', 'critical')
                                    ->whereIn('status', ['open', 'in_progress'])
                                    ->count(),
        ];

        return view('branch.dashboard.queries', compact('queries', 'stats', 'status', 'priority'));
    }

    /**
     * Quick query creation
     */
    public function createQuickQuery(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        $request->validate([
            'stock_transfer_id' => 'required|exists:stock_transfers,id',
            'query_type' => 'required|in:weight_difference,quantity_shortage,quality_issue,damaged_goods,expired_goods,missing_items,other',
            'priority' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'expected_quantity' => 'nullable|numeric|min:0',
            'actual_quantity' => 'nullable|numeric|min:0',
        ]);

        $stockTransfer = StockTransfer::find($request->stock_transfer_id);
        
        if ($stockTransfer->to_branch_id !== $branchId) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $queryData = $request->only([
                'stock_transfer_id', 'query_type', 'priority', 'title', 
                'description', 'expected_quantity', 'actual_quantity'
            ]);

            $query = $this->queryService->createQuery($queryData, $user);

            return response()->json([
                'success' => true,
                'query' => [
                    'id' => $query->id,
                    'query_number' => $query->query_number,
                    'title' => $query->title,
                    'status' => $query->status,
                    'priority' => $query->priority,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create query: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Branch performance overview
     */
    public function performance(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $period = $request->get('period', 'month');

        $performanceData = [
            'kpis' => $this->getBranchKPIs($branchId, $period),
            'trends' => $this->getBranchTrends($branchId, $period),
            'comparisons' => $this->getBranchComparisons($branchId, $period),
            'improvement_areas' => $this->getImprovementAreas($branchId, $period),
        ];

        return view('branch.dashboard.performance', compact('performanceData', 'period'));
    }

    /**
     * Branch financial summary
     */
    public function financial(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $period = $request->get('period', 'month');

        $financialData = [
            'summary' => StockFinancialImpact::getImpactSummary($branchId, $period),
            'cost_breakdown' => $this->getCostBreakdown($branchId, $period),
            'recovery_opportunities' => $this->getRecoveryOpportunities($branchId),
            'trends' => $this->getFinancialTrends($branchId, $period),
        ];

        return view('branch.dashboard.financial', compact('financialData', 'period'));
    }

    /**
     * Get dashboard summary
     */
    protected function getDashboardSummary(int $branchId, string $period): array
    {
        $dateRange = $this->getDateRange($period);

        return [
            'transfers' => [
                'pending_receipts' => StockTransfer::where('to_branch_id', $branchId)
                                                 ->where('status', 'delivered')
                                                 ->count(),
                'confirmed_today' => StockTransfer::where('to_branch_id', $branchId)
                                                 ->where('status', 'confirmed')
                                                 ->whereDate('confirmed_date', today())
                                                 ->count(),
                'total_this_period' => StockTransfer::where('to_branch_id', $branchId)
                                                   ->whereBetween('created_at', $dateRange)
                                                   ->count(),
            ],
            'queries' => [
                'open' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                            $q->where('to_branch_id', $branchId);
                          })->where('status', 'open')->count(),
                'critical' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                $q->where('to_branch_id', $branchId);
                              })->where('priority', 'critical')
                                ->whereIn('status', ['open', 'in_progress'])
                                ->count(),
                'resolved_today' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                      $q->where('to_branch_id', $branchId);
                                    })->where('status', 'resolved')
                                      ->whereDate('resolved_at', today())
                                      ->count(),
            ],
            'financial' => [
                'total_impact' => StockFinancialImpact::where('branch_id', $branchId)
                                                    ->whereBetween('impact_date', $dateRange)
                                                    ->sum('amount'),
                'recoverable_amount' => StockFinancialImpact::where('branch_id', $branchId)
                                                          ->where('is_recoverable', true)
                                                          ->sum(function ($impact) {
                                                              return $impact->amount - $impact->recovered_amount;
                                                          }),
            ],
        ];
    }

    /**
     * Get pending actions for branch
     */
    protected function getPendingActions(int $branchId): array
    {
        return [
            'pending_receipts' => StockTransfer::where('to_branch_id', $branchId)
                                             ->where('status', 'delivered')
                                             ->with(['fromBranch', 'items'])
                                             ->orderBy('delivered_date')
                                             ->limit(5)
                                             ->get(),
            'overdue_transfers' => $this->transferService->getOverdueTransfers($branchId)->take(3),
            'critical_queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                    $q->where('to_branch_id', $branchId);
                                  })->where('priority', 'critical')
                                    ->whereIn('status', ['open', 'in_progress'])
                                    ->with(['stockTransfer'])
                                    ->limit(3)
                                    ->get(),
            'unread_alerts' => StockAlert::where('branch_id', $branchId)
                                       ->where('is_read', false)
                                       ->orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get(),
        ];
    }

    /**
     * Get recent transfers for branch
     */
    protected function getRecentTransfers(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return StockTransfer::where('to_branch_id', $branchId)
                           ->with(['fromBranch', 'items.product', 'queries'])
                           ->orderBy('created_at', 'desc')
                           ->limit(10)
                           ->get();
    }

    /**
     * Get active queries for branch
     */
    protected function getActiveQueries(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                     $q->where('to_branch_id', $branchId);
                                 })
                                 ->whereIn('status', ['open', 'in_progress'])
                                 ->with(['stockTransfer', 'assignedTo'])
                                 ->orderBy('priority', 'desc')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(8)
                                 ->get();
    }

    /**
     * Get branch performance metrics
     */
    protected function getBranchPerformanceMetrics(int $branchId, string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        $transferStats = $this->transferService->getTransferStatistics($branchId, ...$dateRange);
        $queryStats = $this->queryService->getQueryStatistics($branchId, ...$dateRange);

        return [
            'receipt_efficiency' => $transferStats['total_transfers'] > 0 ? 
                ($transferStats['confirmed_transfers'] / $transferStats['total_transfers']) * 100 : 0,
            'query_resolution_rate' => $queryStats['total_queries'] > 0 ? 
                ($queryStats['resolved_queries'] / $queryStats['total_queries']) * 100 : 0,
            'average_confirmation_time' => $this->calculateAverageConfirmationTime($branchId, ...$dateRange),
            'quality_score' => $this->calculateQualityScore($branchId, ...$dateRange),
        ];
    }

    /**
     * Get branch alerts
     */
    protected function getBranchAlerts(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return StockAlert::where('branch_id', $branchId)
                        ->where('is_resolved', false)
                        ->with(['stockTransfer'])
                        ->orderBy('severity', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
    }

    /**
     * Check and create queries for discrepancies
     */
    protected function checkAndCreateQueries(StockTransfer $transfer, array $items, User $user): void
    {
        foreach ($items as $itemData) {
            $item = $transfer->items()->find($itemData['item_id']);
            if (!$item) continue;

            $quantityReceived = $itemData['quantity_received'];
            $difference = abs($quantityReceived - $item->quantity_sent);
            
            // Create query for significant differences (>5%)
            if ($difference > ($item->quantity_sent * 0.05)) {
                $queryType = $quantityReceived < $item->quantity_sent ? 'quantity_shortage' : 'weight_difference';
                
                $this->queryService->createQuery([
                    'stock_transfer_id' => $transfer->id,
                    'stock_transfer_item_id' => $item->id,
                    'query_type' => $queryType,
                    'priority' => $difference > ($item->quantity_sent * 0.15) ? 'high' : 'medium',
                    'title' => "Quantity discrepancy for {$item->product->name}",
                    'description' => "Expected: {$item->quantity_sent}, Received: {$quantityReceived}, Difference: {$difference}",
                    'expected_quantity' => $item->quantity_sent,
                    'actual_quantity' => $quantityReceived,
                ], $user);
            }

            // Create query for damaged/expired items
            if (isset($itemData['condition']) && in_array($itemData['condition'], ['damaged', 'expired'])) {
                $this->queryService->createQuery([
                    'stock_transfer_id' => $transfer->id,
                    'stock_transfer_item_id' => $item->id,
                    'query_type' => $itemData['condition'] === 'damaged' ? 'damaged_goods' : 'expired_goods',
                    'priority' => 'high',
                    'title' => ucfirst($itemData['condition']) . " {$item->product->name}",
                    'description' => $itemData['notes'] ?? "Item received in {$itemData['condition']} condition",
                    'expected_quantity' => $item->quantity_sent,
                    'actual_quantity' => $itemData['condition'] === 'damaged' ? $quantityReceived : 0,
                ], $user);
            }
        }
    }

    /**
     * Create quality query
     */
    protected function createQualityQuery(StockTransfer $transfer, $item, array $inspectionData, User $user): void
    {
        $this->queryService->createQuery([
            'stock_transfer_id' => $transfer->id,
            'stock_transfer_item_id' => $item->id,
            'query_type' => 'quality_issue',
            'priority' => $inspectionData['quality_rating'] === 'rejected' ? 'critical' : 'high',
            'title' => "Quality issue with {$item->product->name}",
            'description' => "Quality rating: {$inspectionData['quality_rating']}. " . 
                           ($inspectionData['condition_notes'] ?? 'No additional notes'),
            'expected_quantity' => $item->quantity_sent,
            'actual_quantity' => $inspectionData['actual_weight'],
        ], $user);
    }

    /**
     * Create weight difference query
     */
    protected function createWeightDifferenceQuery(StockTransfer $transfer, $item, array $inspectionData, User $user): void
    {
        $this->queryService->createQuery([
            'stock_transfer_id' => $transfer->id,
            'stock_transfer_item_id' => $item->id,
            'query_type' => 'weight_difference',
            'priority' => 'medium',
            'title' => "Weight difference for {$item->product->name}",
            'description' => "Expected weight: {$item->quantity_sent}, Actual weight: {$inspectionData['actual_weight']}",
            'expected_quantity' => $item->quantity_sent,
            'actual_quantity' => $inspectionData['actual_weight'],
        ], $user);
    }

    /**
     * Upload inspection photos
     */
    protected function uploadInspectionPhotos($item, array $photos): void
    {
        $uploadedPhotos = [];
        
        foreach ($photos as $photo) {
            $path = $photo->store("inspections/{$item->id}", 'public');
            $uploadedPhotos[] = [
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'uploaded_at' => now()->toISOString(),
            ];
        }

        // Add to existing photos
        $existingPhotos = $item->photos ?? [];
        $allPhotos = array_merge($existingPhotos, $uploadedPhotos);
        
        $item->update(['photos' => $allPhotos]);
    }

    // Additional helper methods for calculations and data retrieval
    protected function getDateRange(string $period): array
    {
        switch ($period) {
            case 'week':
                return [now()->subWeek()->toDateString(), now()->toDateString()];
            case 'month':
                return [now()->subMonth()->toDateString(), now()->toDateString()];
            case 'quarter':
                return [now()->subQuarter()->toDateString(), now()->toDateString()];
            default:
                return [now()->subMonth()->toDateString(), now()->toDateString()];
        }
    }

    protected function calculateAverageConfirmationTime(int $branchId, string $startDate, string $endDate): float
    {
        // Implementation for average confirmation time calculation
        return 0.0;
    }

    protected function calculateQualityScore(int $branchId, string $startDate, string $endDate): float
    {
        // Implementation for quality score calculation
        return 0.0;
    }

    protected function getBranchKPIs(int $branchId, string $period): array
    {
        // Implementation for branch KPIs
        return [];
    }

    protected function getBranchTrends(int $branchId, string $period): array
    {
        // Implementation for branch trends
        return [];
    }

    protected function getBranchComparisons(int $branchId, string $period): array
    {
        // Implementation for branch comparisons
        return [];
    }

    protected function getImprovementAreas(int $branchId, string $period): array
    {
        // Implementation for improvement areas identification
        return [];
    }

    protected function getCostBreakdown(int $branchId, string $period): array
    {
        // Implementation for cost breakdown
        return [];
    }

    protected function getRecoveryOpportunities(int $branchId): array
    {
        // Implementation for recovery opportunities
        return [];
    }

    protected function getFinancialTrends(int $branchId, string $period): array
    {
        // Implementation for financial trends
        return [];
    }
}