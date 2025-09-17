<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * BranchPurchaseEntryController
 * 
 * Handles purchase entries when branch receives materials from admin.
 * Branch managers can record delivery receipts, weight differences, and spoilage.
 */
class BranchPurchaseEntryController extends Controller
{
    /**
     * Show the form for creating a new purchase entry.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        // Get purchase orders that are approved/fulfilled and can receive materials
        // Include both not received and partially received orders
        $availablePurchaseOrders = PurchaseOrder::with(['vendor', 'purchaseOrderItems.product'])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->whereIn('status', ['sent', 'confirmed', 'fulfilled'])
            ->whereIn('receive_status', ['not_received', 'partial'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('branch.purchase-entries.create', compact('availablePurchaseOrders'));
    }

    /**
     * Display purchase entries for the branch.
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
            ->whereIn('status', ['sent', 'confirmed', 'fulfilled', 'received'])
            ->withCount('purchaseOrderItems');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->has('search') && $request->search !== '') {
            $query->where('po_number', 'like', '%' . $request->search . '%');
        }

        $purchaseEntries = $query->latest()->paginate(15);

        $stats = [
            'approved_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->whereIn('status', ['sent', 'confirmed', 'fulfilled'])
                ->whereIn('receive_status', ['not_received', 'partial'])
                ->count(),
            'fulfilled_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('status', 'received')
                ->where('receive_status', 'complete')
                ->count(),
            'partial_receipts' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('receive_status', 'partial')
                ->count(),
            'this_month_receipts' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->whereNotNull('received_at')
                ->whereMonth('received_at', now()->month)->count(),
        ];

        return view('branch.purchase-entries.index', compact('purchaseEntries', 'stats'));
    }

    /**
     * Display the specified purchase entry.
     */
    public function show(PurchaseOrder $purchaseEntry)
    {
        $user = Auth::user();
        
        // Verify access
        if (!$user->hasRole('branch_manager') || $purchaseEntry->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $purchaseEntry->load(['branch', 'user', 'vendor', 'purchaseOrderItems.product']);

        return view('branch.purchase-entries.show', compact('purchaseEntry'));
    }

    /**
     * Show the form for recording delivery receipt.
     */
    public function createReceipt(PurchaseOrder $purchaseEntry)
    {
        $user = Auth::user();
        
        // Verify access and status
        if (!$user->hasRole('branch_manager') || $purchaseEntry->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if (!in_array($purchaseEntry->status, ['sent', 'confirmed', 'fulfilled'])) {
            return redirect()->route('branch.purchase-entries.show', $purchaseEntry)
                ->with('error', 'Only approved/fulfilled orders can have delivery receipts recorded.');
        }

        if ($purchaseEntry->receive_status === 'complete') {
            return redirect()->route('branch.purchase-entries.show', $purchaseEntry)
                ->with('error', 'This order has been completely received. No further receipts can be recorded.');
        }

        $purchaseEntry->load(['branch', 'vendor', 'purchaseOrderItems.product']);

        return view('branch.purchase-entries.create-receipt', compact('purchaseEntry'));
    }

    /**
     * Store delivery receipt with discrepancies.
     */
    public function storeReceipt(Request $request, PurchaseOrder $purchaseEntry)
    {
        $user = Auth::user();
        
        // Verify access and status
        if (!$user->hasRole('branch_manager') || $purchaseEntry->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if (!in_array($purchaseEntry->status, ['sent', 'confirmed', 'fulfilled'])) {
            return redirect()->route('branch.purchase-entries.show', $purchaseEntry)
                ->with('error', 'Only approved/fulfilled orders can have delivery receipts recorded.');
        }

        $request->validate([
            'received_items' => 'required|array',
            'received_items.*.item_id' => 'required|exists:purchase_order_items,id',
            'received_items.*.actual_received_quantity' => 'required|numeric|min:0',
            'received_items.*.actual_weight' => 'nullable|numeric|min:0',
            'received_items.*.expected_weight' => 'nullable|numeric|min:0',
            'received_items.*.spoiled_quantity' => 'nullable|numeric|min:0',
            'received_items.*.damaged_quantity' => 'nullable|numeric|min:0',
            'received_items.*.quality_notes' => 'nullable|string',
            'delivery_notes' => 'nullable|string',
            'delivery_person' => 'nullable|string',
            'delivery_vehicle' => 'nullable|string',
            'receipt_type' => 'required|in:partial,complete',
        ]);

        DB::transaction(function () use ($request, $purchaseEntry, $user) {
            $receiptType = $request->receipt_type;
            $isPartialReceipt = $receiptType === 'partial';
            
            // Update purchase entry with receipt information
            $updateData = [
                'delivery_notes' => $request->delivery_notes,
                'delivery_person' => $request->delivery_person,
                'delivery_vehicle' => $request->delivery_vehicle,
            ];
            
            // Only set received_at and received_by for the first receipt
            if (!$purchaseEntry->received_at) {
                $updateData['received_at'] = now();
                $updateData['received_by'] = $user->id;
            }
            
            $purchaseEntry->update($updateData);

            // Process each received item
            foreach ($request->received_items as $receivedItem) {
                $purchaseOrderItem = PurchaseOrderItem::find($receivedItem['item_id']);
                $thisReceiptQuantity = $receivedItem['actual_received_quantity'];
                $spoiledQuantity = $receivedItem['spoiled_quantity'] ?? 0;
                $damagedQuantity = $receivedItem['damaged_quantity'] ?? 0;
                $actualWeight = $receivedItem['actual_weight'] ?? null;
                $expectedWeight = $receivedItem['expected_weight'] ?? null;

                // Calculate weight difference
                $weightDifference = 0;
                if ($actualWeight && $expectedWeight) {
                    $weightDifference = $actualWeight - $expectedWeight;
                }

                // Calculate usable quantity (excluding spoiled and damaged)
                $usableQuantity = $thisReceiptQuantity - $spoiledQuantity - $damagedQuantity;

                // Get current received quantities
                $currentReceivedQuantity = $purchaseOrderItem->actual_received_quantity ?? 0;
                $currentSpoiledQuantity = $purchaseOrderItem->spoiled_quantity ?? 0;
                $currentDamagedQuantity = $purchaseOrderItem->damaged_quantity ?? 0;
                $currentUsableQuantity = $purchaseOrderItem->usable_quantity ?? 0;

                // Update the item with cumulative receipt details
                $purchaseOrderItem->update([
                    'actual_received_quantity' => $currentReceivedQuantity + $thisReceiptQuantity,
                    'actual_weight' => $actualWeight ?: $purchaseOrderItem->actual_weight,
                    'expected_weight' => $expectedWeight ?: $purchaseOrderItem->expected_weight,
                    'weight_difference' => $weightDifference ?: $purchaseOrderItem->weight_difference,
                    'spoiled_quantity' => $currentSpoiledQuantity + $spoiledQuantity,
                    'damaged_quantity' => $currentDamagedQuantity + $damagedQuantity,
                    'usable_quantity' => $currentUsableQuantity + $usableQuantity,
                    'quality_notes' => $receivedItem['quality_notes'] ?: $purchaseOrderItem->quality_notes,
                ]);

                // Record stock movements for different quantities
                
                // 1. Record usable stock addition
                if ($usableQuantity > 0) {
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $purchaseEntry->branch_id,
                        'type' => 'purchase',
                        'quantity' => $usableQuantity,
                        'reference_type' => 'delivery_receipt',
                        'reference_id' => $purchaseEntry->id,
                        'notes' => "Material Receipt - Usable: {$purchaseEntry->po_number} ({$receiptType})",
                    ]);

                    // Update the product's current stock in the branch
                    $product = $purchaseOrderItem->product;
                    $currentStock = $product->getCurrentStock($purchaseEntry->branch_id);
                    $product->updateBranchStock($purchaseEntry->branch_id, $currentStock + $usableQuantity);
                }

                // 2. Record spoiled quantity as loss
                if ($spoiledQuantity > 0) {
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $purchaseEntry->branch_id,
                        'type' => 'loss',
                        'quantity' => $spoiledQuantity,
                        'reference_type' => 'delivery_spoilage',
                        'reference_id' => $purchaseEntry->id,
                        'notes' => "Material Receipt - Spoiled: {$purchaseEntry->po_number} ({$receiptType})",
                    ]);
                }

                // 3. Record damaged quantity as loss
                if ($damagedQuantity > 0) {
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $purchaseEntry->branch_id,
                        'type' => 'loss',
                        'quantity' => $damagedQuantity,
                        'reference_type' => 'delivery_damage',
                        'reference_id' => $purchaseEntry->id,
                        'notes' => "Material Receipt - Damaged: {$purchaseEntry->po_number} ({$receiptType})",
                    ]);
                }

                // 4. Record weight difference if significant (for reporting)
                if (abs($weightDifference) > 0.1) { // Only record if difference > 0.1kg
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $purchaseEntry->branch_id,
                        'type' => $weightDifference > 0 ? 'adjustment_positive' : 'adjustment_negative',
                        'quantity' => abs($weightDifference),
                        'reference_type' => 'weight_difference',
                        'reference_id' => $purchaseEntry->id,
                        'notes' => "Weight Difference: Expected {$expectedWeight}kg, Actual {$actualWeight}kg ({$receiptType})",
                    ]);
                }
            }
            
            // Update purchase order receive status
            $purchaseEntry->updateReceiveStatus();
            
            // Update purchase order status if complete receipt
            if (!$isPartialReceipt) {
                $purchaseEntry->update(['status' => 'received']);
            }
        });

        $receiptType = $request->receipt_type;
        $message = $receiptType === 'partial' 
            ? 'Partial material receipt recorded successfully! You can continue receiving remaining materials later.'
            : 'Complete material receipt recorded successfully! All materials have been received and inventory updated.';
            
        return redirect()->route('branch.purchase-entries.show', $purchaseEntry)
            ->with('success', $message);
    }

    /**
     * Show receipt details.
     */
    public function showReceipt(PurchaseOrder $purchaseEntry)
    {
        $user = Auth::user();
        
        // Verify access
        if (!$user->hasRole('branch_manager') || $purchaseEntry->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        if (!$purchaseEntry->received_at) {
            return redirect()->route('branch.purchase-entries.show', $purchaseEntry)
                ->with('error', 'No delivery receipt found for this order.');
        }

        $purchaseEntry->load(['branch', 'user', 'vendor', 'purchaseOrderItems.product']);

        // Calculate totals for discrepancies
        $totalExpectedQuantity = $purchaseEntry->purchaseOrderItems->sum('fulfilled_quantity');
        $totalReceivedQuantity = $purchaseEntry->purchaseOrderItems->sum('actual_received_quantity');
        $totalSpoiledQuantity = $purchaseEntry->purchaseOrderItems->sum('spoiled_quantity');
        $totalDamagedQuantity = $purchaseEntry->purchaseOrderItems->sum('damaged_quantity');
        $totalUsableQuantity = $purchaseEntry->purchaseOrderItems->sum('usable_quantity');

        $discrepancySummary = [
            'expected' => $totalExpectedQuantity,
            'received' => $totalReceivedQuantity,
            'spoiled' => $totalSpoiledQuantity,
            'damaged' => $totalDamagedQuantity,
            'usable' => $totalUsableQuantity,
            'loss_percentage' => $totalReceivedQuantity > 0 ? (($totalSpoiledQuantity + $totalDamagedQuantity) / $totalReceivedQuantity) * 100 : 0,
        ];

        return view('branch.purchase-entries.receipt', compact('purchaseEntry', 'discrepancySummary'));
    }

    /**
     * Generate discrepancy report.
     */
    public function discrepancyReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        $query = PurchaseOrder::with(['vendor', 'purchaseOrderItems.product'])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->whereNotNull('received_at');

        // Date range filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('received_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('received_at', '<=', $request->date_to);
        }

        $entries = $query->latest('received_at')->get();

        // Calculate overall discrepancy statistics
        $overallStats = [
            'total_orders' => $entries->count(),
            'total_spoiled' => $entries->sum(function($entry) {
                return $entry->purchaseOrderItems->sum('spoiled_quantity');
            }),
            'total_damaged' => $entries->sum(function($entry) {
                return $entry->purchaseOrderItems->sum('damaged_quantity');
            }),
            'total_received' => $entries->sum(function($entry) {
                return $entry->purchaseOrderItems->sum('actual_received_quantity');
            }),
        ];

        $overallStats['total_loss'] = $overallStats['total_spoiled'] + $overallStats['total_damaged'];
        $overallStats['loss_percentage'] = $overallStats['total_received'] > 0 ? 
            ($overallStats['total_loss'] / $overallStats['total_received']) * 100 : 0;

        return view('branch.purchase-entries.discrepancy-report', compact('entries', 'overallStats'));
    }

    /**
     * Debug page to show all purchase orders and their statuses.
     */
    public function debug(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        // Get all purchase orders for this branch
        $allPurchaseOrders = PurchaseOrder::with(['vendor', 'purchaseOrderItems'])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available orders (same query as create method)
        $availableOrders = PurchaseOrder::with(['vendor', 'purchaseOrderItems'])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->whereIn('status', ['sent', 'confirmed'])
            ->whereNull('received_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('branch.purchase-entries.debug', compact('allPurchaseOrders', 'availableOrders'));
    }
}