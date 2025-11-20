@extends('layouts.cashier')

@section('title', 'Session Handler')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Session Handler Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-8 text-white text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-check text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold mb-2">Session Handler</h1>
                <p class="text-purple-100">Who is handling this session?</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <form id="sessionHandlerForm" method="POST" action="{{ route('pos.process-session-handler') }}">
                    @csrf
                    
                    <!-- Handler Name Input -->
                    <div class="mb-6">
                        <label for="handled_by" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-purple-600"></i>
                            Cashier Name or ID
                        </label>
                        <input type="text" 
                               name="handled_by" 
                               id="handled_by" 
                               value="{{ auth()->user()->name }}"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors @error('handled_by') border-red-500 @enderror"
                               placeholder="Enter your name or ID"
                               required
                               autofocus>
                        @error('handled_by')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            This will be displayed on the POS terminal during your session
                        </p>
                    </div>

                    <!-- Quick Select Options -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-list mr-2 text-purple-600"></i>
                            Quick Select
                        </label>
                        <div class="grid grid-cols-1 gap-2">
                            <button type="button" 
                                    onclick="selectHandler('{{ auth()->user()->name }}')"
                                    class="p-3 text-left border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors">
                                <div class="font-medium text-gray-900">{{ auth()->user()->name }}</div>
                                <div class="text-sm text-gray-500">Current User</div>
                            </button>
                            <button type="button" 
                                    onclick="selectHandler('{{ auth()->user()->email }}')"
                                    class="p-3 text-left border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors">
                                <div class="font-medium text-gray-900">{{ auth()->user()->email }}</div>
                                <div class="text-sm text-gray-500">Email ID</div>
                            </button>
                        </div>
                    </div>

                    <!-- Session Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex items-center space-x-2 mb-2">
                            <i class="fas fa-store text-purple-600"></i>
                            <span class="text-sm font-medium text-gray-700">Branch Information</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <div><strong>Branch:</strong> {{ $branch->name ?? 'Not Assigned' }}</div>
                            <div><strong>Terminal:</strong> {{ $branch->pos_terminal_id ?? 'POS-' . ($branch->code ?? '001') }}</div>
                            <div><strong>Time:</strong> {{ now()->format('M d, Y H:i') }}</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <a href="{{ route('dashboard') }}" 
                           class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg transition-colors text-center">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                        <button type="submit" 
                                class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-check mr-2"></i>
                            Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                <i class="fas fa-lightbulb mr-1 text-yellow-500"></i>
                After confirming the session handler, you'll be taken to start your POS session
            </p>
        </div>
    </div>
</div>

<script>
function selectHandler(value) {
    document.getElementById('handled_by').value = value;
    document.getElementById('handled_by').focus();
}

// Form submission handling
document.getElementById('sessionHandlerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Show loading state
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    submitButton.disabled = true;
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message briefly
            submitButton.innerHTML = '<i class="fas fa-check mr-2"></i>Success!';
            submitButton.classList.remove('from-purple-600', 'to-indigo-600', 'hover:from-purple-700', 'hover:to-indigo-700');
            submitButton.classList.add('from-green-600', 'to-green-700');
            
            setTimeout(() => {
                // Redirect to start session
                window.location.href = data.redirect_url || '{{ route("pos.start-session") }}';
            }, 1000);
        } else {
            // Show error
            submitButton.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Error: ' + (data.message || 'Failed to process');
            submitButton.classList.remove('from-purple-600', 'to-indigo-600', 'hover:from-purple-700', 'hover:to-indigo-700');
            submitButton.classList.add('from-red-600', 'to-red-700');
            
            setTimeout(() => {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                submitButton.classList.remove('from-red-600', 'to-red-700');
                submitButton.classList.add('from-purple-600', 'to-indigo-600', 'hover:from-purple-700', 'hover:to-indigo-700');
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitButton.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Network Error';
        submitButton.classList.remove('from-purple-600', 'to-indigo-600', 'hover:from-purple-700', 'hover:to-indigo-700');
        submitButton.classList.add('from-red-600', 'to-red-700');
        
        setTimeout(() => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            submitButton.classList.remove('from-red-600', 'to-red-700');
            submitButton.classList.add('from-purple-600', 'to-indigo-600', 'hover:from-purple-700', 'hover:to-indigo-700');
        }, 3000);
    });
});
</script>
@endsection


