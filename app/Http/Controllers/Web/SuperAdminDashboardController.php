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
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminDashboardController extends Controller
{
    /**
     * Display the super admin dashboard with system-wide metrics and controls.
     */
    public function index()
    {
        // System-wide statistics
        $stats = [
            'total_users' => User::count(),
            'total_branches' => Branch::count(),
            'total_admins' => User::whereHas('role', fn($q) => $q->where('name', 'admin'))->count(),
            'total_branch_managers' => User::whereHas('role', fn($q) => $q->where('name', 'branch_manager'))->count(),
            'total_cashiers' => User::whereHas('role', fn($q) => $q->where('name', 'cashier'))->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_customers' => Customer::count(),
            'total_vendors' => Vendor::count(),
            'pending_purchase_orders' => PurchaseOrder::where('status', 'pending')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'monthly_revenue' => Order::where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount'),
            'total_expenses' => Expense::sum('amount'),
        ];

        // System health metrics
        $system_health = [
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'active_branches' => Branch::where('is_active', true)->count(),
            'recent_logins' => User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', Carbon::now()->subDays(7))
                ->count(),
        ];

        // Branch performance overview
        $branch_performance = Branch::withCount(['orders', 'users'])
            ->withSum('orders', 'total_amount')
            ->get()
            ->map(function($branch) {
                $manager = $branch->manager;
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                    'location' => $branch->address,
                    'manager' => $manager ? $manager->name : 'No Manager',
                    'manager_id' => $manager ? $manager->id : null,
                    'total_orders' => $branch->orders_count,
                    'total_revenue' => $branch->orders_sum_total_amount ?? 0,
                    'total_staff' => $branch->users_count,
                    'cashiers_count' => $branch->cashiers()->count(),
                    'delivery_count' => $branch->deliveryStaff()->count(),
                    'today_sales' => $branch->todaySales(),
                    'active_pos_sessions' => $branch->activePosSessionsCount(),
                    'status' => $branch->is_active ? 'Active' : 'Inactive',
                    'outlet_type' => $branch->outlet_type,
                    'pos_enabled' => $branch->pos_enabled,
                ];
            });

        // Recent system activities
        $recent_activities = collect()
            ->merge(User::latest()->take(3)->get()->map(function($user) {
                return [
                    'type' => 'user_created',
                    'message' => "New user created: {$user->name} ({$user->role->display_name})",
                    'time' => $user->created_at,
                    'icon' => 'user-plus',
                    'color' => 'blue'
                ];
            }))
            ->merge(Branch::latest()->take(2)->get()->map(function($branch) {
                return [
                    'type' => 'branch_created',
                    'message' => "New branch created: {$branch->name}",
                    'time' => $branch->created_at,
                    'icon' => 'building',
                    'color' => 'green'
                ];
            }))
            ->merge(Order::with('customer')->latest()->take(5)->get()->map(function($order) {
                return [
                    'type' => 'order',
                    'message' => "Order #{$order->id} - " . ($order->customer->name ?? 'Walk-in Customer'),
                    'time' => $order->created_at,
                    'icon' => 'shopping-cart',
                    'color' => 'purple'
                ];
            }))
            ->sortByDesc('time')
            ->take(10);

        // User role distribution
        $role_distribution = Role::withCount('users')
            ->get()
            ->map(function($role) {
                return [
                    'name' => $role->display_name,
                    'count' => $role->users_count,
                    'percentage' => $role->users_count > 0 ? round(($role->users_count / User::count()) * 100, 1) : 0
                ];
            });

        // Revenue analytics by branch
        $revenue_by_branch = Branch::withSum(['orders' => function($query) {
                $query->where('status', 'completed')
                      ->whereMonth('created_at', Carbon::now()->month);
            }], 'total_amount')
            ->get()
            ->map(function($branch) {
                return [
                    'branch_name' => $branch->name,
                    'revenue' => $branch->orders_sum_total_amount ?? 0
                ];
            });

        // Pending management tasks
        $pending_tasks = [
            'branches_without_manager' => Branch::active()->whereDoesntHave('users', function($q) {
                $q->whereHas('role', function($r) {
                    $r->where('name', 'branch_manager');
                });
            })->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'low_stock_branches' => Branch::whereHas('products', function($q) {
                $q->wherePivot('current_stock', '<', 10);
            })->count(),
            'pending_purchase_orders' => PurchaseOrder::where('status', 'pending')->count(),
        ];

        // System alerts
        $system_alerts = [];
        
        if ($pending_tasks['branches_without_manager'] > 0) {
            $system_alerts[] = [
                'type' => 'warning',
                'message' => "{$pending_tasks['branches_without_manager']} branch(es) without assigned manager",
                'action' => 'Assign managers',
                'url' => route('branches.index')
            ];
        }
        
        if ($pending_tasks['inactive_users'] > 0) {
            $system_alerts[] = [
                'type' => 'info',
                'message' => "{$pending_tasks['inactive_users']} inactive user(s) in system",
                'action' => 'Review users',
                'url' => route('users.index')
            ];
        }

        return view('dashboards.super_admin', compact(
            'stats',
            'system_health',
            'branch_performance',
            'recent_activities',
            'role_distribution',
            'revenue_by_branch',
            'pending_tasks',
            'system_alerts'
        ));
    }
}