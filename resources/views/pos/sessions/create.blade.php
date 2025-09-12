@extends('layouts.app')

@section('title', 'Start POS Session')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Start POS Session</h1>
                    <span class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                        {{ auth()->user()->branch->name ?? 'Branch' }}
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('pos.sessions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Sessions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-sm border p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cash-register text-2xl text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Start New POS Session</h2>
                <p class="text-gray-600">Initialize your POS session with opening balance and notes</p>
            </div>

            <form id="start-session-form">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Opening Balance (₹) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="opening-balance" 
                               name="opening_balance" 
                               step="0.01" 
                               min="0" 
                               required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0.00">
                        <p class="text-sm text-gray-500 mt-1">Enter the amount of cash in the register at the start of your session</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Session Notes (Optional)
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="4" 
                                  maxlength="500"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Any notes about this session..."></textarea>
                        <p class="text-sm text-gray-500 mt-1">Optional notes about your session (max 500 characters)</p>
                    </div>

                    <!-- Session Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Session Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
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

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('pos.sessions.index') }}" 
                           class="px-6 py-3 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium flex items-center">
                            <i class="fas fa-play mr-2"></i>
                            Start Session
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-xl shadow-xl p-8 text-center">
            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-700 font-medium">Starting POS Session...</p>
        </div>
    </div>
</div>

<script>
document.getElementById('start-session-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const openingBalance = document.getElementById('opening-balance').value;
    const notes = document.getElementById('notes').value;
    
    if (!openingBalance || parseFloat(openingBalance) < 0) {
        alert('Please enter a valid opening balance');
        return;
    }
    
    // Show loading modal
    document.getElementById('loading-modal').classList.remove('hidden');
    
    const formData = {
        opening_balance: parseFloat(openingBalance),
        notes: notes
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
            alert('POS Session started successfully!');
            // Redirect to the session or POS system
            if (data.session && data.session.id) {
                window.location.href = `/pos/sessions/${data.session.id}`;
            } else {
                window.location.href = '{{ route("pos.index") }}';
            }
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

// Auto-focus on opening balance input
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('opening-balance').focus();
});

// Format opening balance input
document.getElementById('opening-balance').addEventListener('input', function(e) {
    let value = parseFloat(e.target.value);
    if (!isNaN(value)) {
        e.target.value = value.toFixed(2);
    }
});
</script>
@endsection