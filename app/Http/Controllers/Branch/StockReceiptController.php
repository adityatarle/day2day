<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferQuery;
use App\Models\StockReconciliation;
use App\Services\StockTransferService;
use App\Services\StockQueryService;
use App\Services\StockReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockReceiptController extends Controller
{
    protected $stockTransferService;
    protected $stockQueryService;
    protected $reconciliationService;

    public function __construct(
        StockTransferService $stockTransferService,
        StockQueryService $stockQueryService,
        StockReconciliationService $reconciliationService
    ) {
        $this->stockTransferService = $stockTransferService;
        $this->stockQueryService = $stockQueryService;
        $this->reconciliationService = $reconciliationService;
        $this->middleware('auth');
        $this->middleware('role:branch_manager,admin,super_admin');
    }

    /**
     * Display incoming stock transfers for branch
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        if (!$branchId && !$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Access denied. No branch assigned.');
        }

        $query = StockTransfer::with(['fromBranch', 'initiatedBy', 'items.product'])
                              ->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->where('to_branch_id', $branchId);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transfers = $query->paginate(20);

        // Get summary statistics
        $stats = $this->stockTransferService->getTransferStatistics($branchId);

        return view('branch.stock-receipts.index', compact('transfers', 'stats'));
    }

    /**
     * Show specific stock transfer for receipt confirmation
     */
    public function show(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Check if user has access to this transfer
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $stockTransfer->load([
            'fromBranch', 'initiatedBy', 
            'items.product', 'items.batch',
            'queries.raisedBy',
            'reconciliations.items.product'
        ]);

        return view('branch.stock-receipts.show', compact('stockTransfer'));
    }

    /**
     * Confirm receipt of stock transfer
     */
    public function confirmReceipt(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if ($stockTransfer->status !== 'delivered') {
            return back()->with('error', 'Can only confirm receipt of delivered transfers.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:stock_transfer_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->stockTransferService->confirmReceipt(
                $stockTransfer,
                $request->items,
                $user
            );

            if ($result) {
                return redirect()->route('branch.stock-receipts.show', $stockTransfer)
                               ->with('success', 'Stock receipt confirmed successfully.');
            } else {
                return back()->with('error', 'Failed to confirm stock receipt.');
            }

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to confirm stock receipt: ' . $e->getMessage());
        }
    }

    /**
     * Show form to raise query about stock transfer
     */
    public function createQuery(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $stockTransfer->load(['items.product']);

        return view('branch.stock-receipts.create-query', compact('stockTransfer'));
    }

    /**
     * Store new query about stock transfer
     */
    public function storeQuery(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
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

        try {
            $queryData = $request->only([
                'stock_transfer_item_id', 'query_type', 'priority', 
                'title', 'description', 'expected_quantity', 'actual_quantity'
            ]);
            $queryData['stock_transfer_id'] = $stockTransfer->id;

            $query = $this->stockQueryService->createQuery($queryData, $user);

            // Upload evidence files if provided
            if ($request->hasFile('evidence_photos')) {
                $this->stockQueryService->uploadEvidence($query, $request->file('evidence_photos'), 'photos');
            }

            if ($request->hasFile('documents')) {
                $this->stockQueryService->uploadEvidence($query, $request->file('documents'), 'documents');
            }

            return redirect()->route('branch.stock-receipts.show', $stockTransfer)
                           ->with('success', 'Query raised successfully. Query number: ' . $query->query_number);

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to raise query: ' . $e->getMessage());
        }
    }

    /**
     * Show branch queries
     */
    public function queries(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        if (!$branchId && !$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Access denied. No branch assigned.');
        }

        $query = StockTransferQuery::with([
            'stockTransfer', 'stockTransferItem.product', 'assignedTo'
        ])->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

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

        $queries = $query->paginate(20);

        // Get summary statistics
        $stats = $this->stockQueryService->getQueryStatistics($branchId);

        return view('branch.stock-receipts.queries', compact('queries', 'stats'));
    }

    /**
     * Show specific query details
     */
    public function showQuery(StockTransferQuery $query)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $query->stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $query->load([
            'stockTransfer', 'stockTransferItem.product',
            'raisedBy', 'assignedTo', 'responses.user',
            'financialImpacts'
        ]);

        return view('branch.stock-receipts.query-details', compact('query'));
    }

    /**
     * Add response to query
     */
    public function addQueryResponse(Request $request, StockTransferQuery $query)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $query->stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        try {
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

            $this->stockQueryService->addResponse(
                $query,
                $user,
                $request->message,
                'comment',
                $attachments,
                false
            );

            return back()->with('success', 'Response added successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add response: ' . $e->getMessage());
        }
    }

    /**
     * Escalate query
     */
    public function escalateQuery(Request $request, StockTransferQuery $query)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $query->stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if (!in_array($query->status, ['open', 'in_progress'])) {
            return back()->with('error', 'Can only escalate open or in-progress queries.');
        }

        $request->validate([
            'escalation_reason' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->stockQueryService->escalateQuery(
                $query,
                $user,
                $request->escalation_reason
            );

            if ($result) {
                return back()->with('success', 'Query escalated successfully.');
            } else {
                return back()->with('error', 'Failed to escalate query.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to escalate query: ' . $e->getMessage());
        }
    }

    /**
     * Show form for stock reconciliation
     */
    public function createReconciliation(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if ($stockTransfer->status !== 'confirmed') {
            return back()->with('error', 'Can only reconcile confirmed transfers.');
        }

        $stockTransfer->load(['items.product']);

        return view('branch.stock-receipts.create-reconciliation', compact('stockTransfer'));
    }

    /**
     * Store stock reconciliation
     */
    public function storeReconciliation(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.system_quantity' => 'required|numeric|min:0',
            'items.*.physical_quantity' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string|max:500',
        ]);

        try {
            $reconciliation = $this->reconciliationService->createReconciliation(
                $stockTransfer,
                $request->items,
                $user,
                $request->notes
            );

            return redirect()->route('branch.stock-receipts.show', $stockTransfer)
                           ->with('success', 'Stock reconciliation created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to create reconciliation: ' . $e->getMessage());
        }
    }

    /**
     * Show branch dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        if (!$branchId && !$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Access denied. No branch assigned.');
        }

        $stats = [
            'transfers' => $this->stockTransferService->getTransferStatistics($branchId),
            'queries' => $this->stockQueryService->getQueryStatistics($branchId),
            'pending_receipts' => StockTransfer::where('to_branch_id', $branchId)
                                             ->where('status', 'delivered')
                                             ->count(),
            'overdue_transfers' => $this->stockTransferService->getOverdueTransfers($branchId)->count(),
            'open_queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                                $q->where('to_branch_id', $branchId);
                              })->where('status', 'open')->count(),
        ];

        // Get recent activities
        $recentTransfers = StockTransfer::where('to_branch_id', $branchId)
                                       ->with(['fromBranch', 'items.product'])
                                       ->orderBy('created_at', 'desc')
                                       ->take(5)
                                       ->get();

        $recentQueries = StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branchId) {
                           $q->where('to_branch_id', $branchId);
                         })->with(['stockTransfer', 'stockTransferItem.product'])
                           ->orderBy('created_at', 'desc')
                           ->take(5)
                           ->get();

        return view('branch.stock-receipts.dashboard', compact('stats', 'recentTransfers', 'recentQueries'));
    }
}