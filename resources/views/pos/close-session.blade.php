@extends('layouts.cashier')

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

            <!-- Summary Cards -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6 grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Opening Cash</div>
                    <div class="text-lg font-semibold">₹{{ number_format($session->opening_cash, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Expected Cash</div>
                    <div class="text-lg font-semibold text-green-600">₹{{ number_format($expectedCash, 2) }}</div>
                    <a href="{{ route('pos.ledger.index') }}" class="text-xs text-blue-600 hover:underline">View cash give/take</a>
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

            <!-- Live Cash Breakdown Counter -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calculator mr-2 text-blue-600"></i>
                    Cash Settlement Breakdown
                </h3>
                
                <div class="space-y-3">
                    <!-- Opening Cash -->
                    <div class="flex justify-between items-center bg-white rounded-lg p-3 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-xs font-semibold text-gray-600">1</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Opening Cash</span>
                        </div>
                        <span class="text-lg font-semibold text-gray-900">+ ₹{{ number_format($breakdown['opening_cash'], 2) }}</span>
                    </div>

                    <!-- Cash Sales -->
                    <div class="flex justify-between items-center bg-white rounded-lg p-3 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-xs font-semibold text-green-600">2</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Cash Sales</span>
                        </div>
                        <span class="text-lg font-semibold text-green-600">+ ₹{{ number_format($breakdown['cash_sales'], 2) }}</span>
                    </div>

                    <!-- Cash Takes -->
                    @if($breakdown['cash_takes'] > 0)
                    <div class="flex justify-between items-center bg-white rounded-lg p-3 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-xs font-semibold text-blue-600">3</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Cash Takes (In)</span>
                        </div>
                        <span class="text-lg font-semibold text-blue-600">+ ₹{{ number_format($breakdown['cash_takes'], 2) }}</span>
                    </div>
                    @endif

                    <!-- Cash Gives (Deducted) -->
                    @if($breakdown['cash_gives'] > 0)
                    <div class="flex justify-between items-center bg-white rounded-lg p-3 border border-red-200 bg-red-50">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-xs font-semibold text-red-600">4</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Cash Gives (Out) - <span class="text-red-600 font-semibold">SETTLED</span></span>
                        </div>
                        <span class="text-lg font-semibold text-red-600">- ₹{{ number_format($breakdown['cash_gives'], 2) }}</span>
                    </div>
                    @endif

                    <!-- Divider -->
                    <div class="border-t-2 border-gray-300 my-2"></div>

                    <!-- Expected Cash Total -->
                    <div class="flex justify-between items-center bg-green-50 rounded-lg p-4 border-2 border-green-300">
                        <div class="flex items-center">
                            <i class="fas fa-equals mr-2 text-green-600"></i>
                            <span class="text-base font-semibold text-gray-900">Expected Cash (Settled)</span>
                        </div>
                        <span class="text-2xl font-bold text-green-600">₹{{ number_format($breakdown['expected_cash'], 2) }}</span>
                    </div>
                </div>

                <!-- Cash Give/Take Details -->
                @if($cashLedgerEntries->count() > 0)
                <div class="mt-4 pt-4 border-t border-blue-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Recent Cash Give/Take Entries:</h4>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach($cashLedgerEntries->take(5) as $entry)
                        <div class="flex justify-between items-center text-xs bg-white rounded p-2 border border-gray-200">
                            <div class="flex items-center">
                                <span class="px-2 py-1 rounded text-xs mr-2 {{ $entry->entry_type === 'take' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($entry->entry_type) }}
                                </span>
                                @if($entry->purpose)
                                <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-600 mr-2">
                                    {{ ucfirst($entry->purpose) }}
                                </span>
                                @endif
                                <span class="text-gray-600">{{ $entry->counterparty ?? 'N/A' }}</span>
                            </div>
                            <span class="font-semibold {{ $entry->entry_type === 'take' ? 'text-blue-600' : 'text-red-600' }}">
                                {{ $entry->entry_type === 'take' ? '+' : '-' }} ₹{{ number_format($entry->amount, 2) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('pos.ledger.index') }}" class="text-xs text-blue-600 hover:underline mt-2 inline-block">
                        View all entries →
                    </a>
                </div>
                @endif
            </div>

            <form action="{{ route('pos.process-close-session') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="closing_cash" class="block text-sm font-medium text-gray-700 mb-2">
                        Closing Cash Amount (Actual Count)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">₹</span>
                        <input type="number" 
                               name="closing_cash" 
                               id="closing_cash" 
                               step="0.01" 
                               min="0"
                               value="{{ old('closing_cash', number_format($expectedCash, 2, '.', '')) }}"
                               class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('closing_cash') border-red-500 @enderror"
                               required
                               oninput="updateCashDifference()">
                    </div>
                    @error('closing_cash')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">Count the cash in the drawer and enter the actual amount.</p>
                    
                    <!-- Live Cash Difference Display -->
                    <div id="cash_difference_display" class="mt-3 p-3 rounded-lg border hidden">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Difference:</span>
                            <span id="cash_difference_amount" class="text-lg font-bold"></span>
                        </div>
                    </div>
                </div>

                @include('pos.components.cash-breakdown-input', [
                    'context' => 'close-page',
                    'targetInputId' => 'closing_cash'
                ])

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

<script>
function setupCashBreakdownWidgets() {
    const containers = document.querySelectorAll('[data-cash-breakdown]');

    const formatAmount = (amount) => '₹' + amount.toLocaleString('en-IN');

    containers.forEach(container => {
        const targetInputId = container.dataset.targetInput;
        const targetInput = targetInputId ? document.getElementById(targetInputId) : null;
        const applyButton = container.querySelector('[data-apply-breakdown]');
        const summaryEl = container.querySelector('[data-breakdown-summary]');
        const totalDisplays = container.querySelectorAll('[data-total-display]');

        const recalc = () => {
            let total = 0;

            container.querySelectorAll('.denomination-input').forEach(input => {
                const denomination = parseFloat(input.dataset.denomination);
                const count = parseInt(input.value || 0, 10);
                const amount = denomination * (isNaN(count) ? 0 : count);
                const lineTotalEl = input.parentElement.querySelector('[data-line-total]');

                if (lineTotalEl) {
                    lineTotalEl.textContent = amount > 0 ? formatAmount(amount) : '₹0';
                }

                if (!isNaN(count) && count > 0) {
                    total += amount;
                }
            });

            totalDisplays.forEach(display => {
                display.textContent = formatAmount(total);
            });

            if (summaryEl) {
                const summaryParts = [];
                container.querySelectorAll('.denomination-input').forEach(input => {
                    const denomination = parseFloat(input.dataset.denomination);
                    const count = parseInt(input.value || 0, 10);
                    if (!isNaN(count) && count > 0) {
                        summaryParts.push(`${count} x ₹${denomination}`);
                    }
                });

                summaryEl.textContent = summaryParts.length
                    ? summaryParts.join(', ')
                    : 'No denominations entered yet.';
            }

            container.dataset.breakdownTotal = total;
        };

        container.addEventListener('input', event => {
            if (event.target.classList.contains('denomination-input')) {
                recalc();
            }
        });

        if (applyButton && targetInput) {
            applyButton.addEventListener('click', () => {
                const total = parseFloat(container.dataset.breakdownTotal || 0);
                if (total > 0) {
                    targetInput.value = Math.round(total);
                    targetInput.dispatchEvent(new Event('input'));
                }
            });
        }

        recalc();
    });
}

function updateCashDifference() {
    const closingCash = parseFloat(document.getElementById('closing_cash').value) || 0;
    const expectedCash = {{ $expectedCash }};
    const difference = closingCash - expectedCash;
    
    const displayDiv = document.getElementById('cash_difference_display');
    const amountSpan = document.getElementById('cash_difference_amount');
    
    if (closingCash > 0) {
        displayDiv.classList.remove('hidden');
        
        if (difference > 0) {
            displayDiv.className = 'mt-3 p-3 rounded-lg border border-green-300 bg-green-50';
            amountSpan.className = 'text-lg font-bold text-green-600';
            amountSpan.textContent = '+ ₹' + Math.abs(difference).toFixed(2) + ' (Over)';
        } else if (difference < 0) {
            displayDiv.className = 'mt-3 p-3 rounded-lg border border-red-300 bg-red-50';
            amountSpan.className = 'text-lg font-bold text-red-600';
            amountSpan.textContent = '- ₹' + Math.abs(difference).toFixed(2) + ' (Short)';
        } else {
            displayDiv.className = 'mt-3 p-3 rounded-lg border border-blue-300 bg-blue-50';
            amountSpan.className = 'text-lg font-bold text-blue-600';
            amountSpan.textContent = '₹0.00 (Perfect Match)';
        }
    } else {
        displayDiv.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    setupCashBreakdownWidgets();
    updateCashDifference();
});
</script>
@endsection

