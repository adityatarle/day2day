@extends('layouts.app')

@section('title', 'Close POS Session')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-door-closed text-2xl text-red-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Close POS Session</h1>
                <p class="text-gray-600 mt-2">Terminal: {{ $session->terminal_id }} | Started: {{ $session->started_at->format('d M Y H:i') }}</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Opening Cash</div>
                    <div class="text-lg font-semibold">₹{{ number_format($session->opening_cash, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Expected Cash</div>
                    <div class="text-lg font-semibold">₹{{ number_format($expectedCash, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total Sales</div>
                    <div class="text-lg font-semibold">₹{{ number_format($session->total_sales, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Transactions</div>
                    <div class="text-lg font-semibold">{{ $session->total_transactions }}</div>
                </div>
            </div>

            <form action="{{ route('pos.process-close-session') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="closing_cash" class="block text-sm font-medium text-gray-700 mb-2">
                        Closing Cash Amount
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">₹</span>
                        <input type="number" 
                               name="closing_cash" 
                               id="closing_cash" 
                               step="0.01" 
                               min="0"
                               value="{{ old('closing_cash') }}"
                               class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('closing_cash') border-red-500 @enderror"
                               required>
                    </div>
                    @error('closing_cash')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">Count the cash in the drawer and enter the amount.</p>
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (optional)
                    </label>
                    <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Any remarks or discrepancies...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex space-x-4">
                    <a href="{{ route('pos.index') }}" 
                       class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-lg text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg">
                        Close Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

