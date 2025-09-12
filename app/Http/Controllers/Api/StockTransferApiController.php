<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferQuery;
use App\Models\Branch;
use App\Services\StockTransferService;
use App\Services\StockQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockTransferApiController extends Controller
{
    protected $transferService;
    protected $queryService;

    public function __construct(StockTransferService $transferService, StockQueryService $queryService)
    {
        $this->transferService = $transferService;
        $this->queryService = $queryService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get transfers for branch (mobile app)
     */
    public function getBranchTransfers(Request $request)
    {
        try {
            $user = Auth::user();
            $branchId = $user->branch_id;

            if (!$branchId && !$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. No branch assigned.'
                ], 403);
            }

            $status = $request->get('status', 'all');
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $query = StockTransfer::where('to_branch_id', $branchId)
                                  ->with(['fromBranch', 'items.product', 'queries'])
                                  ->orderBy('created_at', 'desc');

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $transfers = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'transfers' => $transfers->items(),
                    'pagination' => [
                        'current_page' => $transfers->currentPage(),
                        'last_page' => $transfers->lastPage(),
                        'per_page' => $transfers->perPage(),
                        'total' => $transfers->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transfers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific transfer details
     */
    public function getTransferDetails($transferId)
    {
        try {
            $user = Auth::user();
            
            $transfer = StockTransfer::with([
                'fromBranch', 'toBranch', 'initiatedBy',
                'items.product', 'items.batch',
                'queries.raisedBy', 'queries.assignedTo',
                'transportExpenses', 'reconciliations'
            ])->find($transferId);

            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer not found'
                ], 404);
            }

            // Check access permissions
            if (!$user->hasRole(['admin', 'super_admin']) && $transfer->to_branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $transfer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transfer details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm receipt of transfer (mobile app)
     */
    public function confirmReceipt(Request $request, $transferId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array',
                'items.*.item_id' => 'required|exists:stock_transfer_items,id',
                'items.*.quantity_received' => 'required|numeric|min:0',
                'items.*.condition' => 'required|in:good,damaged,expired,partial',
                'items.*.notes' => 'nullable|string|max:500',
                'overall_notes' => 'nullable|string|max:1000',
                'photos.*' => 'image|max:10240', // 10MB max per image
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $transfer = StockTransfer::find($transferId);

            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer not found'
                ], 404);
            }

            // Check access and status
            if ($transfer->to_branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            if ($transfer->status !== 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only confirm receipt of delivered transfers'
                ], 400);
            }

            // Process receipt confirmation
            $result = $this->transferService->confirmReceipt($transfer, $request->items, $user);

            if ($result) {
                // Handle photo uploads
                if ($request->hasFile('photos')) {
                    $this->uploadReceiptPhotos($transfer, $request->file('photos'));
                }

                // Add overall notes
                if ($request->filled('overall_notes')) {
                    $transfer->update([
                        'delivery_notes' => $transfer->delivery_notes ? 
                            $transfer->delivery_notes . "\n\nMobile App Notes: " . $request->overall_notes :
                            "Mobile App Notes: " . $request->overall_notes
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Stock receipt confirmed successfully',
                    'data' => $transfer->fresh()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to confirm stock receipt'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create query via mobile app
     */
    public function createQuery(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'stock_transfer_id' => 'required|exists:stock_transfers,id',
                'stock_transfer_item_id' => 'nullable|exists:stock_transfer_items,id',
                'query_type' => 'required|in:weight_difference,quantity_shortage,quality_issue,damaged_goods,expired_goods,missing_items,other',
                'priority' => 'required|in:low,medium,high,critical',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'expected_quantity' => 'nullable|numeric|min:0',
                'actual_quantity' => 'nullable|numeric|min:0',
                'evidence_photos.*' => 'image|max:10240', // 10MB max per image
                'documents.*' => 'file|max:10240', // 10MB max per file
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $transfer = StockTransfer::find($request->stock_transfer_id);

            // Check access
            if ($transfer->to_branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $queryData = $request->only([
                'stock_transfer_id', 'stock_transfer_item_id', 'query_type',
                'priority', 'title', 'description', 'expected_quantity', 'actual_quantity'
            ]);

            $query = $this->queryService->createQuery($queryData, $user);

            // Upload evidence files
            if ($request->hasFile('evidence_photos')) {
                $this->queryService->uploadEvidence($query, $request->file('evidence_photos'), 'photos');
            }

            if ($request->hasFile('documents')) {
                $this->queryService->uploadEvidence($query, $request->file('documents'), 'documents');
            }

            return response()->json([
                'success' => true,
                'message' => 'Query created successfully',
                'data' => [
                    'query_id' => $query->id,
                    'query_number' => $query->query_number,
                    'status' => $query->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create query: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get branch queries
     */
    public function getBranchQueries(Request $request)
    {
        try {
            $user = Auth::user();
            $branchId = $user->branch_id;

            if (!$branchId && !$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. No branch assigned.'
                ], 403);
            }

            $status = $request->get('status', 'all');
            $priority = $request->get('priority', 'all');
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

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

            $queries = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'queries' => $queries->items(),
                    'pagination' => [
                        'current_page' => $queries->currentPage(),
                        'last_page' => $queries->lastPage(),
                        'per_page' => $queries->perPage(),
                        'total' => $queries->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch queries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get query details
     */
    public function getQueryDetails($queryId)
    {
        try {
            $user = Auth::user();
            
            $query = StockTransferQuery::with([
                'stockTransfer.toBranch', 'stockTransferItem.product',
                'raisedBy', 'assignedTo', 'responses.user', 'financialImpacts'
            ])->find($queryId);

            if (!$query) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query not found'
                ], 404);
            }

            // Check access
            if (!$user->hasRole(['admin', 'super_admin']) && $query->stockTransfer->to_branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $query
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch query details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add response to query
     */
    public function addQueryResponse(Request $request, $queryId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string',
                'attachments.*' => 'file|max:10240', // 10MB max per file
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $query = StockTransferQuery::find($queryId);

            if (!$query) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query not found'
                ], 404);
            }

            // Check access
            if (!$user->hasRole(['admin', 'super_admin']) && $query->stockTransfer->to_branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $attachments = null;
            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store("query-responses/{$query->id}", 'public');
                    $attachments[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'uploaded_at' => now()->toISOString(),
                    ];
                }
            }

            $response = $this->queryService->addResponse(
                $query,
                $user,
                $request->message,
                'comment',
                $attachments,
                false
            );

            return response()->json([
                'success' => true,
                'message' => 'Response added successfully',
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add response: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard data for mobile app
     */
    public function getDashboardData(Request $request)
    {
        try {
            $user = Auth::user();
            $branchId = $user->branch_id;

            if (!$branchId && !$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. No branch assigned.'
                ], 403);
            }

            $period = $request->get('period', 'week');

            $data = [
                'summary' => [
                    'pending_receipts' => StockTransfer::where('to_branch_id', $branchId)
                                                     ->where('status', 'delivered')
                                                     ->count(),
                    'open_queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                        $q->where('to_branch_id', $branchId);
                                      })->where('status', 'open')->count(),
                    'confirmed_today' => StockTransfer::where('to_branch_id', $branchId)
                                                     ->where('status', 'confirmed')
                                                     ->whereDate('confirmed_date', today())
                                                     ->count(),
                    'critical_queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                            $q->where('to_branch_id', $branchId);
                                          })->where('priority', 'critical')
                                            ->whereIn('status', ['open', 'in_progress'])
                                            ->count(),
                ],
                'recent_transfers' => StockTransfer::where('to_branch_id', $branchId)
                                                  ->with(['fromBranch', 'items.product'])
                                                  ->orderBy('created_at', 'desc')
                                                  ->limit(5)
                                                  ->get(),
                'recent_queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                      $q->where('to_branch_id', $branchId);
                                    })->with(['stockTransfer'])
                                      ->orderBy('created_at', 'desc')
                                      ->limit(5)
                                      ->get(),
                'performance_metrics' => $this->getBranchPerformanceMetrics($branchId, $period),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get branch statistics
     */
    public function getBranchStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            $branchId = $user->branch_id;

            if (!$branchId && !$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. No branch assigned.'
                ], 403);
            }

            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($period);

            $stats = [
                'transfers' => $this->transferService->getTransferStatistics($branchId, ...$dateRange),
                'queries' => $this->queryService->getQueryStatistics($branchId, ...$dateRange),
                'financial' => \App\Models\StockFinancialImpact::getImpactSummary($branchId, $period),
                'trends' => $this->getBranchTrends($branchId, $period),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload receipt photos
     */
    protected function uploadReceiptPhotos(StockTransfer $transfer, array $photos): void
    {
        $uploadedPhotos = [];
        
        foreach ($photos as $photo) {
            $path = $photo->store("receipts/{$transfer->id}", 'public');
            $uploadedPhotos[] = [
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'uploaded_at' => now()->toISOString(),
            ];
        }

        // Store photos in transfer documents
        $documents = $transfer->documents ?? [];
        $documents['receipt_photos'] = $uploadedPhotos;
        $transfer->update(['documents' => $documents]);
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
            'average_resolution_time' => $queryStats['average_resolution_time'] ?? 0,
            'quality_score' => $this->calculateQualityScore($branchId, ...$dateRange),
        ];
    }

    /**
     * Get branch trends
     */
    protected function getBranchTrends(int $branchId, string $period): array
    {
        $days = $this->getPeriodDays($period);
        $trends = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trends[] = [
                'date' => $date,
                'transfers' => StockTransfer::where('to_branch_id', $branchId)
                                          ->whereDate('created_at', $date)
                                          ->count(),
                'queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                               $q->where('to_branch_id', $branchId);
                             })->whereDate('created_at', $date)->count(),
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
            default:
                return [now()->subWeek()->toDateString(), now()->toDateString()];
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
            default => 7,
        };
    }

    /**
     * Calculate quality score
     */
    protected function calculateQualityScore(int $branchId, string $startDate, string $endDate): float
    {
        // Simple quality score based on queries vs transfers ratio
        $transfers = StockTransfer::where('to_branch_id', $branchId)
                                 ->whereBetween('created_at', [$startDate, $endDate])
                                 ->count();

        $queries = StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                     $q->where('to_branch_id', $branchId);
                   })->whereBetween('created_at', [$startDate, $endDate])
                     ->count();

        if ($transfers === 0) return 100;
        
        $queryRate = ($queries / $transfers) * 100;
        return max(0, 100 - $queryRate);
    }
}