@extends('layouts.cashier')

@section('title', 'Start POS Session')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cash-register text-2xl text-blue-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Start POS Session</h1>
                <p class="text-gray-600 mt-2">{{ $branch->name }} - {{ $branch->city->name ?? 'No City' }}</p>
            </div>

            <form action="{{ route('pos.process-start-session') }}" method="POST">
                @csrf
                
                <!-- Session Handler (if not set from session handler page) -->
                @if(!session('handled_by'))
                <div class="mb-6">
                    <label for="handled_by" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        Session Handler
                    </label>
                    <input type="text" 
                           name="handled_by" 
                           id="handled_by" 
                           value="{{ auth()->user()->name }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('handled_by') border-red-500 @enderror"
                           placeholder="Enter your name or ID"
                           required>
                    @error('handled_by')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">Who is handling this session?</p>
                </div>
                @endif
                
                <div class="mb-6">
                    <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Terminal ID
                    </label>
                    <input type="text" 
                           name="terminal_id" 
                           id="terminal_id" 
                           value="{{ $branch->pos_terminal_id ?? 'POS-' . $branch->code }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('terminal_id') border-red-500 @enderror"
                           required>
                    @error('terminal_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="opening_cash" class="block text-sm font-medium text-gray-700 mb-2">
                        Opening Cash Amount
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">₹</span>
                        <input type="number" 
                               name="opening_cash" 
                               id="opening_cash" 
                               step="0.01" 
                               min="0"
                               value="{{ old('opening_cash', isset($previousClosingCash) ? number_format($previousClosingCash, 2, '.', '') : '0.00') }}"
                               class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('opening_cash') border-red-500 @enderror"
                               required>
                    </div>
                    @error('opening_cash')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">Enter the amount of cash in the register at the start of the session</p>
                    @if(isset($previousClosingCash))
                    <p class="text-gray-500 text-sm mt-1">Suggested from last closing: ₹{{ number_format($previousClosingCash, 2) }}</p>
                    @endif
                </div>

                <div class="flex space-x-4">
                    <a href="{{ route('pos.index') }}" 
                       class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-lg text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg">
                        Start Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection