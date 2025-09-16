@extends('layouts.app')

@section('title', 'Order Workflow Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Order Workflow Dashboard</h1>
            <p class="text-gray-600 mt-2">Monitor and manage order processing workflow</p>
        </div>
        <div class="flex gap-4">
            <button onclick="refreshDashboard()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <div class="flex gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Branches</option>
                    <!-- Add branch options here -->
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($stats as $status => $data)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ $data['config']['name'] }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $data['count'] }}</p>
                        <p class="text-sm text-gray-500">{{ $data['percentage'] }}% of total</p>
                    </div>
                    <div class="p-3 rounded-full bg-{{ $data['config']['color'] }}-100">
                        <svg class="w-6 h-6 text-{{ $data['config']['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <!-- Add appropriate icon based on $data['config']['icon'] -->
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Processing Times -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Average Processing Times</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="text-center">
                <p class="text-sm text-gray-600">Order to Confirmed</p>
                <p class="text-xl font-bold text-blue-600">{{ $processingTimes['order_to_confirmed'] }}m</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Confirmed to Processing</p>
                <p class="text-xl font-bold text-orange-600">{{ $processingTimes['confirmed_to_processing'] }}m</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Processing to Ready</p>
                <p class="text-xl font-bold text-yellow-600">{{ $processingTimes['processing_to_ready'] }}m</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Ready to Delivered</p>
                <p class="text-xl font-bold text-green-600">{{ $processingTimes['ready_to_delivered'] }}m</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Total Cycle Time</p>
                <p class="text-xl font-bold text-purple-600">{{ $processingTimes['total_processing_time'] }}m</p>
            </div>
        </div>
    </div>

    <!-- Recent Orders by Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @foreach(['pending', 'processing', 'ready', 'delivered'] as $status)
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 capitalize">{{ $status }} Orders</h3>
                    <p class="text-sm text-gray-600">{{ $recentOrders[$status]->count() }} orders</p>
                </div>
                <div class="p-6">
                    @if($recentOrders[$status]->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentOrders[$status] as $order)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
                                        <p class="text-sm text-gray-600">{{ $order->customer->name ?? 'Walk-in' }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($order->is_urgent)
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Urgent</span>
                                        @endif
                                        <span class="px-2 py-1 text-xs font-medium bg-{{ $order->getWorkflowStatusInfo()['color'] }}-100 text-{{ $order->getWorkflowStatusInfo()['color'] }}-800 rounded-full">
                                            {{ ucfirst($order->priority) }}
                                        </span>
                                        <a href="{{ route('orders.workflow.show', $order) }}" class="text-blue-600 hover:text-blue-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No {{ $status }} orders</h3>
                            <p class="mt-1 text-sm text-gray-500">No orders in {{ $status }} status currently.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
function refreshDashboard() {
    window.location.reload();
}

// Auto-refresh every 30 seconds
setInterval(refreshDashboard, 30000);
</script>
@endsection