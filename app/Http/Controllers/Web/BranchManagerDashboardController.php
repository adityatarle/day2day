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

class BranchManagerDashboardController extends Controller
{
    /**
     * Display the branch manager dashboard with branch-specific data.
     */
    public function index()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account. Please contact administrator.');
        }

        // Branch-specific statistics
        $stats = [
            'branch_products' => $branch->products()->count(),
            'branch_orders' => Order::where('branch_id', $branch->id)->count(),
            'branch_customers' => Customer::whereHas('orders', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->count(),
            'branch_staff' => $branch->users()->count(),
            'pending_purchase_orders' => PurchaseOrder::where('branch_id', $branch->id)
                ->where('status', 'pending')->count(),
            'branch_revenue' => Order::where('branch_id', $branch->id)
                ->where('status', 'completed')->sum('total_amount'),
            'monthly_revenue' => Order::where('branch_id', $branch->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount'),
            'branch_expenses' => Expense::where('branch_id', $branch->id)->sum('amount'),
        ];

        // Recent orders for this branch
        $recent_orders = Order::with(['customer', 'orderItems.product'])
            ->where('branch_id', $branch->id)
            ->latest()
            ->take(10)
            ->get();

        // Low stock products in this branch
        $low_stock_products = Product::with(['branches' => function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            }])
            ->whereHas('branches', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '<=', DB::raw('stock_threshold'));
            })
            ->take(10)
            ->get();

        // Top selling products in this branch
        $top_products = Product::whereHas('orderItems.order', function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->withCount(['orderItems as total_sold' => function($query) use ($branch) {
                $query->whereHas('order', function($q) use ($branch) {
                    $q->where('branch_id', $branch->id);
                });
            }])
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();

        // Sales analytics for this branch (last 30 days)
        $sales_analytics = Order::where('branch_id', $branch->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Branch staff performance
        $staff_performance = User::where('branch_id', $branch->id)
            ->with('role')
            ->withCount(['orders' => function($query) {
                $query->whereMonth('created_at', Carbon::now()->month);
            }])
            ->get()
            ->map(function($staff) {
                return [
                    'name' => $staff->name,
                    'role' => $staff->role->display_name,
                    'monthly_orders' => $staff->orders_count,
                    'last_login' => $staff->last_login_at ? $staff->last_login_at->diffForHumans() : 'Never',
                    'status' => $staff->is_active ? 'Active' : 'Inactive'
                ];
            });

        // Recent branch activities
        $recent_activities = collect()
            ->merge(Order::with('customer')->where('branch_id', $branch->id)->latest()->take(5)->get()->map(function($order) {
                return [
                    'type' => 'order',
                    'message' => "Order #{$order->id} from " . ($order->customer->name ?? 'Walk-in Customer'),
                    'time' => $order->created_at,
                    'icon' => 'shopping-cart',
                    'color' => 'blue'
                ];
            }))
            ->merge(PurchaseOrder::with('vendor')->where('branch_id', $branch->id)->latest()->take(3)->get()->map(function($po) {
                return [
                    'type' => 'purchase',
                    'message' => "Purchase order #{$po->id} to {$po->vendor->name}",
                    'time' => $po->created_at,
                    'icon' => 'truck',
                    'color' => 'green'
                ];
            }))
            ->sortByDesc('time')
            ->take(10);

        // Branch inventory alerts
        $inventory_alerts = [
            'low_stock' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '<=', DB::raw('stock_threshold'));
            })->count(),
            'out_of_stock' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '<=', 0);
            })->count(),
            'expiring_soon' => Product::whereHas('batches', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('expiry_date', '<=', Carbon::now()->addDays(7));
            })->count(),
        ];

        // Branch financial summary
        $financial_summary = [
            'total_sales' => Order::where('branch_id', $branch->id)
                ->where('status', 'completed')->sum('total_amount'),
            'monthly_sales' => Order::where('branch_id', $branch->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('total_amount'),
            'total_purchases' => PurchaseOrder::where('branch_id', $branch->id)
                ->where('status', 'completed')->sum('total_amount'),
            'monthly_expenses' => Expense::where('branch_id', $branch->id)
                ->whereMonth('created_at', Carbon::now()->month)->sum('amount'),
        ];

        return view('dashboards.branch_manager', compact(
            'stats',
            'branch',
            'recent_orders',
            'low_stock_products',
            'top_products',
            'sales_analytics',
            'staff_performance',
            'recent_activities',
            'inventory_alerts',
            'financial_summary'
        ));
    }
}