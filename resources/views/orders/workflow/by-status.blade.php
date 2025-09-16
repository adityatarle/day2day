@extends('layouts.app')

@section('title', ucfirst($status) . ' Orders')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ ucfirst($status) }} Orders</h1>
            <p class="text-gray-600 mt-2">Manage orders in {{ $status }} status</p>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('orders.workflow.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                Back to Dashboard
            </a>
            @if(count($orders) > 0)
                <button onclick="showBulkActions()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Bulk Actions
                </button>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Urgent Only</label>
                <select name="urgent" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Orders</option>
                    <option value="1" {{ request('urgent') == '1' ? 'selected' : '' }}>Urgent Only</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Orders List -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50 {{ $order->is_urgent ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $order->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                                            <div class="text-sm text-gray-500">#{{ $order->id }}</div>
                                        </div>
                                        @if($order->is_urgent)
                                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Urgent</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $order->customer->name ?? 'Walk-in' }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->customer->phone ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($order->priority == 'urgent') bg-red-100 text-red-800
                                        @elseif($order->priority == 'high') bg-orange-100 text-orange-800
                                        @elseif($order->priority == 'normal') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($order->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $order->orderItems->count() }} items
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₹{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $order->created_at->format('M d, Y') }}</div>
                                    <div>{{ $order->created_at->format('h:i A') }}</div>
                                    @if($order->created_at->diffInHours(now()) > 24)
                                        <div class="text-red-600 text-xs">Overdue</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('orders.workflow.show', $order) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                    @if($order->canTransitionTo('confirmed'))
                                        <button onclick="transitionOrder({{ $order->id }}, 'confirmed')" class="text-green-600 hover:text-green-900">Confirm</button>
                                    @endif
                                    @if($order->canTransitionTo('processing'))
                                        <button onclick="transitionOrder({{ $order->id }}, 'processing')" class="text-orange-600 hover:text-orange-900">Process</button>
                                    @endif
                                    @if($order->canTransitionTo('ready'))
                                        <button onclick="transitionOrder({{ $order->id }}, 'ready')" class="text-yellow-600 hover:text-yellow-900">Ready</button>
                                    @endif
                                    @if($order->canTransitionTo('delivered'))
                                        <button onclick="transitionOrder({{ $order->id }}, 'delivered')" class="text-green-600 hover:text-green-900">Deliver</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No {{ $status }} orders</h3>
                <p class="mt-1 text-sm text-gray-500">No orders found in {{ $status }} status.</p>
            </div>
        @endif
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="bulkActionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Bulk Actions</h3>
            </div>
            <form id="bulkActionsForm" class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                    <select name="action" id="bulkAction" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Action</option>
                        <option value="confirm">Confirm Orders</option>
                        <option value="process">Start Processing</option>
                        <option value="ready">Mark as Ready</option>
                        <option value="deliver">Mark as Delivered</option>
                        <option value="cancel">Cancel Orders</option>
                        <option value="priority_high">Set Priority: High</option>
                        <option value="priority_urgent">Set Priority: Urgent</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add notes for this action..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeBulkActionsModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Execute Action
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Individual checkbox change
document.querySelectorAll('.order-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.order-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        const selectAllCheckbox = document.getElementById('select-all');
        
        selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
    });
});

function showBulkActions() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select orders to perform bulk actions');
        return;
    }
    document.getElementById('bulkActionsModal').classList.remove('hidden');
}

function closeBulkActionsModal() {
    document.getElementById('bulkActionsModal').classList.add('hidden');
}

// Bulk actions form submission
document.getElementById('bulkActionsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    const orderIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    const formData = new FormData(this);
    const data = {
        order_ids: orderIds,
        action: formData.get('action'),
        notes: formData.get('notes'),
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    try {
        const response = await fetch('/orders/workflow/bulk-transition', {
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
        alert('An error occurred while executing bulk action');
    }
});

async function transitionOrder(orderId, status) {
    if (!confirm(`Are you sure you want to transition this order to ${status}?`)) {
        return;
    }

    try {
        const response = await fetch(`/orders/${orderId}/workflow/transition`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: status,
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
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
}
</script>
@endsection