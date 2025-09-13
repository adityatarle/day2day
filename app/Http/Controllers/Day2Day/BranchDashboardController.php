<?php

namespace App\Http\Controllers\Day2Day;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\LossTracking;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchDashboardController extends Controller
{
    /**
     * Display the Day2Day branch dashboard
     */
    public function index()
    {
        $user = auth()->user();
        
        // Ensure user has branch access
        if (!$user->hasRole('branch_manager') && !$user->hasRole('cashier')) {
            abort(403, 'Unauthorized access');
        }

        $dashboardData = $this->getBranchDashboardData($user->branch_id);
        
        return view('day2day.branch.dashboard', $dashboardData);
    }

    /**
     * Get comprehensive dashboard data for branch
     */
    private function getBranchDashboardData($branchId)
    {
        $branch = Branch::with('city')->findOrFail($branchId);

        // Inventory Overview
        $totalProducts = $this->getBranchProductCount($branchId);
        $lowStockCount = $this->getLowStockCount($branchId);
        $outOfStockCount = $this->getOutOfStockCount($branchId);
        $inventoryValue = $this->getInventoryValue($branchId);

        // Sales Overview
        $todaySales = $this->getTodaySales($branchId);
        $monthlySales = $this->getMonthlySales($branchId);
        $averageOrderValue = $this->getAverageOrderValue($branchId);
        $totalOrders = $this->getTotalOrders($branchId);

        // Purchase Overview
        $pendingPurchases = $this->getPendingPurchases($branchId);
        $monthlyPurchases = $this->getMonthlyPurchases($branchId);
        $recentPurchases = $this->getRecentPurchases($branchId);

        // Stock Transfers
        $pendingReceipts = $this->getPendingStockReceipts($branchId);
        $recentTransfers = $this->getRecentStockTransfers($branchId);

        // POS Overview
        $activePosSession = $this->getActivePosSession($branchId);
        $todayPosRevenue = $this->getTodayPosRevenue($branchId);

        // Loss Tracking
        $monthlyLosses = $this->getMonthlyLosses($branchId);
        $recentLosses = $this->getRecentLosses($branchId);

        // Recent Activities
        $recentActivities = $this->getRecentActivities($branchId);

        return compact(
            'branch',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'inventoryValue',
            'todaySales',
            'monthlySales',
            'averageOrderValue',
            'totalOrders',
            'pendingPurchases',
            'monthlyPurchases',
            'recentPurchases',
            'pendingReceipts',
            'recentTransfers',
            'activePosSession',
            'todayPosRevenue',
            'monthlyLosses',
            'recentLosses',
            'recentActivities'
        );
    }

    /**
     * Record material receipt from main branch or vendor (via main branch)
     */
    public function recordMaterialReceipt(Request $request)
    {
        $request->validate([
            'source' => 'required|in:main_branch,vendor_via_main_branch',
            'stock_transfer_id' => 'nullable|exists:stock_transfers,id',
            'vendor_name' => 'nullable|string|max:255', // For reference only, not linked to vendor table
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.is_damaged' => 'boolean',
            'items.*.damage_quantity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
        ]);

        $user = auth()->user();
        
        DB::beginTransaction();
        try {
            // Create material receipt record (not a purchase order to vendor)
            $materialReceipt = PurchaseOrder::create([
                'po_number' => $this->generateMaterialReceiptNumber(),
                'branch_id' => $user->branch_id,
                'vendor_id' => null, // No direct vendor relationship for sub-branches
                'stock_transfer_id' => $request->stock_transfer_id,
                'status' => 'received',
                'order_type' => 'material_receipt',
                'payment_terms' => 'immediate',
                'total_amount' => 0,
                'notes' => $request->notes . ($request->vendor_name ? ' | Original Vendor: ' . $request->vendor_name : ''),
                'terminology_notes' => 'Material Receipt - ' . ($request->source === 'main_branch' ? 'Direct from main branch' : 'From vendor via main branch'),
                'expected_delivery_date' => now(),
                'actual_delivery_date' => now(),
                'user_id' => $user->id,
            ]);

            $totalAmount = 0;
            $totalDamageValue = 0;

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity_received'] * $item['unit_price'];
                $totalAmount += $itemTotal;

                // Create material receipt item
                $receiptItem = PurchaseOrderItem::create([
                    'purchase_order_id' => $materialReceipt->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity_ordered'],
                    'received_quantity' => $item['quantity_received'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $itemTotal,
                ]);

                // Update branch inventory
                $this->updateBranchInventory(
                    $user->branch_id,
                    $item['product_id'],
                    $item['quantity_received']
                );

                // Handle damaged items
                if (!empty($item['is_damaged']) && $item['damage_quantity'] > 0) {
                    $damageValue = $item['damage_quantity'] * $item['unit_price'];
                    $totalDamageValue += $damageValue;

                    // Record loss tracking for damaged items
                    LossTracking::create([
                        'branch_id' => $user->branch_id,
                        'product_id' => $item['product_id'],
                        'batch_id' => null,
                        'loss_type' => 'damage',
                        'quantity_lost' => $item['damage_quantity'],
                        'unit_cost' => $item['unit_price'],
                        'total_loss_value' => $damageValue,
                        'reason' => 'Damaged goods received in material receipt',
                        'loss_date' => now(),
                        'recorded_by' => $user->id,
                    ]);

                    // Reduce inventory for damaged items
                    $this->updateBranchInventory(
                        $user->branch_id,
                        $item['product_id'],
                        -$item['damage_quantity']
                    );
                }
            }

            // Update total amount
            $materialReceipt->update(['total_amount' => $totalAmount]);

            // Mark stock transfer as received if applicable
            if ($request->stock_transfer_id) {
                $stockTransfer = StockTransfer::find($request->stock_transfer_id);
                if ($stockTransfer && $stockTransfer->status === 'dispatched') {
                    $stockTransfer->update([
                        'status' => 'delivered',
                        'delivered_date' => now(),
                        'confirmed_date' => now(),
                    ]);
                }
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Material receipt recorded successfully',
                'receipt_id' => $materialReceipt->id,
                'total_damage_value' => $totalDamageValue,
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record material receipt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record damage/wastage entry
     */
    public function recordDamage(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'loss_type' => 'required|in:damage,wastage,expiry,theft,other',
            'reason' => 'required|string|max:500',
            'unit_cost' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $totalLossValue = $request->quantity * $request->unit_cost;

        DB::beginTransaction();
        try {
            // Record loss tracking
            $lossTracking = LossTracking::create([
                'branch_id' => $user->branch_id,
                'product_id' => $request->product_id,
                'batch_id' => null,
                'loss_type' => $request->loss_type,
                'quantity_lost' => $request->quantity,
                'unit_cost' => $request->unit_cost,
                'total_loss_value' => $totalLossValue,
                'reason' => $request->reason,
                'loss_date' => now(),
                'recorded_by' => $user->id,
            ]);

            // Update branch inventory (reduce stock)
            $this->updateBranchInventory(
                $user->branch_id,
                $request->product_id,
                -$request->quantity
            );

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Damage/wastage recorded successfully',
                'loss_id' => $lossTracking->id,
                'total_loss_value' => $totalLossValue,
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record damage: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get branch sales report
     */
    public function getSalesReport(Request $request)
    {
        $user = auth()->user();
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $salesData = Order::where('branch_id', $user->branch_id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.product', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_orders' => $salesData->count(),
            'total_revenue' => $salesData->sum('total_amount'),
            'average_order_value' => $salesData->avg('total_amount'),
            'total_items_sold' => $salesData->sum(function($order) {
                return $order->items->sum('quantity');
            }),
        ];

        return response()->json([
            'sales_data' => $salesData,
            'summary' => $summary,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    /**
     * Get branch purchase report
     */
    public function getPurchaseReport(Request $request)
    {
        $user = auth()->user();
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $purchaseData = PurchaseOrder::where('branch_id', $user->branch_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['vendor', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_purchases' => $purchaseData->count(),
            'total_amount' => $purchaseData->sum('total_amount'),
            'total_items_purchased' => $purchaseData->sum(function($purchase) {
                return $purchase->items->sum('quantity_received');
            }),
        ];

        return response()->json([
            'purchase_data' => $purchaseData,
            'summary' => $summary,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    // Helper methods
    private function getBranchProductCount($branchId)
    {
        return DB::table('product_branches')
            ->where('branch_id', $branchId)
            ->count();
    }

    private function getLowStockCount($branchId)
    {
        return DB::table('product_branches')
            ->where('branch_id', $branchId)
            ->where('current_stock', '<', 10)
            ->where('current_stock', '>', 0)
            ->count();
    }

    private function getOutOfStockCount($branchId)
    {
        return DB::table('product_branches')
            ->where('branch_id', $branchId)
            ->where('current_stock', '<=', 0)
            ->count();
    }

    private function getInventoryValue($branchId)
    {
        return DB::table('product_branches')
            ->where('branch_id', $branchId)
            ->sum(DB::raw('current_stock * selling_price'));
    }

    private function getTodaySales($branchId)
    {
        return Order::where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total_amount');
    }

    private function getMonthlySales($branchId)
    {
        return Order::where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
    }

    private function getAverageOrderValue($branchId)
    {
        return Order::where('branch_id', $branchId)
            ->where('status', 'completed')
            ->avg('total_amount') ?? 0;
    }

    private function getTotalOrders($branchId)
    {
        return Order::where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    private function getPendingPurchases($branchId)
    {
        return PurchaseOrder::where('branch_id', $branchId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
    }

    private function getMonthlyPurchases($branchId)
    {
        return PurchaseOrder::where('branch_id', $branchId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
    }

    private function getRecentPurchases($branchId)
    {
        return PurchaseOrder::where('branch_id', $branchId)
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($purchase) {
                return [
                    'id' => $purchase->id,
                    'order_number' => $purchase->order_number,
                    'vendor_name' => $purchase->vendor->name ?? 'Unknown',
                    'total_amount' => $purchase->total_amount,
                    'status' => $purchase->status,
                    'created_at' => $purchase->created_at->format('M d, Y H:i'),
                ];
            });
    }

    private function getPendingStockReceipts($branchId)
    {
        return StockTransfer::where('to_branch_id', $branchId)
            ->whereIn('status', ['pending', 'dispatched'])
            ->count();
    }

    private function getRecentStockTransfers($branchId)
    {
        return StockTransfer::where('to_branch_id', $branchId)
            ->with('fromBranch')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($transfer) {
                return [
                    'id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                    'from_branch' => $transfer->fromBranch->name,
                    'status' => $transfer->status,
                    'expected_delivery' => $transfer->expected_delivery ? 
                        Carbon::parse($transfer->expected_delivery)->format('M d, Y') : null,
                    'created_at' => $transfer->created_at->format('M d, Y H:i'),
                ];
            });
    }

    private function getActivePosSession($branchId)
    {
        return PosSession::where('branch_id', $branchId)
            ->where('status', 'active')
            ->with('user')
            ->first();
    }

    private function getTodayPosRevenue($branchId)
    {
        return Order::where('branch_id', $branchId)
            ->where('order_type', 'on_shop')
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total_amount');
    }

    private function getMonthlyLosses($branchId)
    {
        return LossTracking::where('branch_id', $branchId)
            ->whereMonth('loss_date', now()->month)
            ->whereYear('loss_date', now()->year)
            ->sum('total_loss_value');
    }

    private function getRecentLosses($branchId)
    {
        return LossTracking::where('branch_id', $branchId)
            ->with('product')
            ->orderBy('loss_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function($loss) {
                return [
                    'id' => $loss->id,
                    'product_name' => $loss->product->name,
                    'loss_type' => $loss->loss_type,
                    'quantity_lost' => $loss->quantity_lost,
                    'total_loss_value' => $loss->total_loss_value,
                    'reason' => $loss->reason,
                    'loss_date' => Carbon::parse($loss->loss_date)->format('M d, Y H:i'),
                ];
            });
    }

    private function getRecentActivities($branchId)
    {
        // Combine recent orders, purchases, and stock transfers
        $activities = collect();

        // Recent orders
        $recentOrders = Order::where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($order) {
                return [
                    'type' => 'sale',
                    'description' => "Sale #{$order->order_number} - ₹{$order->total_amount}",
                    'timestamp' => $order->created_at,
                ];
            });

        // Recent purchases
        $recentPurchases = PurchaseOrder::where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get()
            ->map(function($purchase) {
                return [
                    'type' => 'purchase',
                    'description' => "Purchase #{$purchase->order_number} - ₹{$purchase->total_amount}",
                    'timestamp' => $purchase->created_at,
                ];
            });

        return $activities->merge($recentOrders)
            ->merge($recentPurchases)
            ->sortByDesc('timestamp')
            ->take(5)
            ->values()
            ->map(function($activity) {
                return [
                    'type' => $activity['type'],
                    'description' => $activity['description'],
                    'time_ago' => Carbon::parse($activity['timestamp'])->diffForHumans(),
                ];
            });
    }

    private function updateBranchInventory($branchId, $productId, $quantity)
    {
        DB::table('product_branches')
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->increment('current_stock', $quantity);
    }

    private function generateMaterialReceiptNumber()
    {
        $prefix = 'MR-' . date('Ymd') . '-';
        $lastReceipt = PurchaseOrder::where('po_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->po_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create purchase request to main branch (sub-branches cannot order from vendors directly)
     */
    public function createPurchaseRequest(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'expected_delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        
        DB::beginTransaction();
        try {
            // Generate request number
            $requestNumber = 'PR-' . date('Y') . '-' . str_pad(
                PurchaseOrder::where('order_type', 'branch_request')->count() + 1, 
                4, '0', STR_PAD_LEFT
            );

            // Create purchase request to main branch
            $purchaseRequest = PurchaseOrder::create([
                'po_number' => $requestNumber,
                'branch_id' => $user->branch_id,
                'vendor_id' => null, // No vendor - this is a request to main branch
                'user_id' => $user->id,
                'status' => 'pending',
                'order_type' => 'branch_request',
                'payment_terms' => 'immediate',
                'total_amount' => 0,
                'notes' => $request->notes,
                'expected_delivery_date' => $request->expected_delivery_date,
                'priority' => $request->priority,
                'terminology_notes' => 'Branch Purchase Request - sent to main branch for approval and fulfillment',
            ]);

            $totalEstimatedAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $estimatedPrice = $product->purchase_price ?? 0;
                $totalPrice = $item['quantity'] * $estimatedPrice;
                $totalEstimatedAmount += $totalPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseRequest->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $estimatedPrice,
                    'total_price' => $totalPrice,
                    'notes' => $item['reason'],
                ]);
            }

            $purchaseRequest->update(['total_amount' => $totalEstimatedAmount]);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase request sent to main branch successfully',
                'request_id' => $purchaseRequest->id,
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get products available for branch
     */
    public function getBranchProducts()
    {
        $user = auth()->user();
        
        return response()->json(
            Product::whereHas('branches', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })->with(['branches' => function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            }])->get()
        );
    }
}