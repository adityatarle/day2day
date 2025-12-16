@php
    $denominations = $denominations ?? [2000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
    $context = $context ?? 'default';
    $targetInputId = $targetInputId ?? 'closing-cash';
@endphp

<div 
    class="bg-white border border-purple-100 rounded-lg p-4 space-y-4"
    data-cash-breakdown="{{ $context }}"
    data-target-input="{{ $targetInputId }}"
>
    <div class="flex items-center justify-between">
        <div>
            <h4 class="text-sm font-semibold text-gray-900">Cash Breakdown</h4>
            <p class="text-xs text-gray-500">Enter the count of each note/coin to log physical cash.</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-gray-500 block">Breakdown Total</span>
            <span class="text-lg font-bold text-gray-900" data-total-display>₹0</span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($denominations as $denomination)
            <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">₹{{ number_format($denomination) }}</p>
                    <p class="text-xs text-gray-500">Notes / coins</p>
                </div>
                <div class="text-right">
                    <input 
                        type="number" 
                        min="0" 
                        inputmode="numeric"
                        name="cash_breakdown[{{ $denomination }}]"
                        class="denomination-input w-20 border border-gray-300 rounded-lg px-2 py-1 text-right focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        data-denomination="{{ $denomination }}"
                    >
                    <p class="text-xs text-gray-500 mt-1" data-line-total>₹0</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-xs text-gray-500" data-breakdown-summary>No denominations entered yet.</div>

    <button 
        type="button" 
        class="text-sm font-medium text-purple-600 hover:text-purple-700"
        data-apply-breakdown
    >
        Use breakdown total for closing cash
    </button>
</div>




