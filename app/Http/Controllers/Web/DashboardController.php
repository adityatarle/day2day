<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with key metrics and data.
     */
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_customers' => Customer::count(),
            'total_vendors' => Vendor::count(),
        ];

        // Get recent orders
        $recent_orders = Order::with(['customer', 'orderItems.product'])
            ->latest()
            ->take(5)
            ->get();

        // Get low stock products
        $low_stock_products = Product::with(['branches'])
            ->whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', DB::raw('stock_threshold'));
            })
            ->take(5)
            ->get();

        // Get top selling products
        $top_products = Product::withCount(['orderItems as total_sold'])
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'recent_orders',
            'low_stock_products',
            'top_products'
        ));
    }
}