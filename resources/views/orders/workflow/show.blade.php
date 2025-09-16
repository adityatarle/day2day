@extends('layouts.app')

@section('title', 'Order Workflow - ' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('orders.workflow.dashboard') }}" class="text-gray-600 hover:text-gray-800">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Order Workflow</h1>
            <p class="text-gray-600">{{ $order->order_number }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Current Status -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Current Status</h2>
                    <div class="flex items-center gap-2">
                        @if($order->is_urgent)
                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Urgent</span>
                        @endif
                        <span class="px-2 py-1 text-xs font-medium bg-{{ $statusInfo['color'] }}-100 text-{{ $statusInfo['color'] }}-800 rounded-full">
                            {{ ucfirst($order->priority) }}
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-full bg-{{ $statusInfo['color'] }}-100">
                        <svg class="w-8 h-8 text-{{ $statusInfo['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <!-- Add appropriate icon based on $statusInfo['icon'] -->
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $statusInfo['name'] }}</h3>
                        <p class="text-gray-600">{{ $statusInfo['description'] }}</p>
                    </div>
                </div>

                <!-- Workflow Progress -->
                <div class="mt-6">
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                        <span>Progress</span>
                        <span>{{ $this->getProgressPercentage() }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-{{ $statusInfo['color'] }}-600 h-2 rounded-full" style="width: {{ $this->getProgressPercentage() }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Items</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->orderItems as $item)
                                <tr>
                                    <td class="px-4 py-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                    </td>
                                    <td class="px-4 py-2 text-right text-sm text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-4 py-2 text-right text-sm text-gray-900">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-gray-900">₹{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Workflow History -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Workflow History</h2>
                <div class="space-y-4">
                    @foreach($workflowHistory as $log)
                        <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                            <div class="p-2 rounded-full bg-{{ $log->to_status === $order->status ? 'blue' : 'gray' }}-100">
                                <svg class="w-5 h-5 text-{{ $log->to_status === $order->status ? 'blue' : 'gray' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <!-- Add appropriate icon -->
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900">{{ $log->to_status_name }}</h4>
                                    <span class="text-sm text-gray-500">{{ $log->duration }}</span>
                                </div>
                                @if($log->user)
                                    <p class="text-sm text-gray-600">by {{ $log->user->name }}</p>
                                @endif
                                @if($log->notes)
                                    <p class="text-sm text-gray-500 mt-1">{{ $log->notes }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Order Number</span>
                        <span class="font-medium">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Customer</span>
                        <span class="font-medium">{{ $order->customer->name ?? 'Walk-in' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Branch</span>
                        <span class="font-medium">{{ $order->branch->name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Order Type</span>
                        <span class="font-medium capitalize">{{ $order->order_type }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Payment Method</span>
                        <span class="font-medium capitalize">{{ $order->payment_method }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Payment Status</span>
                        <span class="font-medium capitalize">{{ $order->payment_status }}</span>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">₹{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax</span>
                            <span class="font-medium">₹{{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Discount</span>
                            <span class="font-medium">-₹{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold border-t pt-2">
                            <span>Total</span>
                            <span>₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Actions -->
            @if(count($possibleTransitions) > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Workflow Actions</h3>
                    <div class="space-y-3">
                        @foreach($possibleTransitions as $status => $config)
                            <button onclick="transitionOrder('{{ $status }}')" 
                                    class="w-full text-left p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-full bg-{{ $config['color'] }}-100">
                                        <svg class="w-4 h-4 text-{{ $config['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <!-- Add appropriate icon -->
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $config['name'] }}</h4>
                                        <p class="text-sm text-gray-600">{{ $config['description'] }}</p>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quality Check -->
            @if($order->status === 'ready' && !$order->quality_checked)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quality Check</h3>
                    <div class="space-y-3">
                        <button onclick="showQualityCheckModal()" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                            Mark as Quality Checked
                        </button>
                        <button onclick="showQualityCheckModal(false)" 
                                class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            Failed Quality Check
                        </button>
                    </div>
                </div>
            @endif

            <!-- Performance Metrics -->
            @if($order->delivered_at)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
                    <div class="space-y-3">
                        @if($order->processing_time_minutes)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Processing Time</span>
                                <span class="font-medium">{{ $order->processing_time_minutes }}m</span>
                            </div>
                        @endif
                        @if($order->delivery_time_minutes)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Time</span>
                                <span class="font-medium">{{ $order->delivery_time_minutes }}m</span>
                            </div>
                        @endif
                        @if($order->total_cycle_time_minutes)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total Cycle Time</span>
                                <span class="font-medium">{{ $order->total_cycle_time_minutes }}m</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Transition Modal -->
<div id="transitionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Transition Order</h3>
            </div>
            <form id="transitionForm" class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add notes for this transition..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeTransitionModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Confirm Transition
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quality Check Modal -->
<div id="qualityCheckModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Quality Check</h3>
            </div>
            <form id="qualityCheckForm" class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add quality check notes..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeQualityCheckModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                        Confirm Quality Check
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentTransition = null;

function transitionOrder(status) {
    currentTransition = status;
    document.getElementById('transitionModal').classList.remove('hidden');
}

function closeTransitionModal() {
    document.getElementById('transitionModal').classList.add('hidden');
    currentTransition = null;
}

function showQualityCheckModal(passed = true) {
    const form = document.getElementById('qualityCheckForm');
    form.dataset.passed = passed;
    document.getElementById('qualityCheckModal').classList.remove('hidden');
}

function closeQualityCheckModal() {
    document.getElementById('qualityCheckModal').classList.add('hidden');
}

// Transition form submission
document.getElementById('transitionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        status: currentTransition,
        notes: formData.get('notes'),
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    try {
        const response = await fetch(`/orders/{{ $order->id }}/workflow/transition`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': data._token
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('An error occurred while transitioning the order');
    }
});

// Quality check form submission
document.getElementById('qualityCheckForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        passed: this.dataset.passed === 'true',
        notes: formData.get('notes'),
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    try {
        const response = await fetch(`/orders/{{ $order->id }}/workflow/quality-check`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': data._token
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('An error occurred while processing quality check');
    }
});
</script>
@endsection