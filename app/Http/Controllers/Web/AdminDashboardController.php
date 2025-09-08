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
}