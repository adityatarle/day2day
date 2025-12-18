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
        $isBranchManager = $user->hasRole('branch_manager') && $user->branch_id;
        $isAdminOrSuperAdmin = $user->hasRole('admin') || $user->hasRole('super_admin');

        // Branch managers (with a branch) can see their data; admins/super admins have read-only access
        if (! $isBranchManager && ! $isAdminOrSuperAdmin) {
            abort(403, 'Access denied.');
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
            ]);

        if ($isBranchManager) {
            $query->where('branch_id', $user->branch_id);
        }

        $query->where('order_type', 'branch_request')
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

        // Calculate detailed statistics for each order using normalized aggregates
        $purchaseOrders->getCollection()->transform(function ($order) {
            $order->recalculateReceiptAggregates();
            $order->total_expected = $order->purchaseOrderItems->sum('quantity');
            $order->total_received = (float) $order->purchaseOrderItems->sum(function ($item) {
                $direct = (float) ($item->received_quantity ?? 0);
                $fromEntries = (float) ($item->actual_received_quantity ?? 0);
                return max($direct, $fromEntries);
            });
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
        // Stats: branch-scoped for managers, global for admins/super admins
        $poStats = PurchaseOrder::query()->where('order_type', 'branch_request');
        $peStats = PurchaseEntry::query();

        if ($isBranchManager) {
            $poStats->where('branch_id', $user->branch_id);
            $peStats->where('branch_id', $user->branch_id);
        }

        $stats = [
            'total_orders'        => (clone $poStats)->count(),
            'pending_orders'      => (clone $poStats)->whereIn('receive_status', ['not_received', 'partial'])->count(),
            'complete_orders'     => (clone $poStats)->where('receive_status', 'complete')->count(),
            'partial_orders'      => (clone $poStats)->where('receive_status', 'partial')->count(),
            'total_entries'       => (clone $peStats)->count(),
            'this_month_entries'  => (clone $peStats)->whereMonth('entry_date', now()->month)->count(),
        ];

        return view('branch.enhanced-purchase-entries.index', compact('purchaseOrders', 'stats'));
    }

    /**
     * Show detailed purchase order with all entries and quantity tracking.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $user = Auth::user();
        $isBranchManager = $user->hasRole('branch_manager');
        $isAdminOrSuperAdmin = $user->hasRole('admin') || $user->hasRole('super_admin');

        // Branch managers must be restricted to their branch; admins/super admins can view any order
        if ($isBranchManager) {
            if (! $user->branch_id || $purchaseOrder->branch_id !== $user->branch_id) {
                abort(403, 'Access denied.');
            }
        } elseif (! $isAdminOrSuperAdmin) {
            abort(403, 'Access denied.');
        }

        // Ensure aggregates are up-to-date before display
        $purchaseOrder->recalculateReceiptAggregates();

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
            $receivedFromEntries = $purchaseOrder->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('received_quantity');
            $direct = (float) ($item->received_quantity ?? 0);
            $totalReceived = max($direct, (float) $receivedFromEntries);
            
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
            'total_received' => (float) $purchaseOrder->purchaseOrderItems->sum(function ($item) {
                $direct = (float) ($item->received_quantity ?? 0);
                $fromEntries = (float) ($item->actual_received_quantity ?? 0);
                return max($direct, $fromEntries);
            }),
            'total_remaining' => $purchaseOrder->purchaseOrderItems->sum('quantity') - (float) $purchaseOrder->purchaseOrderItems->sum(function ($item) {
                $direct = (float) ($item->received_quantity ?? 0);
                $fromEntries = (float) ($item->actual_received_quantity ?? 0);
                return max($direct, $fromEntries);
            }),
            'total_spoiled' => $purchaseOrder->purchaseEntries->sum('total_spoiled_quantity'),
            'total_damaged' => $purchaseOrder->purchaseEntries->sum('total_damaged_quantity'),
            'total_usable' => $purchaseOrder->purchaseEntries->sum('total_usable_quantity'),
            'completion_percentage' => $purchaseOrder->purchaseOrderItems->sum('quantity') > 0 ? 
                (((float) $purchaseOrder->purchaseOrderItems->sum(function ($item) {
                    $direct = (float) ($item->received_quantity ?? 0);
                    $fromEntries = (float) ($item->actual_received_quantity ?? 0);
                    return max($direct, $fromEntries);
                })) / $purchaseOrder->purchaseOrderItems->sum('quantity')) * 100 : 0,
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

        // Pre-validate: ensure no item exceeds ordered quantity when combined with previous entries
        foreach ($request->items as $itemData) {
            $purchaseOrderItem = PurchaseOrderItem::findOrFail($itemData['item_id']);
            if ($purchaseOrderItem->purchase_order_id !== $purchaseOrder->id) {
                abort(422, 'Invalid item reference.');
            }

            $receivedQuantity = (float) $itemData['received_quantity'];
            $spoiledQuantity = (float) ($itemData['spoiled_quantity'] ?? 0);
            $damagedQuantity = (float) ($itemData['damaged_quantity'] ?? 0);

            if ($spoiledQuantity + $damagedQuantity > $receivedQuantity) {
                return back()->withInput()->withErrors(['items' => 'Spoiled + Damaged cannot exceed Received quantity for product ID ' . $purchaseOrderItem->product_id]);
            }

            $previouslyReceived = (float) PurchaseEntryItem::where('purchase_order_item_id', $purchaseOrderItem->id)
                ->sum('received_quantity');
            $ordered = (float) $purchaseOrderItem->quantity;
            if ($previouslyReceived + $receivedQuantity > $ordered + 0.00001) {
                return back()->withInput()->withErrors(['items' => 'Total received cannot exceed ordered quantity for product ' . ($purchaseOrderItem->product->name ?? ('#' . $purchaseOrderItem->product_id))]);
            }
        }

        DB::transaction(function () use ($request, $purchaseOrder, $user) {
            // Load purchase order items to check quantities
            $purchaseOrder->load('purchaseOrderItems');
            
            // Calculate if this is a partial receipt by checking all items
            $isPartialReceipt = false;
            $allItemsData = [];
            
            // First pass: calculate total received across all entries including this one
            foreach ($purchaseOrder->purchaseOrderItems as $orderItem) {
                $previouslyReceived = (float) PurchaseEntryItem::where('purchase_order_item_id', $orderItem->id)
                    ->sum('received_quantity');
                    
                $currentReceiving = 0;
                foreach ($request->items as $itemData) {
                    if ($itemData['item_id'] == $orderItem->id) {
                        $currentReceiving = (float) $itemData['received_quantity'];
                        break;
                    }
                }
                
                $totalReceived = $previouslyReceived + $currentReceiving;
                $ordered = (float) $orderItem->quantity;
                
                // If any item has less than ordered quantity, it's partial
                if ($totalReceived < $ordered - 0.00001) {
                    $isPartialReceipt = true;
                }
                
                $allItemsData[$orderItem->id] = [
                    'ordered' => $ordered,
                    'previously_received' => $previouslyReceived,
                    'current_receiving' => $currentReceiving,
                    'total_received' => $totalReceived
                ];
            }
            
            // Create purchase entry with automatically determined partial status
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
                'is_partial_receipt' => $isPartialReceipt,
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

            // Update purchase order receive status and aggregates
            $purchaseOrder->recalculateReceiptAggregates();
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
        $isBranchManager = $user->hasRole('branch_manager');
        $isAdminOrSuperAdmin = $user->hasRole('admin') || $user->hasRole('super_admin');

        // Branch managers must only see their own branch; admins / super admins can view any entry
        if ($isBranchManager) {
            if (! $user->branch_id || $purchaseEntry->branch_id !== $user->branch_id) {
                abort(403, 'Access denied.');
            }
        } elseif (! $isAdminOrSuperAdmin) {
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
        $isBranchManager = $user->hasRole('branch_manager') && $user->branch_id;
        $isAdminOrSuperAdmin = $user->hasRole('admin') || $user->hasRole('super_admin');

        if (! $isBranchManager && ! $isAdminOrSuperAdmin) {
            abort(403, 'Access denied.');
        }

        $query = PurchaseEntry::with([
                'purchaseOrder.vendor',
                'purchaseEntryItems.product',
                'user'
            ])
            ->when($isBranchManager, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });

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

    /**
     * Edit a purchase entry (adjust quantities with validation).
     */
    public function editEntry(PurchaseEntry $purchaseEntry)
    {
        $user = Auth::user();
        if (!$user->hasRole('branch_manager') || $purchaseEntry->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $purchaseEntry->load(['purchaseOrder.purchaseOrderItems', 'purchaseEntryItems.product']);

        // Compute per-item maximum allowed received considering other entries
        $limits = [];
        foreach ($purchaseEntry->purchaseEntryItems as $entryItem) {
            $poItemId = $entryItem->purchase_order_item_id;
            $poItem = $purchaseEntry->purchaseOrder->purchaseOrderItems->firstWhere('id', $poItemId);
            $otherReceived = (float) PurchaseEntryItem::where('purchase_order_item_id', $poItemId)
                ->where('purchase_entry_id', '!=', $purchaseEntry->id)
                ->sum('received_quantity');
            $limits[$entryItem->id] = [
                'max_received' => max(0, (float) $poItem->quantity - $otherReceived) + (float) $entryItem->received_quantity,
            ];
        }

        return view('branch.enhanced-purchase-entries.edit-entry', compact('purchaseEntry', 'limits'));
    }

    /**
     * Update a purchase entry after editing quantities.
     */
    public function updateEntry(Request $request, PurchaseEntry $purchaseEntry)
    {
        $user = Auth::user();
        if (!$user->hasRole('branch_manager') || $purchaseEntry->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_entry_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.spoiled_quantity' => 'nullable|numeric|min:0',
            'items.*.damaged_quantity' => 'nullable|numeric|min:0',
            'items.*.quality_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $purchaseEntry, $user) {
            $purchaseEntry->load(['purchaseOrder.purchaseOrderItems', 'purchaseEntryItems']);

            // Validate against ordered quantities
            foreach ($request->items as $data) {
                $entryItem = $purchaseEntry->purchaseEntryItems->firstWhere('id', $data['id']);
                if (!$entryItem) {
                    abort(422, 'Invalid entry item.');
                }
                $poItemId = $entryItem->purchase_order_item_id;
                $poItem = $purchaseEntry->purchaseOrder->purchaseOrderItems->firstWhere('id', $poItemId);
                $newReceived = (float) $data['received_quantity'];
                $spoiled = (float) ($data['spoiled_quantity'] ?? 0);
                $damaged = (float) ($data['damaged_quantity'] ?? 0);
                if ($spoiled + $damaged > $newReceived) {
                    abort(422, 'Spoiled + Damaged cannot exceed Received for product ' . ($entryItem->product->name ?? '#'.$entryItem->product_id));
                }
                $otherReceived = (float) PurchaseEntryItem::where('purchase_order_item_id', $poItemId)
                    ->where('purchase_entry_id', '!=', $purchaseEntry->id)
                    ->sum('received_quantity');
                if ($otherReceived + $newReceived > (float) $poItem->quantity + 0.00001) {
                    abort(422, 'Total received cannot exceed ordered for product ' . ($entryItem->product->name ?? '#'.$entryItem->product_id));
                }
            }

            // Remove previous stock movements for this entry to re-create cleanly
            StockMovement::where('reference_type', 'purchase_entry')
                ->where('reference_id', $purchaseEntry->id)
                ->delete();
            StockMovement::where('reference_type', 'purchase_entry_spoilage')
                ->where('reference_id', $purchaseEntry->id)
                ->delete();
            StockMovement::where('reference_type', 'purchase_entry_damage')
                ->where('reference_id', $purchaseEntry->id)
                ->delete();

            $totalExpected = 0;
            $totalReceived = 0;
            $totalSpoiled = 0;
            $totalDamaged = 0;
            $totalUsable = 0;
            $totalExpectedWeight = 0;
            $totalActualWeight = 0;

            foreach ($request->items as $data) {
                $entryItem = $purchaseEntry->purchaseEntryItems->firstWhere('id', $data['id']);
                $poItem = $purchaseEntry->purchaseOrder->purchaseOrderItems->firstWhere('id', $entryItem->purchase_order_item_id);

                $newReceived = (float) $data['received_quantity'];
                $spoiled = (float) ($data['spoiled_quantity'] ?? 0);
                $damaged = (float) ($data['damaged_quantity'] ?? 0);
                $usable = $newReceived - $spoiled - $damaged;
                $oldUsable = (float) $entryItem->usable_quantity;

                $entryItem->update([
                    'received_quantity' => $newReceived,
                    'spoiled_quantity' => $spoiled,
                    'damaged_quantity' => $damaged,
                    'usable_quantity' => $usable,
                    'quality_notes' => $data['quality_notes'] ?? $entryItem->quality_notes,
                    'total_price' => $usable * (float) $entryItem->unit_price,
                ]);

                // Adjust product branch stock based on delta usable
                $deltaUsable = $usable - $oldUsable;
                if (abs($deltaUsable) > 0.00001) {
                    $product = $entryItem->product;
                    $currentStock = $product->getCurrentStock($purchaseEntry->branch_id);
                    $product->updateBranchStock($purchaseEntry->branch_id, $currentStock + $deltaUsable);
                }

                // Totals
                $totalExpected += (float) $poItem->quantity;
                $totalReceived += $newReceived;
                $totalSpoiled += $spoiled;
                $totalDamaged += $damaged;
                $totalUsable += $usable;
                $totalExpectedWeight += (float) ($entryItem->expected_weight ?? 0);
                $totalActualWeight += (float) ($entryItem->actual_weight ?? 0);

                // Recreate stock movements
                if ($usable > 0) {
                    StockMovement::create([
                        'product_id' => $entryItem->product_id,
                        'branch_id' => $purchaseEntry->branch_id,
                        'type' => 'purchase',
                        'quantity' => $usable,
                        'unit_price' => $entryItem->unit_price,
                        'reference_type' => 'purchase_entry',
                        'reference_id' => $purchaseEntry->id,
                        'user_id' => $user->id,
                        'notes' => "Purchase Entry Updated: {$purchaseEntry->entry_number}",
                    ]);
                }
                if ($spoiled > 0) {
                    StockMovement::create([
                        'product_id' => $entryItem->product_id,
                        'branch_id' => $purchaseEntry->branch_id,
                        'type' => 'loss',
                        'quantity' => $spoiled,
                        'unit_price' => $entryItem->unit_price,
                        'reference_type' => 'purchase_entry_spoilage',
                        'reference_id' => $purchaseEntry->id,
                        'user_id' => $user->id,
                        'notes' => "Purchase Entry Spoilage Updated: {$purchaseEntry->entry_number}",
                    ]);
                }
                if ($damaged > 0) {
                    StockMovement::create([
                        'product_id' => $entryItem->product_id,
                        'branch_id' => $purchaseEntry->branch_id,
                        'type' => 'loss',
                        'quantity' => $damaged,
                        'unit_price' => $entryItem->unit_price,
                        'reference_type' => 'purchase_entry_damage',
                        'reference_id' => $purchaseEntry->id,
                        'user_id' => $user->id,
                        'notes' => "Purchase Entry Damage Updated: {$purchaseEntry->entry_number}",
                    ]);
                }
            }

            // Update entry totals
            $purchaseEntry->update([
                'total_expected_quantity' => $totalExpected,
                'total_received_quantity' => $totalReceived,
                'total_spoiled_quantity' => $totalSpoiled,
                'total_damaged_quantity' => $totalDamaged,
                'total_usable_quantity' => $totalUsable,
                'total_expected_weight' => $totalExpectedWeight ?: null,
                'total_actual_weight' => $totalActualWeight ?: null,
                'total_weight_difference' => ($totalActualWeight ?: 0) - ($totalExpectedWeight ?: 0),
                'entry_status' => ($totalSpoiled > 0 || $totalDamaged > 0) ? 'discrepancy' : 'received',
            ]);

            // Recalculate order aggregates
            optional($purchaseEntry->purchaseOrder)->recalculateReceiptAggregates();
        });

        return redirect()->route('enhanced-purchase-entries.entry', $purchaseEntry)
            ->with('success', 'Purchase entry updated successfully!');
    }

    /**
     * Delete a purchase entry and roll back aggregates and stock movements.
     */
    public function destroyEntry(PurchaseEntry $purchaseEntry)
    {
        $user = Auth::user();
        if (!$user->hasRole('branch_manager') || $purchaseEntry->branch_id !== $user->branch_id) {
            abort(403, 'Access denied.');
        }

        DB::transaction(function () use ($purchaseEntry) {
            // Delete related stock movements for this entry
            StockMovement::whereIn('reference_type', ['purchase_entry', 'purchase_entry_spoilage', 'purchase_entry_damage'])
                ->where('reference_id', $purchaseEntry->id)
                ->delete();

            // Before delete, rollback branch stock by subtracting usable quantities
            $purchaseEntry->loadMissing('purchaseEntryItems.product');
            foreach ($purchaseEntry->purchaseEntryItems as $entryItem) {
                $usable = (float) $entryItem->usable_quantity;
                if ($usable > 0) {
                    $product = $entryItem->product;
                    $currentStock = $product->getCurrentStock($purchaseEntry->branch_id);
                    $product->updateBranchStock($purchaseEntry->branch_id, $currentStock - $usable);
                }
            }

            $order = $purchaseEntry->purchaseOrder()->first();
            $purchaseEntry->delete();
            if ($order) {
                $order->recalculateReceiptAggregates();
            }
        });

        return redirect()->route('enhanced-purchase-entries.index')
            ->with('success', 'Purchase entry deleted. Aggregates updated.');
    }
}