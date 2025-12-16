@extends('layouts.app')

@section('title', 'Assigned Deliveries')

@section('content')
<div class="p-6 space-y-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Assigned Deliveries</h1>
                <p class="text-gray-600 mt-1">Manage your assigned delivery orders</p>
            </div>
            <a href="{{ route('delivery.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pending</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_pending'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Assigned</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['assigned'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">In Transit</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['in_transit'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-truck text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Deliveries List -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list mr-2 text-blue-600"></i>
                My Assigned Deliveries
            </h2>
        </div>
        <div class="p-6">
            @if($assignedDeliveries->count() > 0)
            <div class="space-y-4">
                @foreach($assignedDeliveries as $delivery)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-3">
                                <span class="bg-{{ $delivery->status === 'delivered' ? 'green' : ($delivery->status === 'out_for_delivery' ? 'blue' : 'orange') }}-100 text-{{ $delivery->status === 'delivered' ? 'green' : ($delivery->status === 'out_for_delivery' ? 'blue' : 'orange') }}-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                                <span class="text-sm font-medium text-gray-900">
                                    Order #{{ $delivery->order->order_number ?? 'N/A' }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ $delivery->assigned_at ? $delivery->assigned_at->format('M d, Y H:i') : 'N/A' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                <div>
                                    <p><i class="fas fa-user mr-2"></i><strong>Customer:</strong> {{ $delivery->order->customer->name ?? 'N/A' }}</p>
                                    <p class="mt-1"><i class="fas fa-phone mr-2"></i><strong>Phone:</strong> {{ $delivery->order->customer->phone ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p><i class="fas fa-map-marker-alt mr-2"></i><strong>Address:</strong> {{ $delivery->order->customer->address ?? 'N/A' }}</p>
                                    <p class="mt-1"><i class="fas fa-store mr-2"></i><strong>Branch:</strong> {{ $delivery->order->branch->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p><i class="fas fa-rupee-sign mr-2"></i><strong>Amount:</strong> ₹{{ number_format($delivery->order->total_amount ?? 0, 2) }}</p>
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
                            <a href="{{ route('orders.show', $delivery->order->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300 text-center">
                                <i class="fas fa-eye mr-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $assignedDeliveries->links() }}
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
</div>

<script>
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
