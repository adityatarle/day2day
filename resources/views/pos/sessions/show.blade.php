@extends('layouts.app')

@section('title', 'POS Session Details')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Session #{{ $posSession->id }}</h1>
                    @if($posSession->status === 'active')
                        <span class="ml-4 inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                            Active
                        </span>
                    @elseif($posSession->status === 'closed')
                        <span class="ml-4 px-3 py-1 bg-gray-100 text-gray-800 text-sm font-medium rounded-full">
                            Closed
                        </span>
                    @endif
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('pos.sessions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Sessions
                    </a>
                    @if($posSession->status === 'active' && (auth()->user()->id === $posSession->user_id || auth()->user()->isBranchManager() || auth()->user()->isSuperAdmin()))
                        <button onclick="closeSession()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">
                            <i class="fas fa-door-closed mr-2"></i>Close Session
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Session Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="space-y-4">
                                <div>
                                    <span class="text-sm text-gray-500">User</span>
                                    <div class="flex items-center mt-1">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-white font-bold text-xs">{{ strtoupper(substr($posSession->user->name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $posSession->user->name ?? 'Unknown User' }}</div>
                                            <div class="text-sm text-gray-500">{{ $posSession->user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Branch</span>
                                    <div class="font-medium text-gray-900 mt-1">{{ $posSession->branch->name ?? 'Unknown Branch' }}</div>
                                    @if($posSession->branch && $posSession->branch->city)
                                        <div class="text-sm text-gray-500">{{ $posSession->branch->city->name }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="space-y-4">
                                <div>
                                    <span class="text-sm text-gray-500">Started At</span>
                                    <div class="font-medium text-gray-900 mt-1">{{ $posSession->started_at->format('M d, Y H:i:s') }}</div>
                                    <div class="text-sm text-gray-500">{{ $posSession->started_at->diffForHumans() }}</div>
                                </div>
                                @if($posSession->ended_at)
                                    <div>
                                        <span class="text-sm text-gray-500">Ended At</span>
                                        <div class="font-medium text-gray-900 mt-1">{{ $posSession->ended_at->format('M d, Y H:i:s') }}</div>
                                        <div class="text-sm text-gray-500">{{ $sessionStats['duration'] ?? '' }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($posSession->notes)
                        <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Session Notes:</span>
                            <p class="text-sm text-gray-600 mt-1">{{ $posSession->notes }}</p>
                        </div>
                    @endif
                    
                    @if($posSession->closing_notes)
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Closing Notes:</span>
                            <p class="text-sm text-gray-600 mt-1">{{ $posSession->closing_notes }}</p>
                        </div>
                    @endif

                    @if($posSession->closing_cash_breakdown)
                        <div class="mt-4 p-4 bg-white border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-900">Cash Breakdown</span>
                                <span class="text-xs text-gray-500">Totals entered at closing time</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($posSession->closing_cash_breakdown as $row)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">{{ $row['count'] ?? 0 }} x ₹{{ number_format($row['denomination'] ?? 0) }}</span>
                                        <span class="font-semibold text-gray-900">₹{{ number_format($row['amount'] ?? 0, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Summary</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Opening Balance</span>
                        <span class="font-medium text-gray-900">₹{{ number_format($posSession->opening_balance ?? 0, 2) }}</span>
                    </div>
                    
                    @if($posSession->closing_balance !== null)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Closing Balance</span>
                            <span class="font-medium text-gray-900">₹{{ number_format($posSession->closing_balance, 2) }}</span>
                        </div>
                        
                        @if($posSession->expected_closing_balance !== null)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Expected Balance</span>
                                <span class="font-medium text-gray-900">₹{{ number_format($posSession->expected_closing_balance, 2) }}</span>
                            </div>
                        @endif
                        
                        @if($posSession->variance !== null && abs($posSession->variance) > 0.01)
                            <div class="flex justify-between items-center p-3 {{ $posSession->variance > 0 ? 'bg-green-50' : 'bg-red-50' }} rounded-lg">
                                <span class="text-sm font-medium {{ $posSession->variance > 0 ? 'text-green-700' : 'text-red-700' }}">Variance</span>
                                <span class="font-bold {{ $posSession->variance > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $posSession->variance > 0 ? '+' : '' }}₹{{ number_format($posSession->variance, 2) }}
                                </span>
                            </div>
                        @endif
                    @else
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Current Balance</span>
                            <span class="font-medium text-gray-900">₹{{ number_format($posSession->current_balance ?? $posSession->opening_balance ?? 0, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Session Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-receipt text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $sessionStats['total_orders'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Orders</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $sessionStats['completed_orders'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Completed</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $sessionStats['pending_orders'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Pending</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $sessionStats['cancelled_orders'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Cancelled</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-rupee-sign text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">₹{{ number_format($sessionStats['total_sales'] ?? 0, 2) }}</div>
                        <div class="text-sm text-gray-600">Total Sales</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">₹{{ number_format($sessionStats['cash_sales'] ?? 0, 2) }}</div>
                        <div class="text-sm text-gray-600">Cash Sales</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">₹{{ number_format($sessionStats['card_sales'] ?? 0, 2) }}</div>
                        <div class="text-sm text-gray-600">Card Sales</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        @if($posSession->orders && $posSession->orders->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Session Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-3 px-6 font-semibold text-gray-900">Order ID</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-900">Customer</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-900">Items</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-900">Amount</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-900">Payment</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-900">Status</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-900">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posSession->orders->take(20) as $order)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6">
                                        <div class="font-medium text-gray-900">#{{ $order->id }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="font-medium text-gray-900">{{ $order->customer->name ?? 'Walk-in Customer' }}</div>
                                        @if($order->customer && $order->customer->phone)
                                            <div class="text-sm text-gray-500">{{ $order->customer->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="text-sm text-gray-900">{{ $order->orderItems->count() ?? 0 }} items</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="font-medium text-gray-900">₹{{ number_format($order->total_amount ?? 0, 2) }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full capitalize">
                                            {{ $order->payment_method ?? 'cash' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($order->status === 'completed')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Completed</span>
                                        @elseif($order->status === 'pending')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Pending</span>
                                        @elseif($order->status === 'cancelled')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Cancelled</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="text-sm text-gray-900">{{ $order->created_at->format('H:i:s') }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($posSession->orders->count() > 20)
                    <div class="p-4 border-t border-gray-200 text-center">
                        <p class="text-sm text-gray-500">Showing 20 of {{ $posSession->orders->count() }} orders</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Orders Yet</h3>
                <p class="text-gray-600">This session doesn't have any orders yet</p>
            </div>
        @endif
    </div>
</div>

<!-- Close Session Modal -->
<div id="close-session-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Close POS Session</h3>
                    <button onclick="closeCloseSessionModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="close-session-form">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Closing Balance (₹)</label>
                            <input type="number" id="closing-balance" name="closing_balance" step="0.01" min="0" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Closing Notes (Optional)</label>
                            <textarea id="closing-notes" name="closing_notes" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Any notes about the session closure..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeCloseSessionModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                            Close Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function closeSession() {
    document.getElementById('close-session-modal').classList.remove('hidden');
}

function closeCloseSessionModal() {
    document.getElementById('close-session-modal').classList.add('hidden');
    document.getElementById('close-session-form').reset();
}

// Handle close session form submission
document.getElementById('close-session-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        closing_balance: formData.get('closing_balance'),
        closing_notes: formData.get('closing_notes')
    };
    
    fetch(`/pos/sessions/{{ $posSession->id }}/close`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Session closed successfully!');
            closeCloseSessionModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to close session'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error closing session');
    });
});

// Close modal when clicking outside
document.getElementById('close-session-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCloseSessionModal();
    }
});
</script>
@endsection