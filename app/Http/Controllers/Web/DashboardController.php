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

class DashboardController extends Controller
{
    /**
     * Display the comprehensive admin dashboard with key metrics and data.
     */
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_customers' => Customer::count(),
            'total_vendors' => Vendor::count(),
            'total_branches' => Branch::count(),
            'total_users' => User::count(),
            'pending_purchase_orders' => PurchaseOrder::where('status', 'pending')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'monthly_revenue' => Order::where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount'),
            'total_expenses' => Expense::sum('amount'),
        ];

        // Get recent orders
        $recent_orders = Order::with(['customer', 'orderItems.product', 'branch'])
            ->latest()
            ->take(10)
            ->get();

        // Get low stock products
        $low_stock_products = Product::with(['branches'])
            ->whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', DB::raw('stock_threshold'));
            })
            ->take(10)
            ->get();

        // Get top selling products
        $top_products = Product::withCount(['orderItems as total_sold'])
            ->with(['orderItems' => function($query) {
                $query->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(subtotal) as total_revenue');
                $query->groupBy('product_id');
            }])
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();

        // Get sales analytics for the last 7 days
        $sales_analytics = Order::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get branch performance
        $branch_performance = Branch::withCount(['orders', 'products'])
            ->withSum('orders', 'total_amount')
            ->get();

        // Get recent activities
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
            ->merge(User::latest()->take(2)->get()->map(function($user) {
                return [
                    'type' => 'user',
                    'message' => "New user registered: {$user->name}",
                    'time' => $user->created_at,
                    'icon' => 'user-plus',
                    'color' => 'purple'
                ];
            }))
            ->sortByDesc('time')
            ->take(10);

        // Get inventory alerts
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

        return view('dashboard', compact(
            'stats',
            'recent_orders',
            'low_stock_products',
            'top_products',
            'sales_analytics',
            'branch_performance',
            'recent_activities',
            'inventory_alerts'
        ));
    }
}