<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * BranchProductOrderController
 * 
 * Handles product orders from branch managers to admin.
 * Branch managers can only order products, not select vendors.
 */
class BranchProductOrderController extends Controller
{
    /**
     * Display branch product orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        $query = PurchaseOrder::with(['vendor', 'user'])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->withCount('purchaseOrderItems');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->has('search') && $request->search !== '') {
            $query->where('po_number', 'like', '%' . $request->search . '%');
        }

        $productOrders = $query->latest()->paginate(15);

        $stats = [
            'total_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')->count(),
            'pending_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('status', 'pending')->count(),
            'approved_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('status', 'approved')->count(),
            'this_month_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->whereMonth('created_at', now()->month)->count(),
        ];

        return view('branch.product-orders.index', compact('productOrders', 'stats'));
    }

    /**
     * Show the form for creating a new product order.
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        $products = Product::active()->orderBy('name')->get();
        $branch = Branch::find($user->branch_id);

        return view('branch.product-orders.create', compact('products', 'branch'));
    }

    /**
     * Store a newly created product order.
     */
    public function store(Request $request)
    {
        \Log::info('Product Order Store method called');
        
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        // Debug: Log the request data
        \Log::info('Product Order Request Data:', $request->all());

        // Filter out empty items and items with INDEX key
        $items = collect($request->items)->filter(function ($item, $key) {
            // Remove items with INDEX key or empty values
            return $key !== 'INDEX' && 
                   !empty($item['product_id']) && 
                   !empty($item['quantity']) && 
                   !empty($item['reason']);
        })->values()->toArray();

        // Log filtered items for debugging
        \Log::info('Filtered items after removing INDEX and empty items:', $items);

        // Update the request with filtered items
        $request->merge(['items' => $items]);

        try {
            $request->validate([
                'expected_delivery_date' => 'required|date|after:today',
                'notes' => 'nullable|string',
                'priority' => 'required|in:low,medium,high,urgent',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.reason' => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        DB::transaction(function () use ($request, $user) {
            // Generate request number
            $requestNumber = 'BR-' . date('Y') . '-' . str_pad(
                PurchaseOrder::where('order_type', 'branch_request')->count() + 1, 
                4, '0', STR_PAD_LEFT
            );

            // Create product order request
            $productOrder = PurchaseOrder::create([
                'po_number' => $requestNumber,
                'vendor_id' => null, // No vendor - admin will assign
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'status' => 'pending',
                'order_type' => 'branch_request',
                'payment_terms' => 'immediate',
                'transport_cost' => 0,
                'notes' => $request->notes,
                'expected_delivery_date' => $request->expected_delivery_date,
                'priority' => $request->priority,
                'terminology_notes' => 'Branch Product Order - sent to admin for vendor assignment and fulfillment',
            ]);

            // Create product order items
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $estimatedPrice = $product->purchase_price ?? 0;
                $totalPrice = $item['quantity'] * $estimatedPrice;
                $subtotal += $totalPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $productOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $estimatedPrice,
                    'total_price' => $totalPrice,
                    'notes' => $item['reason'],
                ]);
            }

            // Update totals (estimated)
            $productOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => 0, // Will be calculated by admin
                'total_amount' => $subtotal,
            ]);
        });

        return redirect()->route('branch.product-orders.index')
            ->with('success', 'Product order sent to admin successfully!');
    }

    /**
     * Display the specified product order.
     */
    public function show(PurchaseOrder $productOrder)
    {
        $user = Auth::user();
        
        // Verify access
        if (!$user->hasRole('branch_manager') || $productOrder->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $productOrder->load(['branch', 'user', 'vendor', 'purchaseOrderItems.product']);

        return view('branch.product-orders.show', compact('productOrder'));
    }

    /**
     * Show the form for editing the specified product order.
     */
    public function edit(PurchaseOrder $productOrder)
    {
        $user = Auth::user();
        
        // Verify access and status
        if (!$user->hasRole('branch_manager') || $productOrder->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if ($productOrder->status !== 'pending') {
            return redirect()->route('branch.product-orders.show', $productOrder)
                ->with('error', 'Only pending orders can be edited.');
        }

        $products = Product::active()->orderBy('name')->get();
        $branch = Branch::find($user->branch_id);
        $productOrder->load('purchaseOrderItems.product');

        return view('branch.product-orders.edit', compact('productOrder', 'products', 'branch'));
    }

    /**
     * Update the specified product order.
     */
    public function update(Request $request, PurchaseOrder $productOrder)
    {
        $user = Auth::user();
        
        // Verify access and status
        if (!$user->hasRole('branch_manager') || $productOrder->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if ($productOrder->status !== 'pending') {
            return redirect()->route('branch.product-orders.show', $productOrder)
                ->with('error', 'Only pending orders can be updated.');
        }

        // Filter out empty items and items with INDEX key
        $items = collect($request->items)->filter(function ($item, $key) {
            // Remove items with INDEX key or empty values
            return $key !== 'INDEX' && 
                   !empty($item['product_id']) && 
                   !empty($item['quantity']) && 
                   !empty($item['reason']);
        })->values()->toArray();

        // Log filtered items for debugging
        \Log::info('Update - Filtered items after removing INDEX and empty items:', $items);

        // Update the request with filtered items
        $request->merge(['items' => $items]);

        $request->validate([
            'expected_delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $productOrder) {
            // Update product order
            $productOrder->update([
                'notes' => $request->notes,
                'expected_delivery_date' => $request->expected_delivery_date,
                'priority' => $request->priority,
            ]);

            // Delete existing items
            $productOrder->purchaseOrderItems()->delete();

            // Create new items
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $estimatedPrice = $product->purchase_price ?? 0;
                $totalPrice = $item['quantity'] * $estimatedPrice;
                $subtotal += $totalPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $productOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $estimatedPrice,
                    'total_price' => $totalPrice,
                    'notes' => $item['reason'],
                ]);
            }

            // Update totals
            $productOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total_amount' => $subtotal,
            ]);
        });

        return redirect()->route('branch.product-orders.show', $productOrder)
            ->with('success', 'Product order updated successfully!');
    }

    /**
     * Remove the specified product order.
     */
    public function destroy(PurchaseOrder $productOrder)
    {
        $user = Auth::user();
        
        // Verify access and status
        if (!$user->hasRole('branch_manager') || $productOrder->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if ($productOrder->status !== 'pending') {
            return redirect()->route('branch.product-orders.index')
                ->with('error', 'Only pending orders can be deleted.');
        }

        $productOrder->delete();

        return redirect()->route('branch.product-orders.index')
            ->with('success', 'Product order deleted successfully.');
    }
}