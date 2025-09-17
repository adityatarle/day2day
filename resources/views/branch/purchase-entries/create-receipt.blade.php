@extends('layouts.app')

@section('title', 'Record Material Receipt')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('branch.purchase-entries.show', $purchaseEntry) }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Purchase Entry
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Record Material Receipt for #{{ $purchaseEntry->po_number }}</h1>
                <p class="text-gray-600">Branch: {{ $purchaseEntry->branch->name }}</p>
                <p class="text-sm text-gray-500">Vendor: {{ $purchaseEntry->vendor->name }}</p>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-2">Ready for Receipt</span>
                @if($purchaseEntry->receive_status === 'partial')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Partial Receipt</span>
                @endif
            </div>
        </div>

        <!-- Receipt Summary -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Receipt Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-600">Total Items:</span>
                    <p class="text-lg font-semibold text-gray-900">{{ $purchaseEntry->purchaseOrderItems->count() }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-600">Total Ordered:</span>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($purchaseEntry->purchaseOrderItems->sum('quantity'), 2) }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-600">Already Received:</span>
                    <p class="text-lg font-semibold text-orange-600">{{ number_format($purchaseEntry->purchaseOrderItems->sum('actual_received_quantity') ?? 0, 2) }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-600">Remaining:</span>
                    <p class="text-lg font-semibold text-red-600" id="remaining-total">{{ number_format($purchaseEntry->purchaseOrderItems->sum('quantity') - ($purchaseEntry->purchaseOrderItems->sum('actual_received_quantity') ?? 0), 2) }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('branch.purchase-entries.store-receipt', $purchaseEntry) }}" id="receipt-form" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Delivery Person</label>
                    <input type="text" name="delivery_person" class="form-input" placeholder="Name of the delivery person">
                </div>
                <div>
                    <label class="form-label">Delivery Vehicle</label>
                    <input type="text" name="delivery_vehicle" class="form-input" placeholder="Vehicle number/details">
                </div>
                <div>
                    <label class="form-label">Receipt Type</label>
                    <select name="receipt_type" class="form-input" id="receipt-type">
                        <option value="partial">Partial Receipt</option>
                        <option value="complete">Complete Receipt</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label">Delivery Notes</label>
                    <textarea name="delivery_notes" rows="2" class="form-input" placeholder="Any delivery notes or observations"></textarea>
                </div>
            </div>

            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Already Received</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">This Receipt</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Wt (kg)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Wt (kg)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spoiled</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quality Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($purchaseEntry->purchaseOrderItems as $idx => $item)
                        @php
                            $alreadyReceived = $item->actual_received_quantity ?? 0;
                            $orderedQuantity = $item->fulfilled_quantity ?? $item->quantity;
                            $remainingQuantity = $orderedQuantity - $alreadyReceived;
                        @endphp
                        <tr class="item-row" data-item-id="{{ $item->id }}" data-ordered="{{ $orderedQuantity }}" data-received="{{ $alreadyReceived }}">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->product->category }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ number_format($orderedQuantity, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-orange-600 font-medium">{{ number_format($alreadyReceived, 2) }}</td>
                            <td class="px-4 py-3">
                                <input type="hidden" name="received_items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       max="{{ $remainingQuantity }}"
                                       name="received_items[{{ $idx }}][actual_received_quantity]" 
                                       value="{{ $remainingQuantity > 0 ? $remainingQuantity : 0 }}" 
                                       class="form-input w-28 received-quantity-input"
                                       data-max="{{ $remainingQuantity }}"
                                       placeholder="0.00">
                                <div class="text-xs text-gray-500 mt-1">Max: {{ number_format($remainingQuantity, 2) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][expected_weight]" class="form-input w-28" placeholder="0.00">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][actual_weight]" class="form-input w-28" placeholder="0.00">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][spoiled_quantity]" value="0" class="form-input w-24 spoiled-input">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][damaged_quantity]" value="0" class="form-input w-24 damaged-input">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="received_items[{{ $idx }}][quality_notes]" class="form-input w-full" placeholder="Optional notes">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Receipt Summary -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">This Receipt Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-600">Total Receiving:</span>
                        <p class="text-lg font-semibold text-blue-600" id="total-receiving">0.00</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Total Spoiled:</span>
                        <p class="text-lg font-semibold text-red-600" id="total-spoiled">0.00</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Total Damaged:</span>
                        <p class="text-lg font-semibold text-red-600" id="total-damaged">0.00</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Usable Quantity:</span>
                        <p class="text-lg font-semibold text-green-600" id="total-usable">0.00</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('branch.purchase-entries.show', $purchaseEntry) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success" id="submit-btn">Save Receipt & Update Inventory</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const receivedQuantityInputs = document.querySelectorAll('.received-quantity-input');
    const spoiledInputs = document.querySelectorAll('.spoiled-input');
    const damagedInputs = document.querySelectorAll('.damaged-input');
    const receiptTypeSelect = document.getElementById('receipt-type');
    const submitBtn = document.getElementById('submit-btn');
    
    // Update totals
    function updateTotals() {
        let totalReceiving = 0;
        let totalSpoiled = 0;
        let totalDamaged = 0;
        let totalUsable = 0;
        
        receivedQuantityInputs.forEach((input, index) => {
            const received = parseFloat(input.value) || 0;
            const spoiled = parseFloat(spoiledInputs[index].value) || 0;
            const damaged = parseFloat(damagedInputs[index].value) || 0;
            
            totalReceiving += received;
            totalSpoiled += spoiled;
            totalDamaged += damaged;
            totalUsable += received - spoiled - damaged;
        });
        
        document.getElementById('total-receiving').textContent = totalReceiving.toFixed(2);
        document.getElementById('total-spoiled').textContent = totalSpoiled.toFixed(2);
        document.getElementById('total-damaged').textContent = totalDamaged.toFixed(2);
        document.getElementById('total-usable').textContent = totalUsable.toFixed(2);
        
        // Update remaining total
        const totalOrdered = {{ $purchaseEntry->purchaseOrderItems->sum('quantity') }};
        const alreadyReceived = {{ $purchaseEntry->purchaseOrderItems->sum('actual_received_quantity') ?? 0 }};
        const remaining = totalOrdered - alreadyReceived - totalReceiving;
        document.getElementById('remaining-total').textContent = remaining.toFixed(2);
        
        // Update submit button text based on receipt type
        if (receiptTypeSelect.value === 'partial') {
            submitBtn.textContent = 'Save Partial Receipt';
        } else {
            submitBtn.textContent = 'Complete Receipt & Update Inventory';
        }
    }
    
    // Validate quantities
    function validateQuantities() {
        let isValid = true;
        
        receivedQuantityInputs.forEach((input, index) => {
            const received = parseFloat(input.value) || 0;
            const spoiled = parseFloat(spoiledInputs[index].value) || 0;
            const damaged = parseFloat(damagedInputs[index].value) || 0;
            const maxAllowed = parseFloat(input.dataset.max) || 0;
            
            // Check if received quantity exceeds maximum allowed
            if (received > maxAllowed) {
                input.classList.add('border-red-500');
                isValid = false;
            } else {
                input.classList.remove('border-red-500');
            }
            
            // Check if spoiled + damaged exceeds received
            if (spoiled + damaged > received) {
                spoiledInputs[index].classList.add('border-red-500');
                damagedInputs[index].classList.add('border-red-500');
                isValid = false;
            } else {
                spoiledInputs[index].classList.remove('border-red-500');
                damagedInputs[index].classList.remove('border-red-500');
            }
        });
        
        return isValid;
    }
    
    // Add event listeners
    receivedQuantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const maxAllowed = parseFloat(this.dataset.max) || 0;
            const value = parseFloat(this.value) || 0;
            
            if (value > maxAllowed) {
                this.value = maxAllowed;
            }
            
            updateTotals();
        });
    });
    
    spoiledInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });
    
    damagedInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });
    
    receiptTypeSelect.addEventListener('change', function() {
        if (this.value === 'complete') {
            // Auto-fill remaining quantities for complete receipt
            receivedQuantityInputs.forEach(input => {
                const maxAllowed = parseFloat(input.dataset.max) || 0;
                input.value = maxAllowed;
            });
        }
        updateTotals();
    });
    
    // Form submission validation
    document.getElementById('receipt-form').addEventListener('submit', function(e) {
        if (!validateQuantities()) {
            e.preventDefault();
            alert('Please fix the validation errors before submitting.');
            return false;
        }
        
        const totalReceiving = Array.from(receivedQuantityInputs).reduce((sum, input) => {
            return sum + (parseFloat(input.value) || 0);
        }, 0);
        
        if (totalReceiving === 0) {
            e.preventDefault();
            alert('Please enter quantities for at least one item.');
            return false;
        }
    });
    
    // Initialize totals
    updateTotals();
});
</script>
@endsection

