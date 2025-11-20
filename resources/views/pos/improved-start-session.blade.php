@extends('layouts.cashier')

@section('title', 'Start POS Session')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <i class="fas fa-play text-white text-3xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Start New POS Session</h1>
        <p class="text-gray-600">Begin your workday by starting a new point of sale session</p>
    </div>

    <!-- Session Info Card -->
    <div class="cashier-card rounded-xl p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Cashier</div>
                    <div class="font-medium text-gray-900">{{ auth()->user()->name }}</div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-green-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Branch</div>
                    <div class="font-medium text-gray-900">{{ auth()->user()->branch->name ?? 'Unknown' }}</div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-purple-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Date</div>
                    <div class="font-medium text-gray-900">{{ now()->format('M d, Y') }}</div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Time</div>
                    <div class="font-medium text-gray-900">{{ now()->format('H:i:s') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Session Form -->
    <div class="cashier-card rounded-xl p-8">
        <form id="start-session-form">
            @csrf
            <div class="space-y-6">
                <!-- Opening Cash -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Opening Cash Amount (₹) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500 text-lg">₹</span>
                        <input type="number" 
                               id="opening-cash" 
                               name="opening_cash" 
                               step="1" 
                               min="0" 
                               required
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-3 text-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="0">
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Enter the exact amount of cash in your register drawer
                    </p>
                </div>

                <!-- Session Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Session Notes (Optional)
                    </label>
                    <textarea id="session-notes" 
                              name="session_notes" 
                              rows="4" 
                              maxlength="500"
                              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="Any notes about this session (e.g., shift details, special instructions)..."></textarea>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Optional notes to help you remember important details about this session
                    </p>
                </div>

                <!-- Quick Tips -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-lightbulb mr-2"></i>Before You Start
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Count your cash drawer carefully</li>
                        <li>• Ensure your terminal is working properly</li>
                        <li>• Check that you have enough change</li>
                        <li>• Verify your internet connection</li>
                    </ul>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                <a href="{{ route('pos.session-manager') }}" 
                   class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-lg text-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Session Manager
                </a>
                <button type="submit" 
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                    <i class="fas fa-play mr-2"></i>Start Session
                </button>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="cashier-card rounded-xl p-6 mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-question-circle mr-2"></i>Need Help?
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-book text-blue-600 text-sm"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">User Guide</h4>
                    <p class="text-sm text-gray-600">Learn how to use the POS system effectively</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-headset text-green-600 text-sm"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Support</h4>
                    <p class="text-sm text-gray-600">Contact support if you need assistance</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-xl shadow-xl p-8 text-center max-w-sm w-full mx-4">
            <div class="w-16 h-16 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin mx-auto mb-4"></div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Starting Session</h3>
            <p class="text-gray-600">Please wait while we initialize your POS session...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus on opening cash input
    document.getElementById('opening-cash').focus();
    
    // Format currency input (whole numbers only)
    document.getElementById('opening-cash').addEventListener('input', function(e) {
        let value = parseInt(e.target.value);
        if (!isNaN(value)) {
            e.target.value = value;
        }
    });
});

// Form submission
document.getElementById('start-session-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const openingCash = document.getElementById('opening-cash').value;
    const sessionNotes = document.getElementById('session-notes').value;
    
    if (!openingCash || parseInt(openingCash) < 0) {
        alert('Please enter a valid opening cash amount');
        return;
    }
    
    // Show loading modal
    document.getElementById('loading-modal').classList.remove('hidden');
    
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
        document.getElementById('loading-modal').classList.add('hidden');
        
        if (data.success) {
            // Show success message
            alert('POS Session started successfully! Redirecting to POS Terminal...');
            
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
        document.getElementById('loading-modal').classList.add('hidden');
        console.error('Error:', error);
        alert('Error starting session. Please try again.');
    });
});
</script>
@endsection
