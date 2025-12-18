<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\PosSession;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    /**
     * Get dashboard data for authenticated user
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'No branch assigned to your account'
            ], 404);
        }

        // Date range (default to today)
        $startDate = $request->start_date ?? now()->startOfDay();
        $endDate = $request->end_date ?? now()->endOfDay();

        // Today's summary
        $todaySummary = [
            'orders' => [
                'total' => Order::where('branch_id', $branch->id)
                    ->whereDate('created_at', today())
                    ->count(),
                'completed' => Order::where('branch_id', $branch->id)
                    ->whereDate('created_at', today())
                    ->where('status', 'completed')
                    ->count(),
                'pending' => Order::where('branch_id', $branch->id)
                    ->whereDate('created_at', today())
                    ->whereIn('status', ['pending', 'confirmed', 'processing'])
                    ->count(),
            ],
            'sales' => [
                'total' => (float) Order::where('branch_id', $branch->id)
                    ->whereDate('created_at', today())
                    ->where('status', 'completed')
                    ->sum('total_amount'),
                'cash' => (float) Order::where('branch_id', $branch->id)
                    ->whereDate('created_at', today())
                    ->where('payment_method', 'cash')
                    ->where('status', 'completed')
                    ->sum('total_amount'),
                'card' => (float) Order::where('branch_id', $branch->id)
                    ->whereDate('created_at', today())
                    ->where('payment_method', 'card')
                    ->where('status', 'completed')
                    ->sum('total_amount'),
                'upi' => (float) Order::where('branch_id', $branch->id)
                    ->whereDate('created_at', today())
                    ->where('payment_method', 'upi')
                    ->where('status', 'completed')
                    ->sum('total_amount'),
            ],
        ];

        // Active POS session
        $activeSession = PosSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // Recent orders
        $recentOrders = Order::where('branch_id', $branch->id)
            ->with(['customer:id,name,phone', 'orderItems.product:id,name'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer?->name ?? 'Walk-in',
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'order_date' => $order->order_date?->toISOString(),
                ];
            });

        // Low stock products
        $lowStockProducts = Product::whereHas('branches', function($query) use ($branch) {
            $query->where('branch_id', $branch->id)
                  ->whereColumn('current_stock', '<=', 'products.stock_threshold');
        })
        ->where('is_active', true)
        ->with(['branches' => function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        }])
        ->limit(10)
        ->get()
        ->map(function ($product) use ($branch) {
            $branchProduct = $product->branches->first();
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'current_stock' => (float) ($branchProduct?->pivot?->current_stock ?? 0),
                'stock_threshold' => (float) $product->stock_threshold,
                'weight_unit' => $product->weight_unit,
            ];
        });

        // Top selling products (last 7 days)
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.branch_id', $branch->id)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [now()->subDays(7), now()])
            ->select(
                'products.id',
                'products.name',
                'products.code',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_sales')
            )
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                ],
                'today_summary' => $todaySummary,
                'active_pos_session' => $activeSession ? [
                    'id' => $activeSession->id,
                    'terminal_id' => $activeSession->terminal_id,
                    'total_sales' => (float) $activeSession->total_sales,
                    'total_transactions' => (int) $activeSession->total_transactions,
                    'started_at' => $activeSession->started_at?->toISOString(),
                ] : null,
                'recent_orders' => $recentOrders,
                'low_stock_products' => $lowStockProducts,
                'top_products' => $topProducts,
            ],
        ]);
    }

    /**
     * Get sales chart data
     */
    public function salesChart(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'No branch assigned to your account'
            ], 404);
        }

        $period = $request->period ?? 'week'; // week, month, year
        $data = [];

        switch ($period) {
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $sales = Order::where('branch_id', $branch->id)
                        ->whereDate('created_at', $date)
                        ->where('status', 'completed')
                        ->sum('total_amount');
                    
                    $data[] = [
                        'date' => $date->format('Y-m-d'),
                        'label' => $date->format('D'),
                        'sales' => (float) $sales,
                    ];
                }
                break;

            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $sales = Order::where('branch_id', $branch->id)
                        ->whereDate('created_at', $date)
                        ->where('status', 'completed')
                        ->sum('total_amount');
                    
                    $data[] = [
                        'date' => $date->format('Y-m-d'),
                        'label' => $date->format('M d'),
                        'sales' => (float) $sales,
                    ];
                }
                break;

            case 'year':
                for ($i = 11; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $sales = Order::where('branch_id', $branch->id)
                        ->whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->where('status', 'completed')
                        ->sum('total_amount');
                    
                    $data[] = [
                        'date' => $month->format('Y-m'),
                        'label' => $month->format('M Y'),
                        'sales' => (float) $sales,
                    ];
                }
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
