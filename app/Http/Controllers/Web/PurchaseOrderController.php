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

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders.
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

        return view('purchase-orders.create', compact('vendors', 'branches', 'products', 'selectedBranch'));
    }

    /**
     * Store a newly created purchase order in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Branch validation for branch managers
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            if ($request->branch_id != $user->branch_id) {
                return redirect()->back()->withErrors(['branch_id' => 'You can only create purchase orders for your assigned branch.']);
            }
        }
        
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'branch_id' => 'required|exists:branches,id',
            'payment_terms' => 'required|string',
            'expected_delivery_date' => 'required|date|after:today',
            'transport_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate PO number
            $poNumber = 'PO-' . date('Y') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'vendor_id' => $request->vendor_id,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::id(),
                'status' => 'draft',
                'payment_terms' => $request->payment_terms,
                'transport_cost' => $request->transport_cost ?? 0,
                'notes' => $request->notes,
                'expected_delivery_date' => $request->expected_delivery_date,
            ]);

            // Create purchase order items
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
            $taxAmount = $subtotal * 0.18; // 18% GST (can be configurable)
            $purchaseOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount + $purchaseOrder->transport_cost,
            ]);
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order created successfully!');
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
            'payment_terms' => 'required|string',
            'expected_delivery_date' => 'required|date|after:today',
            'transport_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
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
     * Mark purchase order as received and update inventory.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isConfirmed()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only confirmed purchase orders can be received.');
        }

        $request->validate([
            'received_items' => 'required|array',
            'received_items.*.item_id' => 'required|exists:purchase_order_items,id',
            'received_items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            // Update purchase order status
            $purchaseOrder->markAsReceived();

            // Update inventory for each received item
            foreach ($request->received_items as $receivedItem) {
                $purchaseOrderItem = PurchaseOrderItem::find($receivedItem['item_id']);
                $receivedQuantity = $receivedItem['received_quantity'];

                if ($receivedQuantity > 0) {
                    // Add stock to inventory
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $purchaseOrder->branch_id,
                        'type' => 'purchase',
                        'quantity' => $receivedQuantity,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'notes' => "Received from PO: {$purchaseOrder->po_number}",
                    ]);

                    // Update the product's current stock in the branch
                    $product = $purchaseOrderItem->product;
                    $currentStock = $product->getCurrentStock($purchaseOrder->branch_id);
                    $product->updateBranchStock($purchaseOrder->branch_id, $currentStock + $receivedQuantity);
                }

                // Update received quantity in purchase order item
                $purchaseOrderItem->update(['received_quantity' => $receivedQuantity]);
            }
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order received and inventory updated successfully!');
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
    public function showReceiveForm(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isConfirmed()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only confirmed purchase orders can be received.');
        }

        $purchaseOrder->load(['vendor', 'branch', 'purchaseOrderItems.product']);

        return view('purchase-orders.receive', compact('purchaseOrder'));
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
}