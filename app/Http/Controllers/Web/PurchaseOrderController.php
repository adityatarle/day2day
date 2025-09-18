<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * PurchaseOrderController
 * 
 * Handles Purchase Orders (outgoing orders to vendors) and Received Orders (incoming materials).
 * Following Tally terminology:
 * - Purchase Order: Orders sent FROM main branch TO vendors
 * - Received Order: When purchase order status becomes "received" (materials received FROM vendors)
 */
class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders and received orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['vendor', 'branch', 'user'])
            ->withCount('purchaseOrderItems');

        // Branch filtering for branch managers
        $user = Auth::user();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by vendor
        if ($request->has('vendor_id') && $request->vendor_id !== '') {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by branch (only for super admin and admin)
        if ($request->has('branch_id') && $request->branch_id !== '' && !$user->hasRole('branch_manager')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by PO number
        if ($request->has('search') && $request->search !== '') {
            $query->where('po_number', 'like', '%' . $request->search . '%');
        }

        $purchaseOrders = $query->latest()->paginate(15);

        // Get filter options
        $vendors = Vendor::active()->orderBy('name')->get();
        
        // Branch filtering for dropdowns
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->orderBy('name')->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }
        
        $statuses = ['draft', 'sent', 'confirmed', 'received', 'cancelled'];

        // Statistics (branch-specific for branch managers)
        $statsQuery = PurchaseOrder::query();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $statsQuery->where('branch_id', $user->branch_id);
        }
        
        $stats = [
            'total_orders' => (clone $statsQuery)->count(),
            'pending_orders' => (clone $statsQuery)->whereIn('status', ['draft', 'sent', 'confirmed'])->count(),
            'total_value' => (clone $statsQuery)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'this_month_orders' => (clone $statsQuery)->whereMonth('created_at', now()->month)->count(),
        ];

        return view('purchase-orders.index', compact('purchaseOrders', 'vendors', 'branches', 'statuses', 'stats'));
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create()
    {
        $user = Auth::user();
        $vendors = Vendor::active()->orderBy('name')->get();
        $products = Product::active()->with('vendors')->orderBy('name')->get();
        
        // Branch filtering for branch managers
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $selectedBranch = $user->branch_id;
        } else {
            $branches = Branch::orderBy('name')->get();
            $selectedBranch = null;
        }

        // Optional prefill: branch_request_id from query to link PO to branch request
        $branchRequestId = request('branch_request_id');
        $branchRequest = null;
        if ($branchRequestId) {
            $branchRequest = PurchaseOrder::where('id', $branchRequestId)
                ->where('order_type', 'branch_request')
                ->with(['purchaseOrderItems.product', 'branch'])
                ->first();
        }

        return view('purchase-orders.create', compact('vendors', 'branches', 'products', 'selectedBranch', 'branchRequest'));
    }

    /**
     * Store a newly created purchase order in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Purchase Order Creation Attempt', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()?->role?->name,
            'request_data' => $request->all()
        ]);

        $user = Auth::user();
        
        if (!$user) {
            \Log::error('Purchase Order Creation: User not authenticated');
            return redirect()->route('login')->withErrors(['error' => 'Please log in to create purchase orders.']);
        }
        
        // Branch validation for branch managers
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            if ($request->branch_id != $user->branch_id) {
                \Log::warning('Purchase Order Creation: Branch mismatch', [
                    'user_branch' => $user->branch_id,
                    'requested_branch' => $request->branch_id
                ]);
                return redirect()->back()->withErrors(['branch_id' => 'You can only create purchase orders for your assigned branch.']);
            }
        }
        
        try {
            $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'branch_id' => 'required|exists:branches,id',
                'branch_request_id' => 'nullable|exists:purchase_orders,id',
                // DB enum allows: immediate, 7_days, 15_days, 30_days
                'payment_terms' => 'required|in:immediate,7_days,15_days,30_days',
                'expected_delivery_date' => 'required|date|after:today',
                'transport_cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                // Delivery address
                'delivery_address_type' => 'required|in:admin_main,branch,custom',
                'ship_to_branch_id' => 'nullable|required_if:delivery_address_type,branch|exists:branches,id',
                'delivery_address' => 'nullable|required_if:delivery_address_type,custom|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Purchase Order Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        try {
            $purchaseOrder = DB::transaction(function () use ($request) {
                \Log::info('Purchase Order Transaction Started');
                
                // Generate PO number
                $poNumber = 'PO-' . date('Y') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT);
                \Log::info('Generated PO Number: ' . $poNumber);

                // Pre-calculate totals to satisfy NOT NULL DB constraints on insert
                $calculatedSubtotal = 0;
                foreach ($request->items as $calcItem) {
                    $calculatedSubtotal += ((float) $calcItem['quantity']) * ((float) $calcItem['unit_price']);
                }
                $calculatedTransport = (float) ($request->transport_cost ?? 0);
                $calculatedTax = $calculatedSubtotal * 0.18; // 18% GST
                $calculatedTotal = $calculatedSubtotal + $calculatedTax + $calculatedTransport;

                // Create purchase order (include totals on insert)
                $purchaseOrder = PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'vendor_id' => $request->vendor_id,
                    'branch_id' => $request->branch_id,
                    'branch_request_id' => $request->branch_request_id,
                    'user_id' => Auth::id(),
                    'status' => 'draft',
                    'order_type' => 'purchase_order',
                    'delivery_address_type' => $request->delivery_address_type,
                    'ship_to_branch_id' => $request->delivery_address_type === 'branch' ? $request->ship_to_branch_id : null,
                    'delivery_address' => $request->delivery_address_type === 'custom' ? $request->delivery_address : null,
                    'payment_terms' => $request->payment_terms,
                    'transport_cost' => $calculatedTransport,
                    'notes' => $request->notes,
                    'expected_delivery_date' => $request->expected_delivery_date,
                    'subtotal' => $calculatedSubtotal,
                    'tax_amount' => $calculatedTax,
                    'total_amount' => $calculatedTotal,
                ]);
                
                \Log::info('Purchase Order Created', ['po_id' => $purchaseOrder->id]);

                // Create purchase order items
                $subtotal = 0;
                foreach ($request->items as $index => $item) {
                    $totalPrice = $item['quantity'] * $item['unit_price'];
                    $subtotal += $totalPrice;

                    $poItem = PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $totalPrice,
                    ]);
                    
                    \Log::info('Purchase Order Item Created', [
                        'item_index' => $index,
                        'po_item_id' => $poItem->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity']
                    ]);
                }

                // Recalculate and update totals once more after item creation to ensure consistency
                $taxAmount = $subtotal * 0.18; // 18% GST (can be configurable)
                $purchaseOrder->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $subtotal + $taxAmount + $purchaseOrder->transport_cost,
                ]);
                
                \Log::info('Purchase Order Totals Updated', [
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $subtotal + $taxAmount + $purchaseOrder->transport_cost
                ]);

                // If linked to a branch request, annotate terminology notes
                if ($request->branch_request_id) {
                    $purchaseOrder->update([
                        'terminology_notes' => trim(($purchaseOrder->terminology_notes ?? '') . '\nLinked to Branch Request #' . $request->branch_request_id),
                    ]);
                }
                
                return $purchaseOrder;
            });

            \Log::info('Purchase Order Created Successfully', ['po_id' => $purchaseOrder->id, 'po_number' => $purchaseOrder->po_number]);

            return redirect()->route('purchase-orders.index')
                ->with('success', 'Purchase order created successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Purchase Order Creation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create purchase order. Please check all fields and try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'branch', 'user', 'purchaseOrderItems.product']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified purchase order.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        // Only allow editing of draft orders
        if (!$purchaseOrder->isDraft()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be edited.');
        }

        $vendors = Vendor::active()->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $products = Product::active()->with('vendors')->orderBy('name')->get();
        $purchaseOrder->load('purchaseOrderItems.product');

        return view('purchase-orders.edit', compact('purchaseOrder', 'vendors', 'branches', 'products'));
    }

    /**
     * Update the specified purchase order in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Only allow updating of draft orders
        if (!$purchaseOrder->isDraft()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be updated.');
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'branch_id' => 'required|exists:branches,id',
            'branch_request_id' => 'nullable|exists:purchase_orders,id',
            'payment_terms' => 'required|in:immediate,7_days,15_days,30_days',
            'expected_delivery_date' => 'required|date|after:today',
            'transport_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            // Delivery address
            'delivery_address_type' => 'required|in:admin_main,branch,custom',
            'ship_to_branch_id' => 'nullable|required_if:delivery_address_type,branch|exists:branches,id',
            'delivery_address' => 'nullable|required_if:delivery_address_type,custom|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            // Update purchase order
            $purchaseOrder->update([
                'vendor_id' => $request->vendor_id,
                'branch_id' => $request->branch_id,
                'branch_request_id' => $request->branch_request_id,
                'order_type' => 'purchase_order',
                'delivery_address_type' => $request->delivery_address_type,
                'ship_to_branch_id' => $request->delivery_address_type === 'branch' ? $request->ship_to_branch_id : null,
                'delivery_address' => $request->delivery_address_type === 'custom' ? $request->delivery_address : null,
                'payment_terms' => $request->payment_terms,
                'transport_cost' => $request->transport_cost ?? 0,
                'notes' => $request->notes,
                'expected_delivery_date' => $request->expected_delivery_date,
            ]);

            // Delete existing items
            $purchaseOrder->purchaseOrderItems()->delete();

            // Create new items
            $subtotal = 0;
            foreach ($request->items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $subtotal += $totalPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
            }

            // Update totals
            $taxAmount = $subtotal * 0.18; // 18% GST
            $purchaseOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount + $purchaseOrder->transport_cost,
            ]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated successfully!');
    }

    /**
     * Send purchase order to vendor.
     */
    public function send(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isDraft()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be sent.');
        }

        $purchaseOrder->update(['status' => 'sent']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order sent to vendor successfully!');
    }

    /**
     * Confirm purchase order from vendor.
     */
    public function confirm(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isSent()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only sent purchase orders can be confirmed.');
        }

        $purchaseOrder->update(['status' => 'confirmed']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order confirmed successfully!');
    }

    /**
     * Mark purchase order as received (convert to "Received Order") and update inventory.
     * This represents the transition from Purchase Order to Received Order in Tally terminology.
     * Supports partial receives - accumulates received quantities over multiple receives.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isConfirmed() && !$purchaseOrder->isFulfilled() && !$purchaseOrder->isSent()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only confirmed purchase orders can be received.');
        }

        $request->validate([
            'received_items' => 'required|array',
            'received_items.*.item_id' => 'required|exists:purchase_order_items,id',
            'received_items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            // Determine where to receive stock based on delivery address
            $targetBranchId = $purchaseOrder->branch_id; // default fallback
            if ($purchaseOrder->delivery_address_type === 'branch' && $purchaseOrder->ship_to_branch_id) {
                $targetBranchId = $purchaseOrder->ship_to_branch_id;
            } elseif ($purchaseOrder->delivery_address_type === 'admin_main') {
                $mainBranch = Branch::where('code', 'FDC001')->first();
                if ($mainBranch) {
                    $targetBranchId = $mainBranch->id;
                }
            }

            $hasReceivedItems = false;
            $allItemsReceived = true;

            // Update inventory for each received item
            foreach ($request->received_items as $receivedItem) {
                $purchaseOrderItem = PurchaseOrderItem::find($receivedItem['item_id']);
                $newReceivedQuantity = $receivedItem['received_quantity'];
                
                // Get previously received quantity
                $previouslyReceived = $purchaseOrderItem->received_quantity ?? 0;
                $totalReceivedQuantity = $previouslyReceived + $newReceivedQuantity;

                if ($newReceivedQuantity > 0) {
                    $hasReceivedItems = true;
                    
                    // Add stock to inventory (Received Order - materials received from vendor)
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $targetBranchId,
                        'type' => 'purchase',
                        'quantity' => $newReceivedQuantity,
                        'unit_price' => $purchaseOrderItem->unit_price ?? 0,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'user_id' => auth()->id(),
                        'movement_date' => now(),
                        'notes' => "Partial Receipt - PO: {$purchaseOrder->po_number}",
                    ]);

                    // Update the product's current stock in the branch
                    $product = $purchaseOrderItem->product;
                    $currentStock = $product->getCurrentStock($targetBranchId);
                    $product->updateBranchStock($targetBranchId, $currentStock + $newReceivedQuantity);
                }

                // Update total received quantity in purchase order item (accumulate)
                $purchaseOrderItem->update(['received_quantity' => $totalReceivedQuantity]);
                
                // Check if this item is fully received
                if ($totalReceivedQuantity < $purchaseOrderItem->quantity) {
                    $allItemsReceived = false;
                }
            }

            // Update purchase order receive status
            if ($hasReceivedItems) {
                if (!$purchaseOrder->received_at) {
                    $purchaseOrder->update([
                        'received_at' => now(),
                        'received_by' => auth()->id(),
                    ]);
                }
                
                // Update receive status and totals using normalized aggregates
                $purchaseOrder->recalculateReceiptAggregates();
                
                // If all items are fully received, mark as complete
                // recalculateReceiptAggregates already auto-marks as received when complete
            }
        });

        $message = 'Materials received successfully! ';
        if ($purchaseOrder->receive_status === 'partial') {
            $message .= 'Purchase Order partially received. You can receive remaining items later.';
        } else {
            $message .= 'Purchase Order completely received and inventory updated.';
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', $message);
    }

    /**
     * Cancel purchase order.
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->isReceived()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Cannot cancel a received purchase order.');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order cancelled successfully!');
    }

    /**
     * Show receive form for purchase order.
     */
    public function showReceiveForm(Request $request, PurchaseOrder $purchaseOrder = null)
    {
        $user = Auth::user();
        
        // Get pending purchase orders for dropdown
        $pendingOrdersQuery = PurchaseOrder::with(['vendor', 'branch'])
            ->pendingToReceive()
            ->orderBy('expected_delivery_date', 'asc');
        
        // Branch filtering for branch managers
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $pendingOrdersQuery->where('branch_id', $user->branch_id);
        }
        
        $pendingOrders = $pendingOrdersQuery->get();
        
        // If PO is selected from dropdown or passed directly
        if ($request->has('po_id')) {
            $purchaseOrder = PurchaseOrder::find($request->po_id);
        }
        
        if ($purchaseOrder) {
            if (!$purchaseOrder->isConfirmed()) {
                return redirect()->route('purchase-orders.index')
                    ->with('error', 'Only confirmed purchase orders can be received.');
            }
            
            $purchaseOrder->load(['vendor', 'branch', 'purchaseOrderItems.product']);
        }

        return view('purchase-orders.receive', compact('purchaseOrder', 'pendingOrders'));
    }

    /**
     * Generate purchase order PDF.
     */
    public function generatePdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'branch', 'user', 'purchaseOrderItems.product']);

        // For now, return a view that can be printed or saved as PDF
        return view('purchase-orders.pdf', compact('purchaseOrder'));
    }

    /**
     * Get vendor products with pricing for AJAX.
     */
    public function getVendorProducts(Vendor $vendor)
    {
        $products = $vendor->products()
            ->select('products.*', 'product_vendors.supply_price', 'product_vendors.is_primary_supplier')
            ->orderBy('products.name')
            ->get();

        return response()->json($products);
    }

    /**
     * Dashboard for purchase order statistics.
     */
    public function dashboard()
    {
        $stats = [
            'total_orders' => PurchaseOrder::count(),
            'draft_orders' => PurchaseOrder::where('status', 'draft')->count(),
            'sent_orders' => PurchaseOrder::where('status', 'sent')->count(),
            'confirmed_orders' => PurchaseOrder::where('status', 'confirmed')->count(),
            'received_orders' => PurchaseOrder::where('status', 'received')->count(),
            'cancelled_orders' => PurchaseOrder::where('status', 'cancelled')->count(),
            'total_value' => PurchaseOrder::where('status', '!=', 'cancelled')->sum('total_amount'),
            'this_month_value' => PurchaseOrder::whereMonth('created_at', now()->month)
                ->where('status', '!=', 'cancelled')->sum('total_amount'),
        ];

        // Recent orders
        $recentOrders = PurchaseOrder::with(['vendor', 'branch'])
            ->latest()
            ->take(10)
            ->get();

        // Top vendors by order value
        $topVendors = Vendor::withSum('purchaseOrders', 'total_amount')
            ->orderByDesc('purchase_orders_sum_total_amount')
            ->take(5)
            ->get();

        // Pending deliveries
        $pendingDeliveries = PurchaseOrder::with(['vendor', 'branch'])
            ->where('status', 'confirmed')
            ->where('expected_delivery_date', '<=', now()->addDays(3))
            ->orderBy('expected_delivery_date')
            ->get();

        return view('purchase-orders.dashboard', compact('stats', 'recentOrders', 'topVendors', 'pendingDeliveries'));
    }

    /**
     * Display branch purchase requests (for sub-branches).
     * Sub-branches can only send purchase requests to main branch.
     */
    public function branchRequests(Request $request)
    {
        $user = Auth::user();
        
        // Only branch managers can access this
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        $query = PurchaseOrder::with(['vendor', 'branch', 'user'])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->withCount('purchaseOrderItems');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by request number
        if ($request->has('search') && $request->search !== '') {
            $query->where('po_number', 'like', '%' . $request->search . '%');
        }

        $purchaseRequests = $query->latest()->paginate(15);

        $stats = [
            'total_requests' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')->count(),
            'pending_requests' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('status', 'pending')->count(),
            'approved_requests' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('status', 'approved')->count(),
            'this_month_requests' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->whereMonth('created_at', now()->month)->count(),
        ];

        return view('purchase-requests.index', compact('purchaseRequests', 'stats'));
    }

    /**
     * Show form for creating branch purchase request.
     */
    public function createBranchRequest()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        $products = Product::active()->orderBy('name')->get();
        $branch = Branch::find($user->branch_id);

        return view('purchase-requests.create', compact('products', 'branch'));
    }

    /**
     * Store branch purchase request.
     */
    public function storeBranchRequest(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        $request->validate([
            'expected_delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $user) {
            // Generate request number
            $requestNumber = 'PR-' . date('Y') . '-' . str_pad(
                PurchaseOrder::where('order_type', 'branch_request')->count() + 1, 
                4, '0', STR_PAD_LEFT
            );

            // Get or create a system vendor for branch requests
            $systemVendor = \App\Models\Vendor::firstOrCreate(
                ['code' => 'SYS001'],
                [
                    'name' => 'System - Branch Requests',
                    'code' => 'SYS001',
                    'email' => 'system@branch-requests.com',
                    'phone' => '0000000000',
                    'address' => 'System Vendor for Branch Requests',
                    'gst_number' => 'SYSTEM001',
                    'is_active' => true,
                ]
            );

            // Create purchase request (not to vendor, but to main branch)
            $purchaseRequest = PurchaseOrder::create([
                'po_number' => $requestNumber,
                'vendor_id' => $systemVendor->id, // Use system vendor for branch requests
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'status' => 'pending',
                'order_type' => 'branch_request',
                'payment_terms' => 'immediate',
                'transport_cost' => 0,
                'notes' => $request->notes,
                'expected_delivery_date' => $request->expected_delivery_date,
                'priority' => $request->priority,
                'terminology_notes' => 'Branch Purchase Request - sent to main branch for approval and fulfillment',
            ]);

            // Create purchase request items
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $estimatedPrice = $product->purchase_price ?? 0; // Use product's standard price for estimation
                $totalPrice = $item['quantity'] * $estimatedPrice;
                $subtotal += $totalPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseRequest->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $estimatedPrice,
                    'total_price' => $totalPrice,
                    'notes' => $item['reason'],
                ]);
            }

            // Update totals (estimated)
            $purchaseRequest->update([
                'subtotal' => $subtotal,
                'tax_amount' => 0, // Will be calculated by main branch
                'total_amount' => $subtotal,
            ]);
        });

        return redirect()->route('purchase-requests.index')
            ->with('success', 'Purchase request sent to main branch successfully!');
    }

    /**
     * Show branch purchase request.
     */
    public function showBranchRequest(PurchaseOrder $purchaseOrder)
    {
        $user = Auth::user();
        
        // Verify access
        if (!$user->hasRole('branch_manager') || $purchaseOrder->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $purchaseOrder->load(['branch', 'user', 'purchaseOrderItems.product']);

        return view('purchase-requests.show', compact('purchaseOrder'));
    }
}