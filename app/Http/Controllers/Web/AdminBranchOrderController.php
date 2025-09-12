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
        $statuses = ['pending', 'approved', 'processing', 'fulfilled', 'cancelled'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        // Statistics
        $stats = [
            'total_requests' => PurchaseOrder::where('order_type', 'branch_request')->count(),
            'pending_requests' => PurchaseOrder::where('order_type', 'branch_request')->where('status', 'pending')->count(),
            'approved_requests' => PurchaseOrder::where('order_type', 'branch_request')->where('status', 'approved')->count(),
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
     * Approve branch order and assign vendor.
     */
    public function approve(Request $request, PurchaseOrder $branchOrder)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'admin_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $branchOrder) {
            $branchOrder->update([
                'status' => 'approved',
                'vendor_id' => $request->vendor_id,
                'notes' => $branchOrder->notes . "\n\nAdmin Notes: " . $request->admin_notes,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Update items with vendor pricing if available
            foreach ($branchOrder->purchaseOrderItems as $item) {
                $vendorProduct = DB::table('product_vendors')
                    ->where('product_id', $item->product_id)
                    ->where('vendor_id', $request->vendor_id)
                    ->first();

                if ($vendorProduct) {
                    $item->update([
                        'unit_price' => $vendorProduct->supply_price,
                        'total_price' => $item->quantity * $vendorProduct->supply_price,
                    ]);
                }
            }

            // Recalculate totals
            $branchOrder->updateTotals();
        });

        return redirect()->route('admin.branch-orders.show', $branchOrder)
            ->with('success', 'Branch order approved and vendor assigned successfully!');
    }

    /**
     * Mark branch order as fulfilled and update inventory.
     */
    public function fulfill(Request $request, PurchaseOrder $branchOrder)
    {
        if ($branchOrder->status !== 'approved') {
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
                'status' => 'fulfilled',
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
                        'reference_type' => 'branch_order',
                        'reference_id' => $branchOrder->id,
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
                        'reference_type' => 'branch_order_spoilage',
                        'reference_id' => $branchOrder->id,
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
        if ($branchOrder->status !== 'approved') {
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