<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        // Get summary statistics
        $stats = [
            'total_sales' => Order::where('status', 'completed')->sum('total_amount'),
            'total_orders' => Order::count(),
            'total_customers' => Customer::count(),
            'total_products' => Product::count(),
        ];

        // Get monthly sales data for chart
        $monthlySales = Order::where('status', 'completed')
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('reports.index', compact('stats', 'monthlySales'));
    }

    /**
     * Display reports for the authenticated manager's branch.
     */
    public function branchIndex()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'No branch assigned to your account.');
        }

        $stats = [
            'total_sales' => Order::where('status', 'completed')->where('branch_id', $branch->id)->sum('total_amount'),
            'total_orders' => Order::where('branch_id', $branch->id)->count(),
            'total_customers' => Customer::whereHas('orders', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->count(),
            'total_products' => Product::count(),
        ];

        $monthlySales = Order::where('status', 'completed')
            ->where('branch_id', $branch->id)
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('reports.index', compact('stats', 'monthlySales'));
    }

    /**
     * Display sales reports.
     * Main branch (admin) can view all branch sales, sub-branches can only view their own.
     */
    public function sales(Request $request)
    {
        $user = auth()->user();
        $query = Order::with(['customer', 'branch']);

        // Branch filtering based on user role
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            // Sub-branch managers can only see their own branch sales
            $query->where('branch_id', $user->branch_id);
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            // Main branch (admin/super_admin) can see all branch sales
            $branches = Branch::orderBy('name')->get();
            
            // Filter by branch if specified
            if ($request->has('branch_id') && $request->branch_id !== '') {
                $query->where('branch_id', $request->branch_id);
            }
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $orders = $query->where('status', 'completed')->latest()->paginate(20);

        // Calculate totals
        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        // For main branch, also show branch-wise summary
        $branchSummary = [];
        if (!$user->hasRole('branch_manager')) {
            $branchSummary = Order::with('branch')
                ->where('status', 'completed')
                ->when($request->has('start_date') && $request->has('end_date'), function($q) use ($request) {
                    return $q->whereBetween('created_at', [$request->start_date, $request->end_date]);
                })
                ->selectRaw('branch_id, COUNT(*) as order_count, SUM(total_amount) as total_sales')
                ->groupBy('branch_id')
                ->orderByDesc('total_sales')
                ->get();
        }

        return view('reports.sales', compact('orders', 'branches', 'totalSales', 'totalOrders', 'branchSummary'));
    }

    /**
     * Display inventory reports.
     */
    public function inventory()
    {
        $products = Product::with(['branches'])
            ->whereHas('branches', function ($query) {
                $query->where('current_stock', '>', 0);
            })
            ->get();

        $lowStockProducts = Product::with(['branches'])
            ->whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', DB::raw('stock_threshold'));
            })
            ->get();

        $totalValue = $products->sum(function ($product) {
            return $product->branches->sum(function ($branch) use ($product) {
                return $branch->pivot->current_stock * $branch->pivot->selling_price;
            });
        });

        return view('reports.inventory', compact('products', 'lowStockProducts', 'totalValue'));
    }

    /**
     * Display customer reports.
     */
    public function customers()
    {
        $customers = Customer::withCount('orders')
            ->withSum('orders', 'total_amount')
            ->orderBy('orders_sum_total_amount', 'desc')
            ->paginate(20);

        $topCustomers = Customer::withCount('orders')
            ->withSum('orders', 'total_amount')
            ->orderBy('orders_sum_total_amount', 'desc')
            ->take(10)
            ->get();

        return view('reports.customers', compact('customers', 'topCustomers'));
    }

    /**
     * Display vendor reports.
     */
    public function vendors()
    {
        $vendors = Vendor::withCount('purchaseOrders')
            ->withSum('purchaseOrders', 'total_amount')
            ->orderBy('purchase_orders_sum_total_amount', 'desc')
            ->paginate(20);

        return view('reports.vendors', compact('vendors'));
    }

    /**
     * Display expense reports.
     */
    public function expenses()
    {
        // This would typically include various expense categories
        // For now, we'll show a basic structure
        return view('reports.expenses');
    }

    /**
     * Display profit and loss reports.
     */
    public function profitLoss(Request $request)
    {
        $query = Order::where('status', 'completed');

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $totalRevenue = $query->sum('total_amount');
        
        // This is a simplified calculation - in a real system you'd have actual cost data
        $estimatedCosts = $totalRevenue * 0.6; // Assuming 60% cost
        $grossProfit = $totalRevenue - $estimatedCosts;

        return view('reports.profit-loss', compact('totalRevenue', 'estimatedCosts', 'grossProfit'));
    }

    /**
     * Display analytics reports.
     */
    public function analytics()
    {
        // Get top selling products
        $topProducts = Product::withCount(['orderItems as total_sold'])
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();

        // Get sales by category - fix the query to properly count order items
        $salesByCategory = Product::join('order_items', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.category, COUNT(order_items.id) as total_sold')
            ->groupBy('products.category')
            ->orderBy('total_sold', 'desc')
            ->get();

        return view('reports.analytics', compact('topProducts', 'salesByCategory'));
    }
}