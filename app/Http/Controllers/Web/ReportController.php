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
     */
    public function sales(Request $request)
    {
        $query = Order::with(['customer', 'branch']);

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Filter by branch
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $orders = $query->where('status', 'completed')->latest()->paginate(20);
        $branches = Branch::all();

        // Calculate totals
        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        return view('reports.sales', compact('orders', 'branches', 'totalSales', 'totalOrders'));
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

        // Get sales by category
        $salesByCategory = Product::withCount(['orderItems as total_sold'])
            ->selectRaw('category, SUM(order_items_count) as total_sold')
            ->groupBy('category')
            ->get();

        return view('reports.analytics', compact('topProducts', 'salesByCategory'));
    }
}