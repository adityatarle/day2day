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
 * AdminBranchOrderController
 * 
 * Handles orders coming from branches to admin.
 * Admin can view all branch orders, assign vendors, and fulfill them.
 */
class AdminBranchOrderController extends Controller
{
    /**
     * Display orders from branches.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['branch', 'user', 'vendor'])
            ->where('order_type', 'branch_request')
            ->withCount('purchaseOrderItems');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->has('branch_id') && $request->branch_id !== '') {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order number
        if ($request->has('search') && $request->search !== '') {
            $query->where('po_number', 'like', '%' . $request->search . '%');
        }

        $branchOrders = $query->latest()->paginate(15);

        // Get filter options
        $branches = Branch::orderBy('name')->get();
        $statuses = ['draft', 'sent', 'confirmed', 'received', 'cancelled']; // 'sent' = approved, 'confirmed' = fulfilled
        $priorities = ['low', 'medium', 'high', 'urgent'];

        // Statistics
        $stats = [
            'total_requests' => PurchaseOrder::where('order_type', 'branch_request')->count(),
            'pending_requests' => PurchaseOrder::where('order_type', 'branch_request')->where('status', 'draft')->count(),
            'approved_requests' => PurchaseOrder::where('order_type', 'branch_request')->where('status', 'sent')->count(),
            'this_month_requests' => PurchaseOrder::where('order_type', 'branch_request')->whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.branch-orders.index', compact('branchOrders', 'branches', 'statuses', 'priorities', 'stats'));
    }

    /**
     * Show the specified branch order.
     */
    public function show(PurchaseOrder $branchOrder)
    {
        $branchOrder->load(['branch', 'user', 'vendor', 'purchaseOrderItems.product']);
        $vendors = Vendor::active()->orderBy('name')->get();

        return view('admin.branch-orders.show', compact('branchOrder', 'vendors'));
    }

    /**
     * Approve branch order (without vendor assignment).
     * Admin should purchase materials first, then fulfill the order.
     */
    public function approve(Request $request, PurchaseOrder $branchOrder)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $branchOrder) {
            $branchOrder->update([
                'status' => 'sent', // Using 'sent' instead of 'approved' to match current enum
                'notes' => trim(($branchOrder->notes ?? '') . (filled($request->admin_notes) ? ("\n\nAdmin Notes: " . $request->admin_notes) : '')),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('admin.branch-orders.show', $branchOrder)
            ->with('success', 'Branch order approved! You can now purchase materials from vendors and fulfill the order.');
    }

    /**
     * Create a vendor purchase order from a branch request.
     * This allows admin to purchase materials from vendors for the branch order.
     */
    public function createVendorPurchaseOrder(Request $request, PurchaseOrder $branchOrder)
    {
        if ($branchOrder->status !== 'sent') { // Using 'sent' instead of 'approved' to match current enum
            return redirect()->back()->with('error', 'Only approved branch orders can be used to create vendor purchase orders.');
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'expected_delivery_date' => 'required|date|after:today',
            'payment_terms' => 'required|in:immediate,7_days,15_days,30_days',
            'admin_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $branchOrder) {
            // Create a new purchase order for the vendor
            $vendorPO = PurchaseOrder::create([
                'po_number' => 'PO-' . date('Y') . '-' . str_pad(PurchaseOrder::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'vendor_id' => $request->vendor_id,
                'branch_id' => $branchOrder->branch_id, // Admin's main branch
                'branch_request_id' => $branchOrder->id, // Link to the original branch request
                'user_id' => Auth::id(),
                'status' => 'draft',
                'order_type' => 'purchase_order',
                'delivery_address_type' => 'admin_main', // Deliver to admin first
                'payment_terms' => $request->payment_terms,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => "Created from branch request: {$branchOrder->po_number}\n" . ($request->admin_notes ?? ''),
                'subtotal' => 0,
                'tax_amount' => 0,
                'transport_cost' => 0,
                'total_amount' => 0,
            ]);

            // Copy items from branch order to vendor purchase order
            foreach ($branchOrder->purchaseOrderItems as $branchItem) {
                $vendorProduct = DB::table('product_vendors')
                    ->where('product_id', $branchItem->product_id)
                    ->where('vendor_id', $request->vendor_id)
                    ->first();

                $unitPrice = $vendorProduct ? $vendorProduct->supply_price : $branchItem->unit_price;
                $totalPrice = $branchItem->quantity * $unitPrice;

                $vendorPO->purchaseOrderItems()->create([
                    'product_id' => $branchItem->product_id,
                    'quantity' => $branchItem->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'notes' => "From branch request: {$branchOrder->po_number}",
                ]);
            }

            // Update totals
            $vendorPO->updateTotals();
        });

        return redirect()->route('purchase-orders.show', $vendorPO)
            ->with('success', 'Vendor purchase order created successfully! You can now send it to the vendor.');
    }

    /**
     * Mark branch order as fulfilled and update inventory.
     */
    public function fulfill(Request $request, PurchaseOrder $branchOrder)
    {
        if ($branchOrder->status !== 'sent') { // Using 'sent' instead of 'approved' to match current enum
            return redirect()->back()->with('error', 'Only approved orders can be fulfilled.');
        }

        $request->validate([
            'fulfilled_items' => 'required|array',
            'fulfilled_items.*.item_id' => 'required|exists:purchase_order_items,id',
            'fulfilled_items.*.fulfilled_quantity' => 'required|numeric|min:0',
            'fulfilled_items.*.weight_difference' => 'nullable|numeric',
            'fulfilled_items.*.spoiled_quantity' => 'nullable|numeric|min:0',
            'fulfilled_items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $branchOrder) {
            $branchOrder->update([
                'status' => 'confirmed', // Using 'confirmed' instead of 'fulfilled' to match current enum
                'fulfilled_by' => Auth::id(),
                'fulfilled_at' => now(),
            ]);

            // Process each fulfilled item
            foreach ($request->fulfilled_items as $fulfilledItem) {
                $purchaseOrderItem = PurchaseOrderItem::find($fulfilledItem['item_id']);
                $fulfilledQuantity = $fulfilledItem['fulfilled_quantity'];
                $weightDifference = $fulfilledItem['weight_difference'] ?? 0;
                $spoiledQuantity = $fulfilledItem['spoiled_quantity'] ?? 0;

                // Update the item with fulfillment details
                $purchaseOrderItem->update([
                    'fulfilled_quantity' => $fulfilledQuantity,
                    'weight_difference' => $weightDifference,
                    'spoiled_quantity' => $spoiledQuantity,
                    'fulfillment_notes' => $fulfilledItem['notes'] ?? '',
                ]);

                // Add stock to branch inventory (only the good quantity)
                $goodQuantity = $fulfilledQuantity - $spoiledQuantity;
                if ($goodQuantity > 0) {
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $branchOrder->branch_id,
                        'type' => 'purchase',
                        'quantity' => $goodQuantity,
                        'unit_price' => $purchaseOrderItem->unit_price,
                        'reference_type' => 'branch_order',
                        'reference_id' => $branchOrder->id,
                        'user_id' => Auth::id(),
                        'notes' => "Branch Order Fulfillment: {$branchOrder->po_number}",
                    ]);

                    // Update the product's current stock in the branch
                    $product = $purchaseOrderItem->product;
                    $currentStock = $product->getCurrentStock($branchOrder->branch_id);
                    $product->updateBranchStock($branchOrder->branch_id, $currentStock + $goodQuantity);
                }

                // Record spoiled quantity if any
                if ($spoiledQuantity > 0) {
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $branchOrder->branch_id,
                        'type' => 'loss',
                        'quantity' => $spoiledQuantity,
                        'unit_price' => $purchaseOrderItem->unit_price,
                        'reference_type' => 'branch_order_spoilage',
                        'reference_id' => $branchOrder->id,
                        'user_id' => Auth::id(),
                        'notes' => "Spoiled materials from Branch Order: {$branchOrder->po_number}",
                    ]);
                }
            }
        });

        return redirect()->route('admin.branch-orders.show', $branchOrder)
            ->with('success', 'Branch order fulfilled successfully! Inventory updated.');
    }

    /**
     * Show fulfill form for branch order.
     */
    public function showFulfillForm(PurchaseOrder $branchOrder)
    {
        if ($branchOrder->status !== 'sent') { // Using 'sent' instead of 'approved' to match current enum
            return redirect()->route('admin.branch-orders.show', $branchOrder)
                ->with('error', 'Only approved orders can be fulfilled.');
        }

        $branchOrder->load(['branch', 'vendor', 'purchaseOrderItems.product']);

        return view('admin.branch-orders.fulfill', compact('branchOrder'));
    }

    /**
     * Cancel branch order.
     */
    public function cancel(Request $request, PurchaseOrder $branchOrder)
    {
        $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $branchOrder->update([
            'status' => 'cancelled',
            'notes' => $branchOrder->notes . "\n\nCancelled by Admin: " . $request->cancellation_reason,
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return redirect()->route('admin.branch-orders.index')
            ->with('success', 'Branch order cancelled successfully.');
    }
}