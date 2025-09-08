@extends('layouts.cashier')

@section('title', 'POS Session History')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Session History</h1>
                    <span class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                        My Sessions
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('pos.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Back to POS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($sessions->count() > 0)
            <!-- Sessions List -->
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Session</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Terminal</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Started</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Ended</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Duration</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Transactions</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Total Sales</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $session)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-4">
                                            <div class="font-medium text-gray-900">#{{ $session->id }}</div>
                                            <div class="text-sm text-gray-500">{{ $session->branch->name ?? 'Unknown Branch' }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $session->terminal_id }}</span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-sm text-gray-900">{{ $session->started_at->format('d M Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $session->started_at->format('H:i:s') }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            @if($session->ended_at)
                                                <div class="text-sm text-gray-900">{{ $session->ended_at->format('d M Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $session->ended_at->format('H:i:s') }}</div>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4">
                                            @if($session->ended_at)
                                                @php
                                                    $duration = $session->started_at->diffInMinutes($session->ended_at);
                                                    $hours = floor($duration / 60);
                                                    $minutes = $duration % 60;
                                                @endphp
                                                <span class="text-sm text-gray-900">{{ $hours }}h {{ $minutes }}m</span>
                                            @else
                                                <span class="text-sm text-green-600 font-medium">Active</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $session->total_transactions ?? 0 }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-sm font-medium text-green-600">₹{{ number_format($session->total_sales ?? 0, 2) }}</div>
                                        </td>
                                        <td class="py-4 px-4">
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
                                        <td class="py-4 px-4">
                                            <div class="flex items-center space-x-2">
                                                <button onclick="viewSessionDetails({{ $session->id }})" class="text-blue-600 hover:text-blue-700 text-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($session->status === 'active')
                                                    <a href="{{ route('pos.close-session') }}" class="text-red-600 hover:text-red-700 text-sm">
                                                        <i class="fas fa-door-closed"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                @if($sessions->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>

            <!-- Summary Stats -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $sessions->count() }}</div>
                            <div class="text-sm text-gray-600">Total Sessions</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-rupee-sign text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">₹{{ number_format($sessions->sum('total_sales'), 2) }}</div>
                            <div class="text-sm text-gray-600">Total Sales</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-receipt text-purple-600"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $sessions->sum('total_transactions') }}</div>
                            <div class="text-sm text-gray-600">Total Transactions</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-yellow-600"></i>
                        </div>
                        <div class="ml-4">
                            @php
                                $avgSales = $sessions->where('total_transactions', '>', 0)->avg('total_sales');
                            @endphp
                            <div class="text-2xl font-bold text-gray-900">₹{{ number_format($avgSales ?? 0, 2) }}</div>
                            <div class="text-sm text-gray-600">Avg Sales/Session</div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Sessions -->
            <div class="text-center py-12">
                <div class="bg-white rounded-xl shadow-sm border p-8 max-w-md mx-auto">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-history text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Session History</h3>
                    <p class="text-gray-600 mb-6">You haven't started any POS sessions yet</p>
                    <a href="{{ route('pos.start-session') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg">
                        Start Your First Session
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Session Details Modal -->
<div id="session-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Session Details</h3>
                    <button onclick="closeSessionModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="session-details-content">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewSessionDetails(sessionId) {
    // Show modal
    document.getElementById('session-modal').classList.remove('hidden');
    
    // Load session details via AJAX
    fetch(`/api/pos/sessions/${sessionId}/performance`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySessionDetails(data.data);
        } else {
            document.getElementById('session-details-content').innerHTML = '<p class="text-red-600">Error loading session details</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('session-details-content').innerHTML = '<p class="text-red-600">Error loading session details</p>';
    });
}

function displaySessionDetails(session) {
    const content = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Session ID</div>
                    <div class="font-semibold">#${session.id}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Terminal</div>
                    <div class="font-semibold">${session.terminal_id}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Opening Cash</div>
                    <div class="font-semibold">₹${parseFloat(session.opening_cash).toFixed(2)}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Closing Cash</div>
                    <div class="font-semibold">${session.closing_cash ? '₹' + parseFloat(session.closing_cash).toFixed(2) : 'N/A'}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Total Sales</div>
                    <div class="font-semibold text-green-600">₹${parseFloat(session.total_sales || 0).toFixed(2)}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Transactions</div>
                    <div class="font-semibold">${session.total_transactions || 0}</div>
                </div>
            </div>
            
            ${session.session_notes && session.session_notes.length > 0 ? `
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500 mb-2">Session Notes</div>
                    <ul class="text-sm text-gray-700">
                        ${session.session_notes.map(note => `<li>• ${note}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
            
            ${session.cash_difference && Math.abs(session.cash_difference) > 0.01 ? `
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-sm text-red-600 font-medium">Cash Difference</div>
                    <div class="text-sm text-red-700">₹${parseFloat(session.cash_difference).toFixed(2)}</div>
                </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('session-details-content').innerHTML = content;
}

function closeSessionModal() {
    document.getElementById('session-modal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('session-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSessionModal();
    }
});
</script>
@endsection