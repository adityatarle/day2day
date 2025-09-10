<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Batch;
use App\Services\StockTransferService;
use App\Services\StockQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    protected $stockTransferService;
    protected $stockQueryService;

    public function __construct(StockTransferService $stockTransferService, StockQueryService $stockQueryService)
    {
        $this->stockTransferService = $stockTransferService;
        $this->stockQueryService = $stockQueryService;
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Display listing of stock transfers
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with(['toBranch', 'fromBranch', 'initiatedBy', 'items.product'])
                              ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('to_branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transfers = $query->paginate(20);
        $branches = Branch::active()->get();

        // Get summary statistics
        $stats = [
            'total' => StockTransfer::count(),
            'pending' => StockTransfer::where('status', 'pending')->count(),
            'in_transit' => StockTransfer::where('status', 'in_transit')->count(),
            'delivered' => StockTransfer::where('status', 'delivered')->count(),
            'confirmed' => StockTransfer::where('status', 'confirmed')->count(),
            'overdue' => $this->stockTransferService->getOverdueTransfers()->count(),
        ];

        return view('admin.stock-transfers.index', compact('transfers', 'branches', 'stats'));
    }

    /**
     * Show form for creating new stock transfer
     */
    public function create()
    {
        $branches = Branch::active()->get();
        $products = Product::active()->with('batches')->get();

        return view('admin.stock-transfers.create', compact('branches', 'products'));
    }

    /**
     * Store new stock transfer
     */
    public function store(Request $request)
    {
        $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'from_branch_id' => 'nullable|exists:branches,id',
            'expected_delivery' => 'nullable|date|after:today',
            'transport_vendor' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'transport_cost' => 'nullable|numeric|min:0',
            'dispatch_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.quantity_sent' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.unit_of_measurement' => 'required|string|max:20',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.item_notes' => 'nullable|string',
        ]);

        try {
            $transfer = $this->stockTransferService->createStockTransfer(
                $request->only([
                    'to_branch_id', 'from_branch_id', 'expected_delivery', 
                    'transport_vendor', 'vehicle_number', 'driver_name', 
                    'driver_phone', 'transport_cost', 'dispatch_notes'
                ]),
                $request->items,
                Auth::user()
            );

            return redirect()->route('admin.stock-transfers.show', $transfer)
                           ->with('success', 'Stock transfer created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to create stock transfer: ' . $e->getMessage());
        }
    }

    /**
     * Show specific stock transfer
     */
    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load([
            'toBranch', 'fromBranch', 'initiatedBy', 
            'items.product', 'items.batch',
            'queries.raisedBy', 'queries.assignedTo',
            'transportExpenses',
            'reconciliations.items.product'
        ]);

        // Get related statistics
        $stats = [
            'total_items' => $stockTransfer->getTotalItemsCount(),
            'total_value' => $stockTransfer->total_value,
            'transport_expenses' => $stockTransfer->getTotalTransportExpenses(),
            'pending_queries' => $stockTransfer->getPendingQueriesCount(),
            'critical_queries' => $stockTransfer->hasCriticalQueries(),
        ];

        return view('admin.stock-transfers.show', compact('stockTransfer', 'stats'));
    }

    /**
     * Dispatch stock transfer
     */
    public function dispatch(Request $request, StockTransfer $stockTransfer)
    {
        $request->validate([
            'dispatch_notes' => 'nullable|string',
            'vehicle_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
        ]);

        if ($stockTransfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be dispatched.');
        }

        try {
            $result = $this->stockTransferService->dispatchTransfer(
                $stockTransfer,
                $request->only(['dispatch_notes', 'vehicle_number', 'driver_name', 'driver_phone'])
            );

            if ($result) {
                return back()->with('success', 'Stock transfer dispatched successfully.');
            } else {
                return back()->with('error', 'Failed to dispatch stock transfer.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to dispatch stock transfer: ' . $e->getMessage());
        }
    }

    /**
     * Cancel stock transfer
     */
    public function cancel(Request $request, StockTransfer $stockTransfer)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if (in_array($stockTransfer->status, ['confirmed', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel transfer with current status.');
        }

        try {
            $result = $this->stockTransferService->cancelTransfer(
                $stockTransfer,
                $request->cancellation_reason,
                Auth::user()
            );

            if ($result) {
                return redirect()->route('admin.stock-transfers.index')
                               ->with('success', 'Stock transfer cancelled successfully.');
            } else {
                return back()->with('error', 'Failed to cancel stock transfer.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel stock transfer: ' . $e->getMessage());
        }
    }

    /**
     * Show stock transfer queries
     */
    public function queries(Request $request)
    {
        $query = \App\Models\StockTransferQuery::with([
            'stockTransfer.toBranch', 'raisedBy', 'assignedTo', 'stockTransferItem.product'
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

        $queries = $query->paginate(20);
        $branches = Branch::active()->get();

        // Get summary statistics
        $stats = $this->stockQueryService->getQueryStatistics();

        return view('admin.stock-transfers.queries', compact('queries', 'branches', 'stats'));
    }

    /**
     * Show specific query details
     */
    public function showQuery(\App\Models\StockTransferQuery $query)
    {
        $query->load([
            'stockTransfer.toBranch', 'stockTransferItem.product',
            'raisedBy', 'assignedTo', 'responses.user',
            'financialImpacts'
        ]);

        return view('admin.stock-transfers.query-details', compact('query'));
    }

    /**
     * Assign query to admin
     */
    public function assignQuery(Request $request, \App\Models\StockTransferQuery $query)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $admin = \App\Models\User::find($request->assigned_to);
        
        if (!$admin->isAdmin() && !$admin->isSuperAdmin()) {
            return back()->with('error', 'Can only assign to admin users.');
        }

        try {
            $result = $this->stockQueryService->assignQuery($query, $admin);

            if ($result) {
                return back()->with('success', 'Query assigned successfully.');
            } else {
                return back()->with('error', 'Failed to assign query.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign query: ' . $e->getMessage());
        }
    }

    /**
     * Add response to query
     */
    public function addQueryResponse(Request $request, \App\Models\StockTransferQuery $query)
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
    public function resolveQuery(Request $request, \App\Models\StockTransferQuery $query)
    {
        $request->validate([
            'resolution' => 'required|string',
            'recovery_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $result = $this->stockQueryService->resolveQuery(
                $query,
                $request->resolution,
                Auth::user(),
                $request->recovery_amount
            );

            if ($result) {
                return back()->with('success', 'Query resolved successfully.');
            } else {
                return back()->with('error', 'Failed to resolve query.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resolve query: ' . $e->getMessage());
        }
    }

    /**
     * Get dashboard data for admin
     */
    public function dashboard()
    {
        $stats = [
            'transfers' => $this->stockTransferService->generatePerformanceReport(),
            'queries' => $this->stockQueryService->getQueryStatistics(),
            'overdue_transfers' => $this->stockTransferService->getOverdueTransfers()->take(5),
            'overdue_queries' => $this->stockQueryService->getOverdueQueries()->take(5),
        ];

        return view('admin.stock-transfers.dashboard', compact('stats'));
    }

    /**
     * Generate performance report
     */
    public function report(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'report_type' => 'required|in:transfers,queries,financial',
        ]);

        $branchId = $request->branch_id;
        $startDate = $request->date_from;
        $endDate = $request->date_to;

        switch ($request->report_type) {
            case 'transfers':
                $data = $this->stockTransferService->generatePerformanceReport($branchId, $startDate, $endDate);
                break;
            case 'queries':
                $data = $this->stockQueryService->getQueryStatistics($branchId, $startDate, $endDate);
                break;
            case 'financial':
                $data = $this->generateFinancialReport($branchId, $startDate, $endDate);
                break;
        }

        return view('admin.stock-transfers.report', compact('data', 'request'));
    }

    /**
     * Generate financial report
     */
    protected function generateFinancialReport(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $transferQuery = StockTransfer::query();
        $impactQuery = \App\Models\StockFinancialImpact::query();

        if ($branchId) {
            $transferQuery->where('to_branch_id', $branchId);
            $impactQuery->where('branch_id', $branchId);
        }

        if ($startDate && $endDate) {
            $transferQuery->whereBetween('created_at', [$startDate, $endDate]);
            $impactQuery->whereBetween('impact_date', [$startDate, $endDate]);
        }

        $transfers = $transferQuery->get();
        $impacts = $impactQuery->get();

        return [
            'transfer_costs' => [
                'total_transfer_value' => $transfers->sum('total_value'),
                'total_transport_cost' => $transfers->sum('transport_cost'),
                'average_transport_cost' => $transfers->avg('transport_cost'),
                'transport_cost_percentage' => $transfers->sum('total_value') > 0 ? 
                    ($transfers->sum('transport_cost') / $transfers->sum('total_value')) * 100 : 0,
            ],
            'financial_impacts' => [
                'total_losses' => $impacts->where('impact_category', 'direct_loss')->sum('amount'),
                'total_recoveries' => $impacts->sum('recovered_amount'),
                'net_impact' => $impacts->sum('amount') - $impacts->sum('recovered_amount'),
                'recoverable_amount' => $impacts->where('is_recoverable', true)->sum('amount') - 
                                      $impacts->where('is_recoverable', true)->sum('recovered_amount'),
            ],
            'impact_breakdown' => $impacts->groupBy('impact_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount'),
                    'recovered_amount' => $group->sum('recovered_amount'),
                ];
            }),
        ];
    }
}