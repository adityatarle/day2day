<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SystemMonitoringController extends Controller
{
    /**
     * Get real-time system status for Super Admin.
     */
    public function getSystemStatus()
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $status = [
            'timestamp' => now()->toISOString(),
            'active_users' => User::where('last_login_at', '>=', now()->subMinutes(15))->count(),
            'active_pos_sessions' => PosSession::where('status', 'active')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'low_stock_alerts' => Product::whereHas('branches', function($q) {
                $q->where('current_stock', '<=', 10);
            })->count(),
            'system_health' => [
                'database_status' => 'healthy',
                'cache_status' => 'healthy',
                'storage_status' => 'healthy',
            ],
            'recent_activities' => $this->getRecentActivities(),
        ];

        return response()->json($status);
    }

    /**
     * Get real-time branch status for Branch Manager.
     */
    public function getBranchStatus()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return response()->json(['error' => 'No branch assigned.'], 400);
        }

        $status = [
            'timestamp' => now()->toISOString(),
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'active_staff' => $branch->users()->where('last_login_at', '>=', now()->subMinutes(15))->count(),
            'active_pos_sessions' => $branch->activePosSessionsCount(),
            'today_orders' => $branch->orders()->whereDate('created_at', today())->count(),
            'today_sales' => $branch->todaySales(),
            'current_hour_orders' => $branch->orders()
                ->where('created_at', '>=', now()->startOfHour())
                ->count(),
            'inventory_alerts' => [
                'low_stock' => $branch->products()->wherePivot('current_stock', '<=', 10)->count(),
                'out_of_stock' => $branch->products()->wherePivot('current_stock', '<=', 0)->count(),
            ],
            'staff_status' => $this->getBranchStaffStatus($branch),
        ];

        return response()->json($status);
    }

    /**
     * Get real-time POS status for Cashier.
     */
    public function getPosStatus()
    {
        $user = auth()->user();
        $currentSession = $user->currentPosSession();

        $status = [
            'timestamp' => now()->toISOString(),
            'user_id' => $user->id,
            'session_active' => $currentSession ? true : false,
            'session_id' => $currentSession?->id,
            'session_duration' => $currentSession ? 
                $currentSession->started_at->diffForHumans() : null,
            'session_sales' => $currentSession ? 
                $currentSession->orders()->where('status', 'completed')->sum('total_amount') : 0,
            'session_orders' => $currentSession ? 
                $currentSession->orders()->count() : 0,
            'last_order_time' => $currentSession ? 
                $currentSession->orders()->latest()->first()?->created_at : null,
        ];

        return response()->json($status);
    }

    /**
     * Get real-time sales data.
     */
    public function getSalesData(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'today'); // today, week, month
        
        $query = Order::where('status', 'completed');
        
        // Apply branch filter based on user role
        if ($user->isBranchManager() || $user->isCashier()) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply date filter
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
        }

        $salesData = [
            'total_sales' => $query->sum('total_amount'),
            'total_orders' => $query->count(),
            'avg_order_value' => $query->avg('total_amount'),
            'hourly_breakdown' => $query
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders, SUM(total_amount) as sales')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
        ];

        if ($user->isSuperAdmin()) {
            $salesData['branch_breakdown'] = $query
                ->join('branches', 'orders.branch_id', '=', 'branches.id')
                ->selectRaw('branches.name, branches.id, COUNT(orders.id) as orders, SUM(orders.total_amount) as sales')
                ->groupBy('branches.id', 'branches.name')
                ->orderByDesc('sales')
                ->get();
        }

        return response()->json($salesData);
    }

    /**
     * Get inventory alerts.
     */
    public function getInventoryAlerts()
    {
        $user = auth()->user();
        
        $query = Product::with(['branches' => function($q) use ($user) {
            if ($user->isBranchManager() || $user->isCashier()) {
                $q->where('branch_id', $user->branch_id);
            }
        }]);

        $alerts = [
            'low_stock' => $query->whereHas('branches', function($q) use ($user) {
                if ($user->isBranchManager() || $user->isCashier()) {
                    $q->where('branch_id', $user->branch_id);
                }
                $q->where('current_stock', '<=', 10)->where('current_stock', '>', 0);
            })->get()->map(function($product) {
                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $product->branches->first()->pivot->current_stock ?? 0,
                    'branch' => $product->branches->first()->name ?? 'Unknown',
                ];
            }),
            
            'out_of_stock' => $query->whereHas('branches', function($q) use ($user) {
                if ($user->isBranchManager() || $user->isCashier()) {
                    $q->where('branch_id', $user->branch_id);
                }
                $q->where('current_stock', '<=', 0);
            })->get()->map(function($product) {
                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'branch' => $product->branches->first()->name ?? 'Unknown',
                ];
            }),
        ];

        return response()->json($alerts);
    }

    /**
     * Get user activity data.
     */
    public function getUserActivity()
    {
        $user = auth()->user();
        
        $query = User::with(['role', 'branch']);
        
        if ($user->isBranchManager()) {
            $query->where('branch_id', $user->branch_id);
        }

        $activity = [
            'online_users' => $query->where('last_login_at', '>=', now()->subMinutes(15))
                ->get()->map(function($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'role' => $u->role->display_name,
                        'branch' => $u->branch->name ?? 'No Branch',
                        'last_seen' => $u->last_login_at->diffForHumans(),
                        'active_session' => $u->currentPosSession() ? true : false,
                    ];
                }),
            
            'recent_logins' => $query->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subHours(24))
                ->orderBy('last_login_at', 'desc')
                ->limit(10)
                ->get()->map(function($u) {
                    return [
                        'name' => $u->name,
                        'role' => $u->role->display_name,
                        'branch' => $u->branch->name ?? 'No Branch',
                        'login_time' => $u->last_login_at->diffForHumans(),
                    ];
                }),
        ];

        return response()->json($activity);
    }

    /**
     * Get branch performance comparison.
     */
    public function getBranchPerformance()
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $branches = Branch::with(['orders' => function($q) {
            $q->where('status', 'completed')->whereDate('created_at', today());
        }])->get()->map(function($branch) {
            $manager = $branch->manager();
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'manager' => $manager ? $manager->name : 'No Manager',
                'today_sales' => $branch->todaySales(),
                'today_orders' => $branch->orders()->whereDate('created_at', today())->count(),
                'active_sessions' => $branch->activePosSessionsCount(),
                'staff_count' => $branch->users()->count(),
                'status' => $branch->is_active ? 'Active' : 'Inactive',
            ];
        });

        return response()->json($branches);
    }

    /**
     * Get recent system activities.
     */
    private function getRecentActivities()
    {
        return collect()
            ->merge(User::latest()->take(3)->get()->map(function($user) {
                return [
                    'type' => 'user_activity',
                    'message' => "User {$user->name} logged in",
                    'time' => $user->last_login_at,
                    'icon' => 'user',
                ];
            }))
            ->merge(Order::with('customer')->latest()->take(5)->get()->map(function($order) {
                return [
                    'type' => 'order',
                    'message' => "Order #{$order->id} - " . ($order->customer->name ?? 'Walk-in'),
                    'time' => $order->created_at,
                    'icon' => 'shopping-cart',
                ];
            }))
            ->sortByDesc('time')
            ->take(10)
            ->values();
    }

    /**
     * Get branch staff status.
     */
    private function getBranchStaffStatus(Branch $branch)
    {
        return $branch->users()->with(['role', 'posSessions' => function($q) {
            $q->where('status', 'active');
        }])->get()->map(function($staff) {
            return [
                'id' => $staff->id,
                'name' => $staff->name,
                'role' => $staff->role->display_name,
                'online' => $staff->last_login_at && $staff->last_login_at >= now()->subMinutes(15),
                'has_active_session' => $staff->posSessions->isNotEmpty(),
                'last_seen' => $staff->last_login_at ? $staff->last_login_at->diffForHumans() : 'Never',
            ];
        });
    }
}