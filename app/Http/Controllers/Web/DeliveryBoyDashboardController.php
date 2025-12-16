<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryBoyDashboardController extends Controller
{
    /**
     * Display the delivery boy dashboard with delivery-focused interface.
     */
    public function index()
    {
        $user = auth()->user();

        // Only delivery boys can access this
        if (!$user->hasRole('delivery_boy')) {
            abort(403, 'Unauthorized access');
        }

        // Get assigned deliveries
        $assignedDeliveries = Delivery::with(['order.customer', 'order.branch', 'order.orderItems.product'])
            ->where('delivery_boy_id', $user->id)
            ->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])
            ->orderBy('assigned_at', 'desc')
            ->get();

        // Today's statistics
        $todayStats = [
            'total_deliveries' => Delivery::where('delivery_boy_id', $user->id)
                ->whereDate('assigned_at', Carbon::today())
                ->count(),
            'completed_deliveries' => Delivery::where('delivery_boy_id', $user->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', Carbon::today())
                ->count(),
            'pending_deliveries' => Delivery::where('delivery_boy_id', $user->id)
                ->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])
                ->count(),
            'total_earnings' => Delivery::where('delivery_boy_id', $user->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', Carbon::today())
                ->count() * 50, // Assuming 50 per delivery, adjust as needed
        ];

        // Recent deliveries (last 10)
        $recentDeliveries = Delivery::with(['order.customer', 'order.branch'])
            ->where('delivery_boy_id', $user->id)
            ->latest('assigned_at')
            ->take(10)
            ->get();

        // Delivery history for this week
        $weeklyStats = Delivery::where('delivery_boy_id', $user->id)
            ->whereBetween('assigned_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->selectRaw('DATE(assigned_at) as date, COUNT(*) as count, SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Performance metrics
        $performanceMetrics = [
            'avg_delivery_time' => Delivery::where('delivery_boy_id', $user->id)
                ->where('status', 'delivered')
                ->whereNotNull('pickup_time')
                ->whereNotNull('delivery_time')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, pickup_time, delivery_time)) as avg_time')
                ->value('avg_time') ?? 0,
            'on_time_deliveries' => Delivery::where('delivery_boy_id', $user->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', Carbon::today())
                ->count(),
            'total_deliveries_this_month' => Delivery::where('delivery_boy_id', $user->id)
                ->whereMonth('assigned_at', Carbon::now()->month)
                ->whereYear('assigned_at', Carbon::now()->year)
                ->count(),
        ];

        // Upcoming deliveries (next 5)
        $upcomingDeliveries = Delivery::with(['order.customer', 'order.branch'])
            ->where('delivery_boy_id', $user->id)
            ->whereIn('status', ['assigned', 'picked_up'])
            ->orderBy('assigned_at', 'asc')
            ->take(5)
            ->get();

        // Delivery alerts
        $deliveryAlerts = [];
        
        if ($assignedDeliveries->isEmpty()) {
            $deliveryAlerts[] = [
                'type' => 'info',
                'message' => 'No assigned deliveries at the moment.',
            ];
        }

        // Check for overdue deliveries
        $overdueDeliveries = Delivery::where('delivery_boy_id', $user->id)
            ->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])
            ->where('assigned_at', '<', Carbon::now()->subHours(2))
            ->count();

        if ($overdueDeliveries > 0) {
            $deliveryAlerts[] = [
                'type' => 'warning',
                'message' => "You have {$overdueDeliveries} delivery(ies) pending for more than 2 hours.",
            ];
        }

        return view('dashboards.delivery_boy', compact(
            'assignedDeliveries',
            'todayStats',
            'recentDeliveries',
            'weeklyStats',
            'performanceMetrics',
            'upcomingDeliveries',
            'deliveryAlerts'
        ));
    }

    /**
     * Display assigned deliveries page.
     */
    public function assignedDeliveries()
    {
        $user = auth()->user();

        // Only delivery boys can access this
        if (!$user->hasRole('delivery_boy')) {
            abort(403, 'Unauthorized access');
        }

        // Get assigned deliveries
        $assignedDeliveries = Delivery::with(['order.customer', 'order.branch', 'order.orderItems.product'])
            ->where('delivery_boy_id', $user->id)
            ->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])
            ->orderBy('assigned_at', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total_pending' => Delivery::where('delivery_boy_id', $user->id)
                ->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])
                ->count(),
            'assigned' => Delivery::where('delivery_boy_id', $user->id)
                ->where('status', 'assigned')
                ->count(),
            'in_transit' => Delivery::where('delivery_boy_id', $user->id)
                ->whereIn('status', ['picked_up', 'out_for_delivery'])
                ->count(),
        ];

        return view('delivery.assigned', compact('assignedDeliveries', 'stats'));
    }

    /**
     * Display delivery history page.
     */
    public function deliveryHistory()
    {
        $user = auth()->user();

        // Only delivery boys can access this
        if (!$user->hasRole('delivery_boy')) {
            abort(403, 'Unauthorized access');
        }

        // Get delivery history
        $deliveries = Delivery::with(['order.customer', 'order.branch', 'order.orderItems.product'])
            ->where('delivery_boy_id', $user->id)
            ->orderBy('assigned_at', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total' => Delivery::where('delivery_boy_id', $user->id)->count(),
            'completed' => Delivery::where('delivery_boy_id', $user->id)
                ->where('status', 'delivered')
                ->count(),
            'this_month' => Delivery::where('delivery_boy_id', $user->id)
                ->whereMonth('assigned_at', Carbon::now()->month)
                ->whereYear('assigned_at', Carbon::now()->year)
                ->count(),
            'this_week' => Delivery::where('delivery_boy_id', $user->id)
                ->whereBetween('assigned_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),
        ];

        return view('delivery.history', compact('deliveries', 'stats'));
    }
}
