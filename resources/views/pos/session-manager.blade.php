@extends('layouts.cashier')

@section('title', 'POS Session Manager')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-cash-register text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">POS Session Manager</h1>
                <p class="text-gray-600">Manage your point of sale sessions</p>
            </div>
        </div>
    </div>

    @php
        $currentSession = auth()->user()->currentPosSession();
        $hasActiveSession = $currentSession && $currentSession->status === 'active';
    @endphp

    <!-- Session Status Card -->
    <div class="mb-8">
        @if($hasActiveSession)
            <!-- Active Session Card -->
            <div class="cashier-card rounded-xl p-6 bg-gradient-to-r from-green-500 to-emerald-600 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-play text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Active Session</h2>
                            <p class="text-green-100">Session #{{ $currentSession->id }} - Started {{ $currentSession->started_at->format('M d, Y H:i') }}</p>
                            <p class="text-green-100">Duration: {{ $currentSession->started_at->diffForHumans(null, true) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">₹{{ number_format($currentSession->total_sales, 2) }}</div>
                        <div class="text-green-100">{{ $currentSession->total_transactions }} transactions</div>
                        <div class="mt-4 flex space-x-3">
                            <a href="{{ route('pos.index') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>Continue Selling
                            </a>
                            <button onclick="showCloseSessionModal()" class="bg-red-500/80 hover:bg-red-500 px-4 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-stop mr-2"></i>Close Session
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Active Session Card -->
            <div class="cashier-card rounded-xl p-6 bg-gradient-to-r from-orange-500 to-red-600 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-pause text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">No Active Session</h2>
                            <p class="text-orange-100">Start a new POS session to begin processing sales</p>
                        </div>
                    </div>
                    <div>
                        <button onclick="showStartSessionModal()" class="bg-white/20 hover:bg-white/30 px-6 py-3 rounded-lg font-bold transition-colors">
                            <i class="fas fa-play mr-2"></i>Start New Session
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Workflow Guide -->
    @include('pos.components.workflow-guide')

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="{{ route('pos.index') }}" class="cashier-card rounded-xl p-6 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <i class="fas fa-shopping-cart text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">POS Terminal</h3>
                    <p class="text-sm text-gray-600">Process sales</p>
                </div>
            </div>
        </a>

        <a href="{{ route('billing.quickSale') }}" class="cashier-card rounded-xl p-6 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i class="fas fa-bolt text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Quick Sale</h3>
                    <p class="text-sm text-gray-600">Fast transactions</p>
                </div>
            </div>
        </a>

        <a href="{{ route('cashier.orders.index') }}" class="cashier-card rounded-xl p-6 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <i class="fas fa-list-alt text-purple-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">My Sales</h3>
                    <p class="text-sm text-gray-600">View orders</p>
                </div>
            </div>
        </a>

        <a href="{{ route('pos.session.history') }}" class="cashier-card rounded-xl p-6 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <i class="fas fa-history text-orange-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Session History</h3>
                    <p class="text-sm text-gray-600">Past sessions</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Today's Performance -->
    <div class="cashier-card rounded-xl p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Today's Performance</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $todayStats['total_orders'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Total Orders</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">₹{{ number_format($todayStats['total_sales'] ?? 0, 2) }}</div>
                <div class="text-sm text-gray-600">Total Sales</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ $todayStats['active_sessions'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Active Sessions</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-orange-600">{{ $todayStats['avg_order_value'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Avg Order Value</div>
            </div>
        </div>
    </div>

    <!-- Recent Sessions -->
    <div class="cashier-card rounded-xl p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Sessions</h2>
        <div class="space-y-4">
            @forelse($recentSessions as $session)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">#{{ $session->id }}</span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Session #{{ $session->id }}</div>
                            <div class="text-sm text-gray-600">{{ $session->started_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-gray-900">₹{{ number_format($session->total_sales, 2) }}</div>
                        <div class="text-sm text-gray-600">{{ $session->total_transactions }} transactions</div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $session->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-history text-4xl mb-4"></i>
                    <p>No recent sessions found</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Start Session Modal -->
<div id="start-session-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Start New POS Session</h3>
                    <button onclick="closeStartSessionModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="start-session-form">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Opening Cash Amount (₹) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="opening-cash" 
                                   name="opening_cash" 
                                   step="1" 
                                   min="0" 
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="0">
                            <p class="text-sm text-gray-500 mt-1">Enter the amount of cash in the register</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Session Notes (Optional)
                            </label>
                            <textarea id="session-notes" 
                                      name="session_notes" 
                                      rows="3" 
                                      maxlength="500"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                      placeholder="Any notes about this session..."></textarea>
                        </div>

                        <!-- Session Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Session Information</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500">User:</span>
                                    <span class="font-medium text-gray-900 ml-1">{{ auth()->user()->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Branch:</span>
                                    <span class="font-medium text-gray-900 ml-1">{{ auth()->user()->branch->name ?? 'Unknown' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Date:</span>
                                    <span class="font-medium text-gray-900 ml-1">{{ now()->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Time:</span>
                                    <span class="font-medium text-gray-900 ml-1">{{ now()->format('H:i:s') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeStartSessionModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                            <i class="fas fa-play mr-2"></i>Start Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Close Session Modal -->
@if($hasActiveSession && $currentSession)
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
                
                <form id="close-session-form" data-session-id="{{ $currentSession->id ?? '' }}">
                    @csrf
                    <input type="hidden" id="session-id" value="{{ $currentSession->id ?? '' }}">
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Session Summary</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Session ID:</span>
                                    <span class="font-medium text-gray-900 ml-1">#{{ $currentSession->id ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Duration:</span>
                                    <span class="font-medium text-gray-900 ml-1">{{ $currentSession ? $currentSession->started_at->diffForHumans(null, true) : 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Total Sales:</span>
                                    <span class="font-medium text-gray-900 ml-1">₹{{ number_format($currentSession->total_sales ?? 0, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Transactions:</span>
                                    <span class="font-medium text-gray-900 ml-1">{{ $currentSession->total_transactions ?? 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Closing Cash Amount (₹) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="closing-cash" 
                                   name="closing_cash" 
                                   step="1" 
                                   min="0" 
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="0">
                            <p class="text-sm text-gray-500 mt-1">Enter the actual cash count in the register</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Closing Notes (Optional)
                            </label>
                            <textarea id="closing-notes" 
                                      name="closing_notes" 
                                      rows="3" 
                                      maxlength="500"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                      placeholder="Any notes about closing the session..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeCloseSessionModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                            <i class="fas fa-stop mr-2"></i>Close Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<script>
// Modal functions
function showStartSessionModal() {
    document.getElementById('start-session-modal').classList.remove('hidden');
    document.getElementById('opening-cash').focus();
}

function closeStartSessionModal() {
    document.getElementById('start-session-modal').classList.add('hidden');
    document.getElementById('start-session-form').reset();
}

function showCloseSessionModal() {
    const modal = document.getElementById('close-session-modal');
    if (modal) {
        modal.classList.remove('hidden');
        const closingCashInput = document.getElementById('closing-cash');
        if (closingCashInput) {
            closingCashInput.focus();
        }
    }
}

function closeCloseSessionModal() {
    const modal = document.getElementById('close-session-modal');
    if (modal) {
        modal.classList.add('hidden');
        const form = document.getElementById('close-session-form');
        if (form) {
            form.reset();
        }
    }
}

// Start session form submission
document.getElementById('start-session-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const openingCash = document.getElementById('opening-cash').value;
    const sessionNotes = document.getElementById('session-notes').value;
    
    if (!openingCash || parseInt(openingCash) < 0) {
        alert('Please enter a valid opening cash amount');
        return;
    }
    
    const formData = {
        opening_cash: parseInt(openingCash),
        session_notes: sessionNotes
    };
    
    fetch('{{ route("pos.sessions.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('POS Session started successfully! Redirecting to POS Terminal...');
            closeStartSessionModal();
            // Add a small delay to ensure session is properly created
            setTimeout(function() {
                window.location.href = '{{ route("pos.index") }}';
            }, 1000);
        } else {
            if (data.errors) {
                let errorMessage = 'Please fix the following errors:\n';
                Object.keys(data.errors).forEach(key => {
                    errorMessage += `- ${data.errors[key].join(', ')}\n`;
                });
                alert(errorMessage);
            } else {
                alert('Error: ' + (data.message || 'Failed to start session'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error starting session. Please try again.');
    });
});

// Close session form submission
@if($hasActiveSession && $currentSession)
const closeSessionForm = document.getElementById('close-session-form');
if (closeSessionForm) {
    closeSessionForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const closingCash = document.getElementById('closing-cash').value;
    const closingNotes = document.getElementById('closing-notes').value;
    
    if (!closingCash || parseInt(closingCash) < 0) {
        alert('Please enter a valid closing cash amount');
        return;
    }
    
    const sessionId = document.getElementById('session-id').value;
    
    if (!sessionId) {
        alert('No active session found. Please refresh the page.');
        return;
    }
    
    const formData = {
        closing_cash: parseInt(closingCash),
        closing_notes: closingNotes
    };
    
    // Build the URL manually to avoid route helper issues
    const closeUrl = `/pos/sessions/${sessionId}/close`;
    
    fetch(closeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('POS Session closed successfully!');
            closeCloseSessionModal();
            location.reload();
        } else {
            if (data.errors) {
                let errorMessage = 'Please fix the following errors:\n';
                Object.keys(data.errors).forEach(key => {
                    errorMessage += `- ${data.errors[key].join(', ')}\n`;
                });
                alert(errorMessage);
            } else {
                alert('Error: ' + (data.message || 'Failed to close session'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error closing session. Please try again.');
    });
    });
}
@endif

// Close modals when clicking outside
document.getElementById('start-session-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStartSessionModal();
    }
});

document.getElementById('close-session-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCloseSessionModal();
    }
});

// Auto-format currency inputs (whole numbers only)
document.getElementById('opening-cash').addEventListener('input', function(e) {
    let value = parseInt(e.target.value);
    if (!isNaN(value)) {
        e.target.value = value;
    }
});

document.getElementById('closing-cash').addEventListener('input', function(e) {
    let value = parseInt(e.target.value);
    if (!isNaN(value)) {
        e.target.value = value;
    }
});
</script>
@endsection
