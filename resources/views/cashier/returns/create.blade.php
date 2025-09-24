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
                                <p class="text-sm text-gray-600">Available: {{ $item->quantity }}</p>
                                <p class="font-medium text-gray-900">₹{{ number_format($item->unit_price, 2) }} each</p>
                            </div>
                        </div>
                        
                        <div id="return_details_{{ $index }}" class="hidden space-y-3">
                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="quantity_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Quantity to Return *</label>
                                    <input type="number" 
                                           name="items[{{ $index }}][quantity]" 
                                           id="quantity_{{ $index }}"
                                           min="1" 
                                           max="{{ $item->quantity }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('items.'.$index.'.quantity') border-red-500 @enderror"
                                           onchange="updateSubtotal({{ $index }}, {{ $item->unit_price }})">
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
        quantityInput.value = 1;
        updateSubtotal(index, parseFloat(quantityInput.dataset.unitPrice || 0));
    } else {
        details.classList.add('hidden');
        quantityInput.value = '';
        updateSubtotal(index, 0);
    }
    
    updateTotals();
}

function updateSubtotal(index, unitPrice) {
    const quantityInput = document.getElementById('quantity_' + index);
    const subtotalElement = document.getElementById('subtotal_' + index);
    
    const quantity = parseInt(quantityInput.value) || 0;
    const subtotal = quantity * unitPrice;
    
    subtotalElement.textContent = '₹' + subtotal.toFixed(2);
    updateTotals();
}

function updateTotals() {
    let totalItems = 0;
    let totalRefund = 0;
    
    // Count checked items and calculate total refund
    for (let i = 0; i < {{ $order->orderItems->count() }}; i++) {
        const checkbox = document.getElementById('item_' + i);
        if (checkbox && checkbox.checked) {
            const quantityInput = document.getElementById('quantity_' + i);
            const quantity = parseInt(quantityInput.value) || 0;
            totalItems += quantity;
            
            // Get unit price from the item data
            const unitPrice = parseFloat(quantityInput.closest('.border').querySelector('.text-right p:last-child').textContent.replace('₹', '').replace(',', ''));
            totalRefund += quantity * unitPrice;
        }
    }
    
    document.getElementById('total_items').textContent = totalItems;
    document.getElementById('total_refund').textContent = '₹' + totalRefund.toFixed(2);
}

// Initialize unit prices for calculation
document.addEventListener('DOMContentLoaded', function() {
    @foreach($order->orderItems as $index => $item)
        const quantityInput_{{ $index }} = document.getElementById('quantity_{{ $index }}');
        if (quantityInput_{{ $index }}) {
            quantityInput_{{ $index }}.dataset.unitPrice = {{ $item->unit_price }};
        }
    @endforeach
});
</script>
@endsection