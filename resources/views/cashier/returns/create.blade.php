@extends('layouts.cashier')

@section('title', 'Create Return')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-orange-600 to-red-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Create Return</h1>
                <p class="text-orange-100">Order #{{ $order->id }} - {{ $order->order_number }}</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">₹{{ number_format($order->total_amount, 2) }}</div>
                <div class="text-orange-100 text-sm">Order Total</div>
            </div>
        </div>
    </div>

    <!-- Order Information -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-600">Customer</label>
                <p class="text-gray-900">{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Order Date</label>
                <p class="text-gray-900">{{ $order->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Order Total</label>
                <p class="text-gray-900 font-semibold">₹{{ number_format($order->total_amount, 2) }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Status</label>
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Return Form -->
    <form method="POST" action="{{ route('cashier.returns.store', $order) }}" class="space-y-6">
        @csrf
        
        <!-- Return Reason -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Return Reason</h3>
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Return *</label>
                <textarea name="reason" id="reason" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('reason') border-red-500 @enderror"
                          placeholder="Please provide a detailed reason for the return...">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Items to Return -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Items to Return</h3>
            <p class="text-sm text-gray-600 mb-4">Select the items and quantities to return:</p>
            
            <div class="space-y-4">
                @foreach($order->orderItems as $index => $item)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       name="items[{{ $index }}][include]" 
                                       id="item_{{ $index }}"
                                       value="1"
                                       class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                       onchange="toggleItemReturn({{ $index }})">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $item->product->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $item->product->code }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">
                                    Available: {{ $item->quantity }} 
                                    @if($item->unit)
                                        {{ $item->unit }}
                                    @elseif($item->product->bill_by === 'weight')
                                        {{ $item->product->weight_unit }}
                                    @else
                                        {{ $item->product->weight_unit }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">
                                    @if($item->billed_weight)
                                        ({{ number_format($item->billed_weight, 3) }} kg)
                                    @endif
                                </p>
                                <p class="font-medium text-gray-900">₹{{ number_format($item->unit_price, 2) }} 
                                    @if($item->unit)
                                        /{{ $item->unit }}
                                    @elseif($item->product->bill_by === 'weight')
                                        /{{ $item->product->weight_unit }}
                                    @else
                                        /{{ $item->product->weight_unit }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">Total Paid: ₹{{ number_format($item->total_price, 2) }}</p>
                            </div>
                        </div>
                        
                        <div id="return_details_{{ $index }}" class="hidden space-y-3">
                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="quantity_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">
                                        Quantity to Return * 
                                        <span class="text-xs text-gray-500">
                                            (in {{ $item->unit ?? ($item->product->bill_by === 'weight' ? $item->product->weight_unit : $item->product->weight_unit) }})
                                        </span>
                                    </label>
                                    <input type="number" 
                                           name="items[{{ $index }}][quantity]" 
                                           id="quantity_{{ $index }}"
                                           min="0.01" 
                                           step="0.01"
                                           max="{{ $item->quantity }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('items.'.$index.'.quantity') border-red-500 @enderror"
                                           onchange="updateSubtotal({{ $index }})"
                                           data-original-quantity="{{ $item->quantity }}"
                                           data-original-total="{{ $item->total_price }}">
                                    @error('items.'.$index.'.quantity')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="item_reason_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Item-specific Reason</label>
                                    <input type="text" 
                                           name="items[{{ $index }}][reason]" 
                                           id="item_reason_{{ $index }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                           placeholder="e.g., Defective, Wrong size, etc.">
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                                <span id="subtotal_{{ $index }}" class="font-bold text-orange-600">₹0.00</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @error('items')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Payment Breakdown Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Original Payment Breakdown</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-white rounded-lg p-4 border border-blue-100">
                    <div class="text-sm text-gray-600 mb-1">Total Paid</div>
                    <div class="text-xl font-bold text-gray-900">₹{{ number_format($paymentBreakdown['total'], 2) }}</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-green-100">
                    <div class="text-sm text-gray-600 mb-1">Cash Payment</div>
                    <div class="text-xl font-bold text-green-600">₹{{ number_format($paymentBreakdown['cash'], 2) }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ number_format($paymentBreakdown['cash_percentage'], 1) }}%</div>
                </div>
                <div class="bg-white rounded-lg p-4 border border-purple-100">
                    <div class="text-sm text-gray-600 mb-1">UPI Payment</div>
                    <div class="text-xl font-bold text-purple-600">₹{{ number_format($paymentBreakdown['upi'], 2) }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ number_format($paymentBreakdown['upi_percentage'], 1) }}%</div>
                </div>
            </div>
        </div>

        <!-- Refund Method Selection -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Refund Method</h3>
            <div class="space-y-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Refund Method *</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors refund-method-option" data-method="cash">
                        <input type="radio" name="refund_method" value="cash" required
                               class="sr-only peer"
                               {{ old('refund_method', 'cash') === 'cash' ? 'checked' : '' }}
                               onchange="updateRefundBreakdown()">
                        <div class="flex items-center space-x-3 w-full">
                            <div class="flex-shrink-0">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-green-600 peer-checked:bg-green-600 flex items-center justify-center">
                                    <div class="w-3 h-3 bg-white rounded-full hidden peer-checked:block"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">Cash Refund</div>
                                <div class="text-sm text-gray-500">Refund through cash payment</div>
                            </div>
                            <div class="text-green-600">
                                <i class="fas fa-money-bill-wave text-2xl"></i>
                            </div>
                        </div>
                    </label>
                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors refund-method-option" data-method="upi">
                        <input type="radio" name="refund_method" value="upi" required
                               class="sr-only peer"
                               {{ old('refund_method') === 'upi' ? 'checked' : '' }}
                               onchange="updateRefundBreakdown()">
                        <div class="flex items-center space-x-3 w-full">
                            <div class="flex-shrink-0">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-purple-600 peer-checked:bg-purple-600 flex items-center justify-center">
                                    <div class="w-3 h-3 bg-white rounded-full hidden peer-checked:block"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">UPI Refund</div>
                                <div class="text-sm text-gray-500">Refund through UPI payment</div>
                            </div>
                            <div class="text-purple-600">
                                <i class="fas fa-mobile-alt text-2xl"></i>
                            </div>
                        </div>
                    </label>
                </div>
                @error('refund_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Return Summary -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Return Summary</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Items to Return:</span>
                    <span id="total_items" class="font-medium">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Refund Amount:</span>
                    <span id="total_refund" class="text-xl font-bold text-red-600">₹0.00</span>
                </div>
                <div class="border-t pt-3 mt-3">
                    <div class="text-sm font-medium text-gray-700 mb-2">Refund Breakdown:</div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-green-50 rounded-lg p-3 border border-green-200" id="cash_refund_container">
                            <div class="text-xs text-gray-600 mb-1">Cash Refund</div>
                            <div id="cash_refund" class="text-lg font-bold text-green-600">₹0.00</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-3 border border-purple-200" id="upi_refund_container">
                            <div class="text-xs text-gray-600 mb-1">UPI Refund</div>
                            <div id="upi_refund" class="text-lg font-bold text-purple-600">₹0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex space-x-4">
            <button type="submit" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                <i class="fas fa-undo mr-2"></i>Create Return
            </button>
            <a href="{{ route('cashier.orders.show', $order) }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-lg transition-colors text-center">
                <i class="fas fa-arrow-left mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

<script>
function toggleItemReturn(index) {
    const checkbox = document.getElementById('item_' + index);
    const details = document.getElementById('return_details_' + index);
    const quantityInput = document.getElementById('quantity_' + index);
    
    if (checkbox.checked) {
        details.classList.remove('hidden');
        // Set initial quantity to the full available quantity
        quantityInput.value = quantityInput.dataset.originalQuantity || '';
        updateSubtotal(index);
    } else {
        details.classList.add('hidden');
        quantityInput.value = '';
        updateSubtotal(index);
    }
    
    updateTotals();
}

function updateSubtotal(index) {
    const quantityInput = document.getElementById('quantity_' + index);
    const subtotalElement = document.getElementById('subtotal_' + index);
    
    const returnQuantity = parseFloat(quantityInput.value) || 0;
    const originalQuantity = parseFloat(quantityInput.dataset.originalQuantity) || 0;
    const originalTotal = parseFloat(quantityInput.dataset.originalTotal) || 0;
    
    // Use proportional calculation: (return_quantity / original_quantity) * original_total_price
    // This handles unit conversion correctly (grams vs kg, etc.)
    let subtotal = 0;
    if (originalQuantity > 0) {
        const proportion = returnQuantity / originalQuantity;
        subtotal = proportion * originalTotal;
    }
    
    subtotalElement.textContent = '₹' + subtotal.toFixed(2);
    updateTotals();
}

function updateTotals() {
    let totalItems = 0;
    let totalRefund = 0;
    
    // Count checked items and calculate total refund using proportional method
    for (let i = 0; i < {{ $order->orderItems->count() }}; i++) {
        const checkbox = document.getElementById('item_' + i);
        if (checkbox && checkbox.checked) {
            const quantityInput = document.getElementById('quantity_' + i);
            const returnQuantity = parseFloat(quantityInput.value) || 0;
            const originalQuantity = parseFloat(quantityInput.dataset.originalQuantity) || 0;
            const originalTotal = parseFloat(quantityInput.dataset.originalTotal) || 0;
            
            totalItems += returnQuantity;
            
            // Use proportional calculation: (return_quantity / original_quantity) * original_total_price
            if (originalQuantity > 0) {
                const proportion = returnQuantity / originalQuantity;
                totalRefund += proportion * originalTotal;
            }
        }
    }
    
    document.getElementById('total_items').textContent = totalItems.toFixed(2);
    document.getElementById('total_refund').textContent = '₹' + totalRefund.toFixed(2);
    
    updateRefundBreakdown();
}

function updateRefundBreakdown() {
    const totalRefund = parseFloat(document.getElementById('total_refund').textContent.replace('₹', '').replace(',', '')) || 0;
    const refundMethod = document.querySelector('input[name="refund_method"]:checked')?.value || 'cash';
    
    let cashRefund = 0;
    let upiRefund = 0;
    
    if (refundMethod === 'cash') {
        cashRefund = totalRefund;
        upiRefund = 0;
    } else {
        cashRefund = 0;
        upiRefund = totalRefund;
    }
    
    document.getElementById('cash_refund').textContent = '₹' + cashRefund.toFixed(2);
    document.getElementById('upi_refund').textContent = '₹' + upiRefund.toFixed(2);
    
    // Update visual styling based on selected method
    const cashContainer = document.getElementById('cash_refund_container');
    const upiContainer = document.getElementById('upi_refund_container');
    
    if (refundMethod === 'cash') {
        cashContainer.classList.add('ring-2', 'ring-green-500');
        upiContainer.classList.remove('ring-2', 'ring-purple-500');
    } else {
        upiContainer.classList.add('ring-2', 'ring-purple-500');
        cashContainer.classList.remove('ring-2', 'ring-green-500');
    }
}

// Initialize data attributes for calculation and refund method selection
document.addEventListener('DOMContentLoaded', function() {
    @foreach($order->orderItems as $index => $item)
        const quantityInput_{{ $index }} = document.getElementById('quantity_{{ $index }}');
        if (quantityInput_{{ $index }}) {
            // Store original quantity and total for proportional calculation
            quantityInput_{{ $index }}.dataset.originalQuantity = {{ $item->quantity }};
            quantityInput_{{ $index }}.dataset.originalTotal = {{ $item->total_price }};
        }
    @endforeach
    
    // Update refund method option styling
    const refundMethodOptions = document.querySelectorAll('.refund-method-option');
    refundMethodOptions.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        if (radio.checked) {
            option.classList.add('border-2', 'ring-2');
            if (radio.value === 'cash') {
                option.classList.add('border-green-500', 'ring-green-200');
            } else {
                option.classList.add('border-purple-500', 'ring-purple-200');
            }
        }
        
        radio.addEventListener('change', function() {
            refundMethodOptions.forEach(opt => {
                opt.classList.remove('border-2', 'ring-2', 'border-green-500', 'ring-green-200', 'border-purple-500', 'ring-purple-200');
            });
            if (this.checked) {
                option.classList.add('border-2', 'ring-2');
                if (this.value === 'cash') {
                    option.classList.add('border-green-500', 'ring-green-200');
                } else {
                    option.classList.add('border-purple-500', 'ring-purple-200');
                }
            }
        });
    });
    
    // Initialize refund breakdown
    updateRefundBreakdown();
});
</script>
@endsection