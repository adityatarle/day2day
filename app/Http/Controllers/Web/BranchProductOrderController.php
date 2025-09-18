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
use Illuminate\Database\UniqueConstraintViolationException;

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
                ->where('status', 'draft')->count(),
            'approved_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('status', 'sent')->count(),
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
            // Calculate subtotal before creating the purchase order
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $estimatedPrice = $product->purchase_price ?? 0;
                $totalPrice = $item['quantity'] * $estimatedPrice;
                $subtotal += $totalPrice;
            }

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

            // Generate a unique request number with retry on rare race conditions
            $productOrder = null;
            $maxRetries = 5;
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                $year = now()->year;
                $prefix = 'BR-' . $year . '-';

                $nextSeq = DB::table('purchase_orders')
                    ->where('order_type', 'branch_request')
                    ->whereYear('created_at', $year)
                    ->where('po_number', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->selectRaw("COALESCE(MAX(CAST(SUBSTRING_INDEX(po_number, '-', -1) AS UNSIGNED)), 0) AS max_seq")
                    ->value('max_seq') + 1;

                $requestNumber = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                try {
                    $productOrder = PurchaseOrder::create([
                        'po_number' => $requestNumber,
                        'vendor_id' => $systemVendor->id, // Use system vendor for branch requests
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                        'status' => 'draft',
                        'order_type' => 'branch_request',
                        'payment_terms' => 'immediate',
                        'transport_cost' => 0,
                        'subtotal' => $subtotal,
                        'tax_amount' => 0, // Will be calculated by admin
                        'total_amount' => $subtotal,
                        'notes' => $request->notes,
                        'expected_delivery_date' => $request->expected_delivery_date,
                        'priority' => $request->priority,
                        'terminology_notes' => 'Branch Product Order - sent to admin for vendor assignment and fulfillment',
                    ]);
                    break;
                } catch (UniqueConstraintViolationException $e) {
                    if ($attempt === $maxRetries) {
                        throw $e;
                    }
                    // Retry with a fresh sequence value
                }
            }

            // Create product order items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $estimatedPrice = $product->purchase_price ?? 0;
                $totalPrice = $item['quantity'] * $estimatedPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $productOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $estimatedPrice,
                    'total_price' => $totalPrice,
                    'notes' => $item['reason'],
                ]);
            }
        }, 1);

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

        // Load detailed relations for item tracking and entries
        $productOrder->load([
            'branch', 
            'user', 
            'vendor', 
            'purchaseOrderItems.product',
            'purchaseEntries' => function($q) { $q->orderBy('entry_date', 'desc'); },
            'purchaseEntries.purchaseEntryItems.product',
            'purchaseEntries.user'
        ]);

        // Ensure latest aggregates before display
        $productOrder->recalculateReceiptAggregates();

        // Compute per-item tracking for ordered/received/remaining using normalized totals
        $itemTracking = $productOrder->purchaseOrderItems->map(function ($item) use ($productOrder) {
            $receivedFromEntries = $productOrder->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('received_quantity');
            $direct = (float) ($item->received_quantity ?? 0);
            $totalReceived = max($direct, (float) $receivedFromEntries);

            $remaining = ((float) ($item->quantity ?? 0)) - $totalReceived;

            return [
                'item' => $item,
                'ordered_quantity' => (float) ($item->quantity ?? 0),
                'received_quantity' => (float) $totalReceived,
                'remaining_quantity' => max(0, (float) $remaining),
                'unit_price' => (float) ($item->unit_price ?? 0),
                'total_price' => (float) ($item->total_price ?? 0),
            ];
        });

        // Financials (keep existing fields and provide computed helpers)
        $financials = [
            'subtotal' => (float) $productOrder->subtotal,
            'discount' => 0.0, // placeholder if discounting is added later
            'tax_cgst' => 0.0,
            'tax_sgst' => 0.0,
            'tax_igst' => 0.0,
            'transport' => (float) $productOrder->transport_cost,
            'tax_total' => (float) $productOrder->tax_amount,
            'grand_total' => (float) $productOrder->total_amount,
        ];

        return view('branch.product-orders.show', compact('productOrder', 'itemTracking', 'financials'));
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

        if ($productOrder->status !== 'draft') {
            return redirect()->route('branch.product-orders.show', $productOrder)
                ->with('error', 'Only draft orders can be edited.');
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

        if ($productOrder->status !== 'draft') {
            return redirect()->route('branch.product-orders.show', $productOrder)
                ->with('error', 'Only draft orders can be updated.');
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
            // Calculate subtotal before updating
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $estimatedPrice = $product->purchase_price ?? 0;
                $totalPrice = $item['quantity'] * $estimatedPrice;
                $subtotal += $totalPrice;
            }

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

            // Update product order
            $productOrder->update([
                'vendor_id' => $systemVendor->id, // Ensure system vendor is used
                'notes' => $request->notes,
                'expected_delivery_date' => $request->expected_delivery_date,
                'priority' => $request->priority,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total_amount' => $subtotal,
            ]);

            // Delete existing items
            $productOrder->purchaseOrderItems()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $estimatedPrice = $product->purchase_price ?? 0;
                $totalPrice = $item['quantity'] * $estimatedPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $productOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $estimatedPrice,
                    'total_price' => $totalPrice,
                    'notes' => $item['reason'],
                ]);
            }
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

        if ($productOrder->status !== 'draft') {
            return redirect()->route('branch.product-orders.index')
                ->with('error', 'Only draft orders can be deleted.');
        }

        $productOrder->delete();

        return redirect()->route('branch.product-orders.index')
            ->with('success', 'Product order deleted successfully.');
    }
}