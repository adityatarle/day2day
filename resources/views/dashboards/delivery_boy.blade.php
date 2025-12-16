@extends('layouts.app')

@section('title', 'Delivery Boy Dashboard')

@section('content')
<div class="p-6 space-y-6 bg-gray-50 min-h-screen">
    <!-- Welcome Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-motorcycle text-xl text-blue-700"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">
                            Delivery Dashboard
                        </h1>
                        <p class="text-gray-600 text-sm">Welcome back, {{ Auth::user()->name }}!</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4 mt-4">
                    <div class="flex items-center space-x-2 bg-blue-50 rounded-md px-3 py-1.5 border border-blue-200">
                        <i class="fas fa-box text-blue-600 text-xs"></i>
                        <span class="text-sm font-medium text-blue-800">{{ $todayStats['pending_deliveries'] }} Pending</span>
                    </div>
                    <div class="flex items-center space-x-2 bg-green-50 rounded-md px-3 py-1.5 border border-green-200">
                        <i class="fas fa-check-circle text-green-600 text-xs"></i>
                        <span class="text-sm font-medium text-green-800">{{ $todayStats['completed_deliveries'] }} Completed Today</span>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="text-right space-y-1">
                    <div class="text-2xl font-semibold text-gray-900" id="ist-time">
                        {{ Carbon\Carbon::now('Asia/Kolkata')->format('H:i') }}
                    </div>
                    <div class="text-gray-600 text-sm" id="ist-date">
                        {{ Carbon\Carbon::now('Asia/Kolkata')->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Deliveries -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Deliveries</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $todayStats['total_deliveries'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Today</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Completed Deliveries -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $todayStats['completed_deliveries'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Today</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Deliveries -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $todayStats['pending_deliveries'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Active</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Earnings -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Earnings</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">₹{{ number_format($todayStats['total_earnings'], 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Estimated</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(count($deliveryAlerts) > 0)
    <div class="space-y-3">
        @foreach($deliveryAlerts as $alert)
        <div class="bg-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-50 border border-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-{{ $alert['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle' }} text-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-600 mt-0.5 mr-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-800">
                        {{ $alert['message'] }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Assigned Deliveries -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list mr-2 text-blue-600"></i>
                    Assigned Deliveries
                </h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                    {{ $assignedDeliveries->count() }} Active
                </span>
            </div>
        </div>
        <div class="p-6">
            @if($assignedDeliveries->count() > 0)
            <div class="space-y-4">
                @foreach($assignedDeliveries as $delivery)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="bg-{{ $delivery->status === 'delivered' ? 'green' : ($delivery->status === 'out_for_delivery' ? 'blue' : 'orange') }}-100 text-{{ $delivery->status === 'delivered' ? 'green' : ($delivery->status === 'out_for_delivery' ? 'blue' : 'orange') }}-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                                <span class="text-sm font-medium text-gray-900">
                                    Order #{{ $delivery->order->order_number ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="space-y-1 text-sm text-gray-600">
                                <p><i class="fas fa-user mr-2"></i><strong>Customer:</strong> {{ $delivery->order->customer->name ?? 'N/A' }}</p>
                                <p><i class="fas fa-phone mr-2"></i><strong>Phone:</strong> {{ $delivery->order->customer->phone ?? 'N/A' }}</p>
                                <p><i class="fas fa-map-marker-alt mr-2"></i><strong>Address:</strong> {{ $delivery->order->customer->address ?? 'N/A' }}</p>
                                <p><i class="fas fa-store mr-2"></i><strong>Branch:</strong> {{ $delivery->order->branch->name ?? 'N/A' }}</p>
                                <p><i class="fas fa-rupee-sign mr-2"></i><strong>Amount:</strong> ₹{{ number_format($delivery->order->total_amount ?? 0, 2) }}</p>
                                @if($delivery->assigned_at)
                                <p><i class="fas fa-clock mr-2"></i><strong>Assigned:</strong> {{ $delivery->assigned_at->format('M d, Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4 flex flex-col space-y-2">
                            @if($delivery->status === 'assigned')
                            <button onclick="startDelivery({{ $delivery->order->id }})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-play mr-1"></i>Start Delivery
                            </button>
                            @elseif($delivery->status === 'out_for_delivery' || $delivery->status === 'picked_up')
                            <button onclick="completeDelivery({{ $delivery->order->id }})" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-check mr-1"></i>Complete Delivery
                            </button>
                            @endif
                            <a href="{{ route('orders.show', $delivery->order->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300">
                                <i class="fas fa-eye mr-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <i class="fas fa-box-open text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 font-medium">No assigned deliveries at the moment</p>
                <p class="text-sm text-gray-500 mt-1">New deliveries will appear here when assigned to you</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600">Average Delivery Time</h3>
                <i class="fas fa-stopwatch text-gray-400"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ round($performanceMetrics['avg_delivery_time']) }} min</p>
            <p class="text-xs text-gray-500 mt-1">Based on completed deliveries</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600">This Month</h3>
                <i class="fas fa-calendar text-gray-400"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $performanceMetrics['total_deliveries_this_month'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total deliveries</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600">On-Time Rate</h3>
                <i class="fas fa-clock text-gray-400"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $performanceMetrics['on_time_deliveries'] }}</p>
            <p class="text-xs text-gray-500 mt-1">On-time today</p>
        </div>
    </div>

    <!-- Recent Deliveries -->
    @if($recentDeliveries->count() > 0)
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history mr-2 text-gray-600"></i>
                Recent Deliveries
            </h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentDeliveries as $delivery)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $delivery->order->order_number ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ $delivery->order->customer->name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="bg-{{ $delivery->status === 'delivered' ? 'green' : 'orange' }}-100 text-{{ $delivery->status === 'delivered' ? 'green' : 'orange' }}-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ $delivery->assigned_at ? $delivery->assigned_at->format('M d, H:i') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₹{{ number_format($delivery->order->total_amount ?? 0, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    // Update time every minute
    setInterval(function() {
        const now = new Date();
        const istTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Kolkata"}));
        document.getElementById('ist-time').textContent = istTime.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: false});
        document.getElementById('ist-date').textContent = istTime.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
    }, 60000);

    // Delivery actions (using API endpoints)
    function startDelivery(orderId) {
        if (confirm('Start delivery for this order?')) {
            fetch(`/api/delivery/orders/${orderId}/start`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to start delivery'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    }

    function completeDelivery(orderId) {
        if (confirm('Mark this delivery as completed?')) {
            fetch(`/api/delivery/orders/${orderId}/process`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    delivery_status: 'delivered',
                    delivery_notes: 'Completed via web dashboard'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to complete delivery'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    }
</script>
@endsection
