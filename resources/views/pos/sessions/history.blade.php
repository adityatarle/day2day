@extends('layouts.cashier')

@section('title', 'Session History - POS System')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-history text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Session History</h1>
                    <p class="text-gray-600">View your POS session history and performance</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('pos.session.current') }}" class="bg-white border-2 border-purple-500 text-purple-600 px-4 py-2 rounded-lg font-medium hover:bg-purple-50 transition-colors">
                    <i class="fas fa-clock mr-2"></i>
                    Current Session
                </a>
                <a href="{{ route('pos.sessions.create') }}" class="quick-action px-4 py-2 text-white rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    Start New Session
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="cashier-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_sessions'] }}</div>
                    <div class="text-sm text-gray-600">Total Sessions</div>
                </div>
            </div>
        </div>

        <div class="cashier-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-play text-green-600"></i>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['active_sessions'] }}</div>
                    <div class="text-sm text-gray-600">Active Sessions</div>
                </div>
            </div>
        </div>

        <div class="cashier-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['total_sales'], 0) }}</div>
                    <div class="text-sm text-gray-600">Total Sales</div>
                </div>
            </div>
        </div>

        <div class="cashier-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-stopwatch text-orange-600"></i>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['avg_session_duration'] }}m</div>
                    <div class="text-sm text-gray-600">Avg Duration</div>
                </div>
            </div>
        </div>
    </div>

    @if($sessions->count() > 0)
        <!-- Sessions List -->
        <div class="cashier-card rounded-xl">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Recent Sessions</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Showing {{ $sessions->count() }} sessions</span>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-6 font-semibold text-gray-900">Session</th>
                            <th class="text-left py-3 px-6 font-semibold text-gray-900">Date & Time</th>
                            <th class="text-left py-3 px-6 font-semibold text-gray-900">Duration</th>
                            <th class="text-left py-3 px-6 font-semibold text-gray-900">Opening Balance</th>
                            <th class="text-left py-3 px-6 font-semibold text-gray-900">Closing Balance</th>
                            <th class="text-left py-3 px-6 font-semibold text-gray-900">Variance</th>
                            <th class="text-left py-3 px-6 font-semibold text-gray-900">Status</th>
                            <th class="text-left py-3 px-6 font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($sessions as $session)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-white font-bold text-sm">#{{ $session->id }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">Session #{{ $session->id }}</div>
                                            <div class="text-sm text-gray-500">{{ $session->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-sm text-gray-900">{{ $session->started_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $session->started_at->format('H:i:s') }}</div>
                                    @if($session->ended_at)
                                        <div class="text-xs text-gray-500">to {{ $session->ended_at->format('H:i:s') }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($session->ended_at)
                                        @php
                                            $duration = $session->started_at->diffInMinutes($session->ended_at);
                                            $hours = floor($duration / 60);
                                            $minutes = $duration % 60;
                                        @endphp
                                        <span class="text-sm text-gray-900">{{ $hours }}h {{ $minutes }}m</span>
                                    @else
                                        <span class="text-sm text-green-600 font-medium">
                                            {{ $session->started_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-sm font-medium text-gray-900">₹{{ number_format($session->opening_cash ?? 0, 2) }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    @if($session->closing_cash !== null)
                                        <div class="text-sm font-medium text-gray-900">₹{{ number_format($session->closing_cash, 2) }}</div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($session->cash_difference !== null && abs($session->cash_difference) > 0.01)
                                        <div class="text-sm font-medium {{ $session->cash_difference > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $session->cash_difference > 0 ? '+' : '' }}₹{{ number_format($session->cash_difference, 2) }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($session->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5 animate-pulse"></div>
                                            Active
                                        </span>
                                    @elseif($session->status === 'closed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Closed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ ucfirst($session->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('pos.sessions.show', $session) }}" 
                                           class="text-blue-600 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($session->status === 'active' && auth()->user()->id === $session->user_id)
                                            <button onclick="closeSession({{ $session->id }})" 
                                                    class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors" 
                                                    title="Close Session">
                                                <i class="fas fa-door-closed"></i>
                                            </button>
                                        @endif
                                        <button onclick="getPerformanceData({{ $session->id }})" 
                                                class="text-green-600 hover:text-green-700 p-2 rounded-lg hover:bg-green-50 transition-colors" 
                                                title="Performance Data">
                                            <i class="fas fa-chart-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($sessions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- No Sessions -->
        <div class="cashier-card rounded-xl p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-history text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Session History Found</h3>
            <p class="text-gray-600 mb-6">You haven't started any POS sessions yet</p>
            <a href="{{ route('pos.sessions.create') }}" class="quick-action px-6 py-3 text-white rounded-lg font-medium">
                <i class="fas fa-plus mr-2"></i>
                Start Your First Session
            </a>
        </div>
    @endif
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Closing Cash (₹)</label>
                            <input type="number" id="closing-cash" name="closing_cash" step="0.01" min="0" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Closing Notes (Optional)</label>
                            <textarea id="closing-notes" name="closing_notes" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                      placeholder="Any notes about the session..."></textarea>
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

<!-- Performance Data Modal -->
<div id="performance-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Session Performance</h3>
                    <button onclick="closePerformanceModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="performance-content">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentSessionId = null;

function closeSession(sessionId) {
    currentSessionId = sessionId;
    document.getElementById('close-session-modal').classList.remove('hidden');
}

function closeCloseSessionModal() {
    document.getElementById('close-session-modal').classList.add('hidden');
    currentSessionId = null;
    document.getElementById('close-session-form').reset();
}

function getPerformanceData(sessionId) {
    document.getElementById('performance-modal').classList.remove('hidden');
    document.getElementById('performance-content').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-gray-400"></i> Loading...</div>';
    
    fetch(`/pos/sessions/${sessionId}/performance`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.hourly_performance || data.payment_methods) {
            displayPerformanceData(data);
        } else {
            document.getElementById('performance-content').innerHTML = '<p class="text-red-600">Error loading performance data</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('performance-content').innerHTML = '<p class="text-red-600">Error loading performance data</p>';
    });
}

function displayPerformanceData(data) {
    let content = '<div class="space-y-6">';
    
    if (data.hourly_performance && data.hourly_performance.length > 0) {
        content += '<div><h4 class="font-semibold text-gray-900 mb-3">Hourly Performance</h4>';
        content += '<div class="grid grid-cols-2 gap-4">';
        data.hourly_performance.forEach(hour => {
            content += `
                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="text-sm text-gray-500">${hour.hour}:00</div>
                    <div class="font-semibold">${hour.orders} orders - ₹${parseFloat(hour.sales).toFixed(2)}</div>
                </div>
            `;
        });
        content += '</div></div>';
    }
    
    if (data.payment_methods && data.payment_methods.length > 0) {
        content += '<div><h4 class="font-semibold text-gray-900 mb-3">Payment Methods</h4>';
        content += '<div class="space-y-2">';
        data.payment_methods.forEach(method => {
            content += `
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="capitalize font-medium">${method.payment_method}</span>
                    <span>${method.count} orders - ₹${parseFloat(method.total).toFixed(2)}</span>
                </div>
            `;
        });
        content += '</div></div>';
    }
    
    content += '</div>';
    
    if (!data.hourly_performance && !data.payment_methods) {
        content = '<p class="text-gray-600">No performance data available for this session.</p>';
    }
    
    document.getElementById('performance-content').innerHTML = content;
}

function closePerformanceModal() {
    document.getElementById('performance-modal').classList.add('hidden');
}

// Handle close session form submission
document.getElementById('close-session-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentSessionId) return;
    
    const formData = new FormData(this);
    const data = {
        closing_cash: formData.get('closing_cash'),
        closing_notes: formData.get('closing_notes')
    };
    
    fetch(`/pos/sessions/${currentSessionId}/close`, {
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

// Close modals when clicking outside
document.getElementById('close-session-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCloseSessionModal();
    }
});

document.getElementById('performance-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePerformanceModal();
    }
});
</script>
@endsection
