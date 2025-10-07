<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashierDashboardController extends Controller
{
    /**
     * Display the cashier dashboard with POS-focused interface.
     */
    public function index()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account. Please contact your manager.');
        }

        // Current POS session
        $current_session = $user->currentPosSession();

        // Previous day's closing balance (fallback to last closed session if none yesterday)
        $previous_day = Carbon::yesterday();
        $previous_closing_balance = \App\Models\PosSession::where('user_id', $user->id)
            ->where('status', 'closed')
            ->whereDate('ended_at', $previous_day)
            ->orderBy('ended_at', 'desc')
            ->value('closing_cash');

        if ($previous_closing_balance === null) {
            $previous_closing_balance = \App\Models\PosSession::where('user_id', $user->id)
                ->where('status', 'closed')
                ->orderBy('ended_at', 'desc')
                ->value('closing_cash');
        }

        // Today's statistics for this cashier
        $today_stats = [
            'today_orders' => Order::where('branch_id', $branch->id)
                ->where('created_by', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'today_sales' => Order::where('branch_id', $branch->id)
                ->where('created_by', $user->id)
                ->where('status', 'completed')
                ->whereDate('created_at', Carbon::today())
                ->sum('total_amount'),
            'session_sales' => $current_session ? 
                Order::where('pos_session_id', $current_session->id)
                    ->where('status', 'completed')
                    ->sum('total_amount') : 0,
            'session_orders' => $current_session ? 
                Order::where('pos_session_id', $current_session->id)->count() : 0,
        ];

        // Branch overview (limited info for cashier)
        $branch_manager = $branch->manager;
        $branch_info = [
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address,
            'phone' => $branch->phone,
            'manager' => $branch_manager ? $branch_manager->name : 'No Manager Assigned',
            'manager_phone' => $branch_manager ? $branch_manager->phone : null,
            'total_products' => $branch->products()->where('is_active', true)->count(),
            'outlet_type' => $branch->outlet_type,
            'is_open' => $branch->isOpen(),
        ];

        // Recent orders by this cashier
        $recent_orders = Order::with(['customer', 'orderItems.product'])
            ->where('branch_id', $branch->id)
            ->where('created_by', $user->id)
            ->latest()
            ->take(10)
            ->get();

        // Available products for this branch (for quick reference)
        $available_products = Product::whereHas('branches', function($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '>', 0);
            })
            ->where('is_active', true)
            ->with(['branches' => function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            }])
            ->take(20)
            ->get()
            ->map(function($product) {
                $branchProduct = $product->branches->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'price' => $branchProduct?->pivot?->selling_price ?? $product->selling_price,
                    'stock' => $branchProduct?->pivot?->current_stock ?? 0,
                    'category' => $product->category ?? 'Uncategorized',
                ];
            });

        // Quick stats for the cashier interface
        $quick_stats = [
            'low_stock_items' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '<=', DB::raw('stock_threshold'))
                      ->where('current_stock', '>', 0);
            })->count(),
            'out_of_stock_items' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '<=', 0);
            })->count(),
            'total_customers_today' => Order::where('branch_id', $branch->id)
                ->whereDate('created_at', Carbon::today())
                ->whereNotNull('customer_id')
                ->distinct('customer_id')
                ->count(),
        ];

        // POS session history for this cashier (last 7 sessions)
        $session_history = PosSession::where('user_id', $user->id)
            ->with(['orders' => function($query) {
                $query->where('status', 'completed');
            }])
            ->latest()
            ->take(7)
            ->get()
            ->map(function($session) {
                return [
                    'id' => $session->id,
                    'started_at' => $session->started_at,
                    'ended_at' => $session->ended_at,
                    'duration' => $session->ended_at ? 
                        $session->started_at->diffForHumans($session->ended_at, true) : 
                        $session->started_at->diffForHumans(),
                    'total_orders' => $session->orders->count(),
                    'total_sales' => $session->orders->sum('total_amount'),
                    'status' => $session->status,
                ];
            });

        // Today's hourly sales for visual representation
        $hourly_sales = Order::where('branch_id', $branch->id)
            ->where('created_by', $user->id)
            ->where('status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Recent customers (for quick customer lookup)
        $recent_customers = Customer::whereHas('orders', function($query) use ($branch, $user) {
                $query->where('branch_id', $branch->id)
                      ->where('created_by', $user->id);
            })
            ->withCount(['orders' => function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            }])
            ->latest('updated_at')
            ->take(10)
            ->get();

        // POS alerts and notifications
        $pos_alerts = [];
        
        if (!$current_session) {
            $pos_alerts[] = [
                'type' => 'warning',
                'message' => 'No active POS session. Start a session to begin sales.',
                'action' => 'Start Session',
                'url' => route('pos.sessions.create')
            ];
        }
        
        if ($quick_stats['out_of_stock_items'] > 0) {
            $pos_alerts[] = [
                'type' => 'info',
                'message' => "{$quick_stats['out_of_stock_items']} item(s) are out of stock",
                'action' => 'View Inventory',
                'url' => route('inventory.index')
            ];
        }

        // Session performance metrics
        $session_metrics = [];
        if ($current_session) {
            $session_duration = now()->diffInMinutes($current_session->started_at);
            $avg_order_value = $today_stats['session_orders'] > 0 ? 
                $today_stats['session_sales'] / $today_stats['session_orders'] : 0;
            
            $session_metrics = [
                'duration_minutes' => $session_duration,
                'duration_formatted' => $current_session->started_at->diffForHumans(),
                'avg_order_value' => $avg_order_value,
                'orders_per_hour' => $session_duration > 0 ? 
                    round(($today_stats['session_orders'] / $session_duration) * 60, 1) : 0,
            ];
        }

        return view('dashboards.cashier', compact(
            'today_stats',
            'branch_info',
            'current_session',
            'recent_orders',
            'available_products',
            'quick_stats',
            'session_history',
            'hourly_sales',
            'recent_customers',
            'pos_alerts',
            'session_metrics',
            'previous_closing_balance'
        ));
    }

    /**
     * Get POS-specific data for AJAX calls
     */
    public function getPosData()
    {
        $user = auth()->user();
        $branch = $user->branch;
        $current_session = $user->currentPosSession();

        return response()->json([
            'session_active' => $current_session ? true : false,
            'session_id' => $current_session?->id,
            'session_start_time' => $current_session?->started_at,
            'session_sales' => $current_session ? 
                Order::where('pos_session_id', $current_session->id)
                    ->where('status', 'completed')
                    ->sum('total_amount') : 0,
            'branch_products_count' => $branch->products()->where('is_active', true)->count(),
        ]);
    }
}