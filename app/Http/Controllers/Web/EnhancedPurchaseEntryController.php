<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * EnhancedPurchaseEntryController
 * 
 * Handles comprehensive purchase entry management with detailed quantity tracking.
 * Shows received quantities against purchase orders with remaining items tracking.
 */
class EnhancedPurchaseEntryController extends Controller
{
    /**
     * Display purchase entries with detailed quantity tracking.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        // Get purchase orders with their entries and detailed tracking
        $query = PurchaseOrder::with([
                'vendor', 
                'user', 
                'purchaseOrderItems.product',
                'purchaseEntries' => function($q) {
                    $q->orderBy('entry_date', 'desc');
                },
                'purchaseEntries.purchaseEntryItems.product'
            ])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->whereIn('status', ['sent', 'confirmed', 'fulfilled', 'received']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'pending') {
                $query->whereIn('receive_status', ['not_received', 'partial']);
            } elseif ($request->status === 'complete') {
                $query->where('receive_status', 'complete');
            } elseif ($request->status === 'partial') {
                $query->where('receive_status', 'partial');
            }
        }

        // Search by order number
        if ($request->has('search') && $request->search !== '') {
            $query->where('po_number', 'like', '%' . $request->search . '%');
        }

        $purchaseOrders = $query->latest('created_at')->paginate(15);

        // Calculate detailed statistics for each order
        $purchaseOrders->getCollection()->transform(function ($order) {
            $order->total_expected = $order->purchaseOrderItems->sum('quantity');
            $order->total_received = $order->purchaseEntries->sum('total_received_quantity');
            $order->total_remaining = $order->total_expected - $order->total_received;
            $order->total_spoiled = $order->purchaseEntries->sum('total_spoiled_quantity');
            $order->total_damaged = $order->purchaseEntries->sum('total_damaged_quantity');
            $order->total_usable = $order->purchaseEntries->sum('total_usable_quantity');
            $order->receipt_count = $order->purchaseEntries->count();
            $order->completion_percentage = $order->total_expected > 0 ? 
                ($order->total_received / $order->total_expected) * 100 : 0;
            
            return $order;
        });

        // Calculate overall statistics
        $stats = [
            'total_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->count(),
            'pending_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->whereIn('receive_status', ['not_received', 'partial'])
                ->count(),
            'complete_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('receive_status', 'complete')
                ->count(),
            'partial_orders' => PurchaseOrder::where('branch_id', $user->branch_id)
                ->where('order_type', 'branch_request')
                ->where('receive_status', 'partial')
                ->count(),
            'total_entries' => PurchaseEntry::where('branch_id', $user->branch_id)->count(),
            'this_month_entries' => PurchaseEntry::where('branch_id', $user->branch_id)
                ->whereMonth('entry_date', now()->month)
                ->count(),
        ];

        return view('branch.enhanced-purchase-entries.index', compact('purchaseOrders', 'stats'));
    }

    /**
     * Show detailed purchase order with all entries and quantity tracking.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || $purchaseOrder->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $purchaseOrder->load([
            'vendor', 
            'user', 
            'purchaseOrderItems.product',
            'purchaseEntries' => function($q) {
                $q->orderBy('entry_date', 'desc');
            },
            'purchaseEntries.purchaseEntryItems.product',
            'purchaseEntries.user'
        ]);

        // Calculate detailed tracking for each item
        $itemTracking = $purchaseOrder->purchaseOrderItems->map(function ($item) use ($purchaseOrder) {
            $totalReceived = $purchaseOrder->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('received_quantity');
            
            $totalSpoiled = $purchaseOrder->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('spoiled_quantity');
            
            $totalDamaged = $purchaseOrder->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('damaged_quantity');
            
            $totalUsable = $purchaseOrder->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('usable_quantity');
            
            $remaining = $item->quantity - $totalReceived;
            $completionPercentage = $item->quantity > 0 ? ($totalReceived / $item->quantity) * 100 : 0;
            
            return [
                'item' => $item,
                'expected_quantity' => $item->quantity,
                'received_quantity' => $totalReceived,
                'spoiled_quantity' => $totalSpoiled,
                'damaged_quantity' => $totalDamaged,
                'usable_quantity' => $totalUsable,
                'remaining_quantity' => $remaining,
                'completion_percentage' => $completionPercentage,
                'is_complete' => $remaining <= 0,
                'has_discrepancies' => $totalSpoiled > 0 || $totalDamaged > 0,
            ];
        });

        // Calculate overall order statistics
        $orderStats = [
            'total_expected' => $purchaseOrder->purchaseOrderItems->sum('quantity'),
            'total_received' => $purchaseOrder->purchaseEntries->sum('total_received_quantity'),
            'total_remaining' => $purchaseOrder->purchaseOrderItems->sum('quantity') - $purchaseOrder->purchaseEntries->sum('total_received_quantity'),
            'total_spoiled' => $purchaseOrder->purchaseEntries->sum('total_spoiled_quantity'),
            'total_damaged' => $purchaseOrder->purchaseEntries->sum('total_damaged_quantity'),
            'total_usable' => $purchaseOrder->purchaseEntries->sum('total_usable_quantity'),
            'completion_percentage' => $purchaseOrder->purchaseOrderItems->sum('quantity') > 0 ? 
                ($purchaseOrder->purchaseEntries->sum('total_received_quantity') / $purchaseOrder->purchaseOrderItems->sum('quantity')) * 100 : 0,
            'receipt_count' => $purchaseOrder->purchaseEntries->count(),
        ];

        return view('branch.enhanced-purchase-entries.show', compact('purchaseOrder', 'itemTracking', 'orderStats'));
    }

    /**
     * Show the form for creating a new purchase entry.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        // Get purchase orders that can receive materials
        $availablePurchaseOrders = PurchaseOrder::with(['vendor', 'purchaseOrderItems.product'])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->whereIn('status', ['sent', 'confirmed', 'fulfilled'])
            ->whereIn('receive_status', ['not_received', 'partial'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('branch.enhanced-purchase-entries.create', compact('availablePurchaseOrders'));
    }

    /**
     * Store a new purchase entry with detailed quantity tracking.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'entry_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'delivery_person' => 'nullable|string|max:255',
            'delivery_vehicle' => 'nullable|string|max:255',
            'delivery_notes' => 'nullable|string',
            'is_partial_receipt' => 'boolean',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.spoiled_quantity' => 'nullable|numeric|min:0',
            'items.*.damaged_quantity' => 'nullable|numeric|min:0',
            'items.*.actual_weight' => 'nullable|numeric|min:0',
            'items.*.expected_weight' => 'nullable|numeric|min:0',
            'items.*.quality_notes' => 'nullable|string',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
        
        if ($purchaseOrder->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        DB::transaction(function () use ($request, $purchaseOrder, $user) {
            // Create purchase entry
            $purchaseEntry = PurchaseEntry::create([
                'entry_number' => PurchaseEntry::generateEntryNumber(),
                'purchase_order_id' => $purchaseOrder->id,
                'vendor_id' => $purchaseOrder->vendor_id,
                'branch_id' => $purchaseOrder->branch_id,
                'user_id' => $user->id,
                'entry_date' => $request->entry_date,
                'delivery_date' => $request->delivery_date,
                'delivery_person' => $request->delivery_person,
                'delivery_vehicle' => $request->delivery_vehicle,
                'delivery_notes' => $request->delivery_notes,
                'is_partial_receipt' => $request->boolean('is_partial_receipt'),
                'entry_status' => 'received',
            ]);

            $totalExpected = 0;
            $totalReceived = 0;
            $totalSpoiled = 0;
            $totalDamaged = 0;
            $totalUsable = 0;
            $totalExpectedWeight = 0;
            $totalActualWeight = 0;

            // Process each item
            foreach ($request->items as $itemData) {
                $purchaseOrderItem = PurchaseOrderItem::findOrFail($itemData['item_id']);
                
                $receivedQuantity = $itemData['received_quantity'];
                $spoiledQuantity = $itemData['spoiled_quantity'] ?? 0;
                $damagedQuantity = $itemData['damaged_quantity'] ?? 0;
                $usableQuantity = $receivedQuantity - $spoiledQuantity - $damagedQuantity;
                
                $expectedWeight = $itemData['expected_weight'] ?? null;
                $actualWeight = $itemData['actual_weight'] ?? null;
                $weightDifference = 0;
                
                if ($actualWeight && $expectedWeight) {
                    $weightDifference = $actualWeight - $expectedWeight;
                }

                // Create purchase entry item
                $purchaseEntryItem = PurchaseEntryItem::create([
                    'purchase_entry_id' => $purchaseEntry->id,
                    'purchase_order_item_id' => $purchaseOrderItem->id,
                    'product_id' => $purchaseOrderItem->product_id,
                    'expected_quantity' => $purchaseOrderItem->quantity,
                    'received_quantity' => $receivedQuantity,
                    'spoiled_quantity' => $spoiledQuantity,
                    'damaged_quantity' => $damagedQuantity,
                    'usable_quantity' => $usableQuantity,
                    'expected_weight' => $expectedWeight,
                    'actual_weight' => $actualWeight,
                    'weight_difference' => $weightDifference,
                    'unit_price' => $purchaseOrderItem->unit_price,
                    'total_price' => $usableQuantity * $purchaseOrderItem->unit_price,
                    'quality_notes' => $itemData['quality_notes'] ?? null,
                ]);

                // Update totals
                $totalExpected += $purchaseOrderItem->quantity;
                $totalReceived += $receivedQuantity;
                $totalSpoiled += $spoiledQuantity;
                $totalDamaged += $damagedQuantity;
                $totalUsable += $usableQuantity;
                $totalExpectedWeight += $expectedWeight ?? 0;
                $totalActualWeight += $actualWeight ?? 0;

                // Record stock movements
                if ($usableQuantity > 0) {
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $purchaseOrder->branch_id,
                        'type' => 'purchase',
                        'quantity' => $usableQuantity,
                        'unit_price' => $purchaseOrderItem->unit_price,
                        'reference_type' => 'purchase_entry',
                        'reference_id' => $purchaseEntry->id,
                        'user_id' => $user->id,
                        'notes' => "Purchase Entry: {$purchaseEntry->entry_number}",
                    ]);

                    // Update product stock
                    $product = $purchaseOrderItem->product;
                    $currentStock = $product->getCurrentStock($purchaseOrder->branch_id);
                    $product->updateBranchStock($purchaseOrder->branch_id, $currentStock + $usableQuantity);
                }

                // Record spoiled quantity as loss
                if ($spoiledQuantity > 0) {
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $purchaseOrder->branch_id,
                        'type' => 'loss',
                        'quantity' => $spoiledQuantity,
                        'unit_price' => $purchaseOrderItem->unit_price,
                        'reference_type' => 'purchase_entry_spoilage',
                        'reference_id' => $purchaseEntry->id,
                        'user_id' => $user->id,
                        'notes' => "Purchase Entry Spoilage: {$purchaseEntry->entry_number}",
                    ]);
                }

                // Record damaged quantity as loss
                if ($damagedQuantity > 0) {
                    StockMovement::create([
                        'product_id' => $purchaseOrderItem->product_id,
                        'branch_id' => $purchaseOrder->branch_id,
                        'type' => 'loss',
                        'quantity' => $damagedQuantity,
                        'unit_price' => $purchaseOrderItem->unit_price,
                        'reference_type' => 'purchase_entry_damage',
                        'reference_id' => $purchaseEntry->id,
                        'user_id' => $user->id,
                        'notes' => "Purchase Entry Damage: {$purchaseEntry->entry_number}",
                    ]);
                }
            }

            // Update purchase entry totals
            $purchaseEntry->update([
                'total_expected_quantity' => $totalExpected,
                'total_received_quantity' => $totalReceived,
                'total_spoiled_quantity' => $totalSpoiled,
                'total_damaged_quantity' => $totalDamaged,
                'total_usable_quantity' => $totalUsable,
                'total_expected_weight' => $totalExpectedWeight,
                'total_actual_weight' => $totalActualWeight,
                'total_weight_difference' => $totalActualWeight - $totalExpectedWeight,
                'entry_status' => ($totalSpoiled > 0 || $totalDamaged > 0) ? 'discrepancy' : 'received',
            ]);

            // Update purchase order receive status
            $purchaseOrder->updateReceiveStatus();
        });

        return redirect()->route('enhanced-purchase-entries.show', $purchaseOrder)
            ->with('success', 'Purchase entry created successfully!');
    }

    /**
     * Show detailed purchase entry.
     */
    public function showEntry(PurchaseEntry $purchaseEntry)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || $purchaseEntry->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $purchaseEntry->load([
            'purchaseOrder.vendor',
            'purchaseOrder.purchaseOrderItems.product',
            'purchaseEntryItems.product',
            'user'
        ]);

        return view('branch.enhanced-purchase-entries.entry', compact('purchaseEntry'));
    }

    /**
     * Generate detailed purchase entry report.
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('branch_manager') || !$user->branch_id) {
            abort(403, 'Access denied. Branch managers only.');
        }

        $query = PurchaseEntry::with([
                'purchaseOrder.vendor',
                'purchaseEntryItems.product',
                'user'
            ])
            ->where('branch_id', $user->branch_id);

        // Date range filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }

        $entries = $query->latest('entry_date')->get();

        // Calculate overall statistics
        $overallStats = [
            'total_entries' => $entries->count(),
            'total_expected' => $entries->sum('total_expected_quantity'),
            'total_received' => $entries->sum('total_received_quantity'),
            'total_spoiled' => $entries->sum('total_spoiled_quantity'),
            'total_damaged' => $entries->sum('total_damaged_quantity'),
            'total_usable' => $entries->sum('total_usable_quantity'),
            'total_loss' => $entries->sum('total_spoiled_quantity') + $entries->sum('total_damaged_quantity'),
        ];

        $overallStats['loss_percentage'] = $overallStats['total_received'] > 0 ? 
            ($overallStats['total_loss'] / $overallStats['total_received']) * 100 : 0;

        return view('branch.enhanced-purchase-entries.report', compact('entries', 'overallStats'));
    }
}