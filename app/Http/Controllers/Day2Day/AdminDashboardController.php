<?php

namespace App\Http\Controllers\Day2Day;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\PurchaseOrder;
use App\Models\Order;
use App\Models\User;
use App\Models\City;
use App\Models\CityProductPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the Day2Day admin dashboard
     */
    public function index()
    {
        $user = auth()->user();
        
        // Ensure user is admin
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized access');
        }

        $dashboardData = $this->getDashboardData();
        
        return view('day2day.admin.dashboard', $dashboardData);
    }

    /**
     * Get comprehensive dashboard data for admin
     */
    private function getDashboardData()
    {
        // Branch Statistics
        $totalBranches = Branch::where('code', '!=', 'D2D-MAIN')->active()->count();
        $activeBranches = Branch::where('code', '!=', 'D2D-MAIN')
            ->active()
            ->whereHas('users', function($q) {
                $q->where('is_active', true);
            })->count();

        // Stock Transfer Statistics
        $pendingTransfers = StockTransfer::where('status', 'pending')->count();
        $inTransitTransfers = StockTransfer::where('status', 'dispatched')->count();
        $overdueTransfers = StockTransfer::where('status', 'dispatched')
            ->where('expected_delivery', '<', now())
            ->count();

        // Material Supply Overview
        $totalProductsSupplied = Product::active()->count();
        $lowStockProducts = $this->getLowStockProducts();
        $recentTransfers = $this->getRecentTransfers();

        // Branch Performance
        $branchPerformance = $this->getBranchPerformance();
        $topPerformingBranches = $this->getTopPerformingBranches();

        // Financial Overview
        $monthlySupplyValue = $this->getMonthlySupplyValue();
        $totalRevenue = $this->getTotalRevenue();

        // Recent Activities
        $recentPurchaseOrders = $this->getRecentPurchaseOrders();
        $recentBranchRequests = $this->getRecentBranchRequests();

        return compact(
            'totalBranches',
            'activeBranches',
            'pendingTransfers',
            'inTransitTransfers',
            'overdueTransfers',
            'totalProductsSupplied',
            'lowStockProducts',
            'recentTransfers',
            'branchPerformance',
            'topPerformingBranches',
            'monthlySupplyValue',
            'totalRevenue',
            'recentPurchaseOrders',
            'recentBranchRequests'
        );
    }

    /**
     * Get products with low stock across branches
     */
    private function getLowStockProducts()
    {
        return DB::table('product_branches')
            ->join('products', 'product_branches.product_id', '=', 'products.id')
            ->join('branches', 'product_branches.branch_id', '=', 'branches.id')
            ->select(
                'products.name as product_name',
                'products.code as product_code',
                'branches.name as branch_name',
                'product_branches.current_stock',
                'product_branches.selling_price'
            )
            ->where('product_branches.current_stock', '<', 10)
            ->where('branches.code', '!=', 'D2D-MAIN')
            ->orderBy('product_branches.current_stock', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Get recent stock transfers
     */
    private function getRecentTransfers()
    {
        return StockTransfer::with(['fromBranch', 'toBranch', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($transfer) {
                return [
                    'id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                    'from_branch' => $transfer->fromBranch->name,
                    'to_branch' => $transfer->toBranch->name,
                    'status' => $transfer->status,
                    'total_items' => $transfer->items->count(),
                    'created_at' => $transfer->created_at->format('M d, Y H:i'),
                    'expected_delivery' => $transfer->expected_delivery ? 
                        Carbon::parse($transfer->expected_delivery)->format('M d, Y') : null,
                ];
            });
    }

    /**
     * Get branch performance data
     */
    private function getBranchPerformance()
    {
        return Branch::where('code', '!=', 'D2D-MAIN')
            ->active()
            ->withCount(['orders as total_sales'])
            ->with(['city'])
            ->get()
            ->map(function($branch) {
                $monthlyRevenue = Order::where('branch_id', $branch->id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount');

                $averageOrderValue = Order::where('branch_id', $branch->id)
                    ->where('status', 'completed')
                    ->avg('total_amount');

                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'city' => $branch->city->name,
                    'code' => $branch->code,
                    'total_sales' => $branch->total_sales,
                    'monthly_revenue' => round($monthlyRevenue, 2),
                    'average_order_value' => round($averageOrderValue, 2),
                    'is_active' => $branch->is_active,
                ];
            });
    }

    /**
     * Get top performing branches
     */
    private function getTopPerformingBranches()
    {
        return Branch::where('code', '!=', 'D2D-MAIN')
            ->active()
            ->withSum(['orders as revenue' => function($query) {
                $query->where('status', 'completed')
                      ->whereMonth('created_at', now()->month);
            }], 'total_amount')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get()
            ->map(function($branch) {
                return [
                    'name' => $branch->name,
                    'city' => $branch->city->name ?? 'Unknown',
                    'revenue' => round($branch->revenue ?? 0, 2),
                ];
            });
    }

    /**
     * Get monthly supply value
     */
    private function getMonthlySupplyValue()
    {
        return StockTransfer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->with('items')
            ->get()
            ->sum(function($transfer) {
                return $transfer->items->sum(function($item) {
                    return $item->quantity * $item->unit_cost;
                });
            });
    }

    /**
     * Get total revenue from all branches
     */
    private function getTotalRevenue()
    {
        return Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
    }

    /**
     * Get recent purchase requests from branches (sub-branches send requests to main branch)
     */
    private function getRecentPurchaseOrders()
    {
        // Get both regular purchase orders (to vendors) and branch requests (from sub-branches)
        $regularOrders = PurchaseOrder::with(['branch', 'vendor'])
            ->where('order_type', 'purchase_order')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($po) {
                return [
                    'id' => $po->id,
                    'order_number' => $po->po_number ?? $po->order_number,
                    'branch_name' => $po->branch->name ?? 'Main Branch',
                    'vendor_name' => $po->vendor->name ?? 'Unknown',
                    'total_amount' => $po->total_amount,
                    'status' => $po->status,
                    'type' => 'Purchase Order',
                    'created_at' => $po->created_at->format('M d, Y H:i'),
                ];
            });

        $branchRequests = PurchaseOrder::with(['branch'])
            ->where('order_type', 'branch_request')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($pr) {
                return [
                    'id' => $pr->id,
                    'order_number' => $pr->po_number,
                    'branch_name' => $pr->branch->name ?? 'Unknown',
                    'vendor_name' => 'Main Branch',
                    'total_amount' => $pr->total_amount,
                    'status' => $pr->status,
                    'type' => 'Branch Request',
                    'created_at' => $pr->created_at->format('M d, Y H:i'),
                ];
            });

        return $regularOrders->merge($branchRequests)->sortByDesc('created_at')->take(10)->values();
    }

    /**
     * Get recent branch requests/queries
     */
    private function getRecentBranchRequests()
    {
        // This would typically come from a requests/queries table
        // For now, we'll use stock transfer queries as an example
        return collect([
            [
                'id' => 1,
                'branch_name' => 'Mumbai Branch',
                'type' => 'Stock Request',
                'description' => 'Urgent requirement for Daily Essentials Kit',
                'priority' => 'high',
                'created_at' => now()->subHours(2)->format('M d, Y H:i'),
            ],
            [
                'id' => 2,
                'branch_name' => 'Delhi Branch',
                'type' => 'Damage Report',
                'description' => 'Damaged goods received in last shipment',
                'priority' => 'medium',
                'created_at' => now()->subHours(5)->format('M d, Y H:i'),
            ],
            [
                'id' => 3,
                'branch_name' => 'Bangalore Branch',
                'type' => 'Price Update',
                'description' => 'Request for updated pricing for Health & Wellness Pack',
                'priority' => 'low',
                'created_at' => now()->subHours(8)->format('M d, Y H:i'),
            ],
        ]);
    }

    /**
     * Supply materials to branches (Create stock transfer)
     */
    public function supplyMaterials(Request $request)
    {
        $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'expected_delivery' => 'required|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $mainBranch = Branch::where('code', 'D2D-MAIN')->first();
            
            $stockTransfer = StockTransfer::create([
                'transfer_number' => $this->generateTransferNumber(),
                'from_branch_id' => $mainBranch->id,
                'to_branch_id' => $request->to_branch_id,
                'status' => 'pending',
                'expected_delivery' => $request->expected_delivery,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                $stockTransfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $product->purchase_price,
                    'total_cost' => $item['quantity'] * $product->purchase_price,
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Materials supplied successfully',
                'transfer_id' => $stockTransfer->id,
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to supply materials: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get branch-specific reports
     */
    public function getBranchReports($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        
        $salesData = Order::where('branch_id', $branchId)
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as daily_sales')
            ->whereMonth('created_at', now()->month)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $purchaseData = PurchaseOrder::where('branch_id', $branchId)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as daily_purchases')
            ->whereMonth('created_at', now()->month)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'branch' => $branch,
            'sales_data' => $salesData,
            'purchase_data' => $purchaseData,
        ]);
    }

    /**
     * Update city-specific pricing
     */
    public function updateCityPricing(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'product_id' => 'required|exists:products,id',
            'selling_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $cityPricing = CityProductPricing::updateOrCreate(
            [
                'city_id' => $request->city_id,
                'product_id' => $request->product_id,
            ],
            [
                'selling_price' => $request->selling_price,
                'mrp' => $request->mrp,
                'discount_percentage' => $request->discount_percentage ?? 0,
                'is_available' => true,
                'effective_from' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'City pricing updated successfully',
            'pricing' => $cityPricing,
        ]);
    }

    /**
     * Generate unique transfer number
     */
    private function generateTransferNumber()
    {
        $prefix = 'ST-' . date('Ymd') . '-';
        $lastTransfer = StockTransfer::where('transfer_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransfer) {
            $lastNumber = intval(substr($lastTransfer->transfer_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get all cities for admin operations
     */
    public function getCities()
    {
        return response()->json(City::active()->get());
    }

    /**
     * Get all products for admin operations
     */
    public function getProducts()
    {
        return response()->json(Product::active()->get());
    }

    /**
     * Get all branches (excluding main branch)
     */
    public function getBranches()
    {
        return response()->json(
            Branch::where('code', '!=', 'D2D-MAIN')
                ->active()
                ->with('city')
                ->get()
        );
    }
}