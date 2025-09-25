<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with business operations focus.
     */
    public function index()
    {
        // Business operation statistics
        $stats = [
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_customers' => Customer::count(),
            'total_vendors' => Vendor::count(),
            'total_branches' => Branch::count(),
            'total_staff' => User::whereHas('role', fn($q) => $q->whereIn('name', ['branch_manager', 'cashier', 'delivery_boy']))->count(),
            'pending_purchase_orders' => PurchaseOrder::where('status', 'pending')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'monthly_revenue' => Order::where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount'),
            'total_expenses' => Expense::sum('amount'),
        ];

        // Recent orders with detailed information
        $recent_orders = Order::with(['customer', 'orderItems.product', 'branch'])
            ->latest()
            ->take(10)
            ->get();

        // Low stock products across all branches
        $low_stock_products = Product::with(['branches'])
            ->whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', DB::raw('stock_threshold'));
            })
            ->take(10)
            ->get();

        // Top selling products
        $top_products = Product::withCount(['orderItems as total_sold'])
            ->with(['orderItems' => function($query) {
                $query->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue');
                $query->groupBy('product_id');
            }])
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();

        // Sales analytics for the last 30 days
        $sales_analytics = Order::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Branch performance with detailed metrics
        $branch_performance = Branch::withCount(['orders', 'products', 'users'])
            ->withSum('orders', 'total_amount')
            ->with(['manager' => function($query) {
                $query->select('id', 'name', 'branch_id');
            }])
            ->get()
            ->map(function($branch) {
                $monthly_orders = Order::where('branch_id', $branch->id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->count();
                
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'manager' => $branch->manager->name ?? 'No Manager',
                    'total_orders' => $branch->orders_count,
                    'monthly_orders' => $monthly_orders,
                    'total_revenue' => $branch->orders_sum_total_amount ?? 0,
                    'total_products' => $branch->products_count,
                    'total_staff' => $branch->users_count,
                ];
            });

        // Recent business activities
        $recent_activities = collect()
            ->merge(Order::with('customer')->latest()->take(5)->get()->map(function($order) {
                return [
                    'type' => 'order',
                    'message' => "New order #{$order->id} from " . ($order->customer->name ?? 'Walk-in Customer'),
                    'time' => $order->created_at,
                    'icon' => 'shopping-cart',
                    'color' => 'blue'
                ];
            }))
            ->merge(PurchaseOrder::with('vendor')->latest()->take(3)->get()->map(function($po) {
                return [
                    'type' => 'purchase',
                    'message' => "Purchase order #{$po->id} to {$po->vendor->name}",
                    'time' => $po->created_at,
                    'icon' => 'truck',
                    'color' => 'green'
                ];
            }))
            ->merge(Customer::latest()->take(2)->get()->map(function($customer) {
                return [
                    'type' => 'customer',
                    'message' => "New customer registered: {$customer->name}",
                    'time' => $customer->created_at,
                    'icon' => 'user-plus',
                    'color' => 'purple'
                ];
            }))
            ->sortByDesc('time')
            ->take(10);

        // Inventory alerts
        $inventory_alerts = [
            'low_stock' => Product::whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', DB::raw('stock_threshold'));
            })->count(),
            'out_of_stock' => Product::whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', 0);
            })->count(),
            'expiring_soon' => Product::whereHas('batches', function ($query) {
                $query->where('expiry_date', '<=', Carbon::now()->addDays(7));
            })->count(),
        ];

        // Financial overview
        $financial_overview = [
            'total_sales' => Order::where('status', 'completed')->sum('total_amount'),
            'monthly_sales' => Order::where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('total_amount'),
            'total_purchases' => PurchaseOrder::where('status', 'completed')->sum('total_amount'),
            'monthly_expenses' => Expense::whereMonth('created_at', Carbon::now()->month)->sum('amount'),
        ];

        return view('dashboards.admin', compact(
            'stats',
            'recent_orders',
            'low_stock_products',
            'top_products',
            'sales_analytics',
            'branch_performance',
            'recent_activities',
            'inventory_alerts',
            'financial_overview'
        ));
    }

    /**
     * Get today's focus data for the modal
     */
    public function todaysFocus()
    {
        $today = Carbon::today();
        
        // Today's orders
        $todays_orders = Order::whereDate('created_at', $today)->count();
        $todays_revenue = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');
        
        // Low stock alerts
        $low_stock_items = Product::whereHas('branches', function ($query) {
            $query->where('current_stock', '<=', DB::raw('stock_threshold'));
        })->count();
        
        // Pending purchase orders
        $pending_purchase_orders = PurchaseOrder::where('status', 'pending')->count();
        
        // Today's activities
        $todays_activities = collect()
            ->merge(Order::whereDate('created_at', $today)->with('customer')->latest()->take(5)->get()->map(function($order) {
                return [
                    'type' => 'order',
                    'message' => "Order #{$order->id} from " . ($order->customer->name ?? 'Walk-in Customer'),
                    'time' => $order->created_at->format('H:i'),
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'icon' => 'shopping-cart',
                    'color' => 'blue'
                ];
            }))
            ->merge(PurchaseOrder::whereDate('created_at', $today)->with('vendor')->latest()->take(3)->get()->map(function($po) {
                return [
                    'type' => 'purchase',
                    'message' => "Purchase order #{$po->id} to {$po->vendor->name}",
                    'time' => $po->created_at->format('H:i'),
                    'amount' => $po->total_amount,
                    'status' => $po->status,
                    'icon' => 'truck',
                    'color' => 'green'
                ];
            }))
            ->sortByDesc('time')
            ->take(8);

        // Priority tasks
        $priority_tasks = [
            [
                'title' => 'Review Low Stock Items',
                'description' => "{$low_stock_items} items need restocking",
                'priority' => $low_stock_items > 0 ? 'high' : 'low',
                'action' => route('inventory.lowStockAlerts'),
                'icon' => 'exclamation-triangle',
                'color' => $low_stock_items > 0 ? 'red' : 'green'
            ],
            [
                'title' => 'Process Pending Purchase Orders',
                'description' => "{$pending_purchase_orders} orders awaiting approval",
                'priority' => $pending_purchase_orders > 0 ? 'high' : 'low',
                'action' => route('purchase-orders.index'),
                'icon' => 'file-invoice',
                'color' => $pending_purchase_orders > 0 ? 'orange' : 'green'
            ],
            [
                'title' => 'Review Today\'s Sales',
                'description' => "₹" . number_format($todays_revenue, 2) . " in revenue from {$todays_orders} orders",
                'priority' => 'medium',
                'action' => route('orders.index'),
                'icon' => 'chart-line',
                'color' => 'blue'
            ]
        ];

        return response()->json([
            'todays_orders' => $todays_orders,
            'todays_revenue' => $todays_revenue,
            'low_stock_items' => $low_stock_items,
            'pending_purchase_orders' => $pending_purchase_orders,
            'activities' => $todays_activities,
            'priority_tasks' => $priority_tasks
        ]);
    }

    /**
     * Get performance data for the modal
     */
    public function performance()
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        
        // Current month metrics
        $currentMonthOrders = Order::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();
        $currentMonthRevenue = Order::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');
        
        // Last month metrics
        $lastMonthOrders = Order::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();
        $lastMonthRevenue = Order::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->where('status', 'completed')
            ->sum('total_amount');
        
        // Calculate growth percentages
        $orderGrowth = $lastMonthOrders > 0 ? (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;
        $revenueGrowth = $lastMonthRevenue > 0 ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;
        
        // Branch performance comparison
        $branchPerformance = Branch::withCount(['orders' => function($query) use ($currentMonth) {
            $query->whereMonth('created_at', $currentMonth->month)
                  ->whereYear('created_at', $currentMonth->year);
        }])
        ->withSum(['orders' => function($query) use ($currentMonth) {
            $query->whereMonth('created_at', $currentMonth->month)
                  ->whereYear('created_at', $currentMonth->year)
                  ->where('status', 'completed');
        }], 'total_amount')
        ->with('manager')
        ->get()
        ->map(function($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'manager' => $branch->manager->name ?? 'No Manager',
                'orders' => $branch->orders_count,
                'revenue' => $branch->orders_sum_total_amount ?? 0,
                'performance_score' => $this->calculateBranchPerformanceScore($branch)
            ];
        })
        ->sortByDesc('performance_score')
        ->take(5);

        // Top performing products
        $topProducts = Product::withCount(['orderItems as total_sold' => function($query) use ($currentMonth) {
            $query->whereHas('order', function($q) use ($currentMonth) {
                $q->whereMonth('created_at', $currentMonth->month)
                  ->whereYear('created_at', $currentMonth->year);
            });
        }])
        ->orderBy('total_sold', 'desc')
        ->take(5)
        ->get();

        // Performance trends (last 7 days)
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayOrders = Order::whereDate('created_at', $date)->count();
            $dayRevenue = Order::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $trends[] = [
                'date' => $date->format('M d'),
                'orders' => $dayOrders,
                'revenue' => $dayRevenue
            ];
        }

        return response()->json([
            'current_month' => [
                'orders' => $currentMonthOrders,
                'revenue' => $currentMonthRevenue,
                'order_growth' => round($orderGrowth, 1),
                'revenue_growth' => round($revenueGrowth, 1)
            ],
            'branch_performance' => $branchPerformance,
            'top_products' => $topProducts,
            'trends' => $trends
        ]);
    }

    /**
     * Get widget data for AJAX updates
     */
    public function getWidgetData(Request $request)
    {
        $widget = $request->get('widget');
        
        switch ($widget) {
            case 'products':
                $totalProducts = Product::count();
                $lowStock = Product::whereHas('branches', function ($query) {
                    $query->where('current_stock', '<=', DB::raw('stock_threshold'));
                })->count();
                
                return response()->json([
                    'total' => $totalProducts,
                    'low_stock' => $lowStock
                ]);
                
            case 'orders':
                $totalOrders = Order::count();
                $todaysOrders = Order::whereDate('created_at', Carbon::today())->count();
                
                return response()->json([
                    'total' => $totalOrders,
                    'todays' => $todaysOrders
                ]);
                
            case 'revenue':
                $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
                $monthlyRevenue = Order::where('status', 'completed')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->sum('total_amount');
                
                return response()->json([
                    'total' => $totalRevenue,
                    'monthly' => $monthlyRevenue
                ]);
                
            case 'branches':
                $totalBranches = Branch::count();
                $totalStaff = User::whereHas('role', fn($q) => $q->whereIn('name', ['branch_manager', 'cashier', 'delivery_boy']))->count();
                
                return response()->json([
                    'total' => $totalBranches,
                    'staff' => $totalStaff
                ]);
                
            default:
                return response()->json(['error' => 'Invalid widget'], 400);
        }
    }

    /**
     * Calculate branch performance score
     */
    private function calculateBranchPerformanceScore($branch)
    {
        $ordersScore = min(($branch->orders_count / 100) * 100, 100);
        $revenueScore = min(($branch->orders_sum_total_amount / 100000) * 100, 100);
        
        return round(($ordersScore + $revenueScore) / 2, 1);
    }
}