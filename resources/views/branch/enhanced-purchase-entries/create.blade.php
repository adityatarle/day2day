@extends('layouts.app')

@section('title', 'Create Purchase Entry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Purchase Entry</h1>
            <p class="text-gray-600">Record detailed receipt of materials with quantity tracking and discrepancy reporting</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('enhanced-purchase-entries.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('enhanced-purchase-entries.store') }}" class="space-y-8">
        @csrf
        
        <!-- Order Selection -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Purchase Order</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="purchase_order_id" class="form-label">Purchase Order *</label>
                    <select name="purchase_order_id" id="purchase_order_id" class="form-input" required>
                        <option value="">Select a purchase order</option>
                        @foreach($availablePurchaseOrders as $order)
                            <option value="{{ $order->id }}" 
                                    data-vendor="{{ $order->vendor ? $order->vendor->name : 'Admin' }}"
                                    data-date="{{ $order->created_at->format('M d, Y') }}"
                                    data-items="{{ $order->purchaseOrderItems->count() }}"
                                    {{ request('order_id') == $order->id ? 'selected' : '' }}>
                                {{ $order->po_number }} - {{ $order->vendor ? $order->vendor->name : 'Admin' }} ({{ $order->created_at->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div id="order-details" class="hidden">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900">Order Details</h4>
                        <p class="text-sm text-gray-600" id="vendor-name"></p>
                        <p class="text-sm text-gray-600" id="order-date"></p>
                        <p class="text-sm text-gray-600" id="items-count"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Entry Details -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Entry Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="entry_date" class="form-label">Entry Date *</label>
                    <input type="date" name="entry_date" id="entry_date" value="{{ old('entry_date', now()->format('Y-m-d')) }}" class="form-input" required>
                </div>
                
                <div>
                    <label for="delivery_date" class="form-label">Delivery Date</label>
                    <input type="date" name="delivery_date" id="delivery_date" value="{{ old('delivery_date') }}" class="form-input">
                </div>
                
                <div>
                    <label for="delivery_person" class="form-label">Delivery Person</label>
                    <input type="text" name="delivery_person" id="delivery_person" value="{{ old('delivery_person') }}" class="form-input" placeholder="Driver name">
                </div>
                
                <div>
                    <label for="delivery_vehicle" class="form-label">Delivery Vehicle</label>
                    <input type="text" name="delivery_vehicle" id="delivery_vehicle" value="{{ old('delivery_vehicle') }}" class="form-input" placeholder="Vehicle number">
                </div>
                
                <div class="md:col-span-2">
                    <label for="delivery_notes" class="form-label">Delivery Notes</label>
                    <textarea name="delivery_notes" id="delivery_notes" rows="2" class="form-input" placeholder="Any additional notes about the delivery">{{ old('delivery_notes') }}</textarea>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_partial_receipt" id="is_partial_receipt" value="1" class="form-checkbox" {{ old('is_partial_receipt') ? 'checked' : '' }}>
                    <label for="is_partial_receipt" class="ml-2 text-sm text-gray-700">This is a partial receipt</label>
                </div>
            </div>
        </div>

        <!-- Items Receipt -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Items Receipt</h3>
            <p class="text-sm text-gray-600 mb-6">Enter the actual quantities received for each item. Leave spoiled/damaged quantities as 0 if no issues.</p>
            
            <div id="items-container">
                <p class="text-gray-500 text-center py-8">Please select a purchase order to view items</p>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Create Purchase Entry
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderSelect = document.getElementById('purchase_order_id');
    const orderDetails = document.getElementById('order-details');
    const itemsContainer = document.getElementById('items-container');
    
    orderSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            // Show order details
            document.getElementById('vendor-name').textContent = 'Vendor: ' + selectedOption.dataset.vendor;
            document.getElementById('order-date').textContent = 'Order Date: ' + selectedOption.dataset.date;
            document.getElementById('items-count').textContent = 'Items: ' + selectedOption.dataset.items;
            orderDetails.classList.remove('hidden');
            
            // Load items
            loadOrderItems(this.value);
        } else {
            orderDetails.classList.add('hidden');
            itemsContainer.innerHTML = '<p class="text-gray-500 text-center py-8">Please select a purchase order to view items</p>';
        }
    });
    
    function loadOrderItems(orderId) {
        fetch(`/api/purchase-orders/${orderId}/items`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderItems(data.items);
                } else {
                    itemsContainer.innerHTML = '<p class="text-red-500 text-center py-8">Error loading items</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                itemsContainer.innerHTML = '<p class="text-red-500 text-center py-8">Error loading items</p>';
            });
    }
    
    function renderItems(items) {
        let html = '<div class="space-y-4">';
        
        items.forEach((item, index) => {
            html += `
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="font-medium text-gray-900">${item.product.name}</h4>
                            <p class="text-sm text-gray-600">SKU: ${item.product.sku} | Expected: ${item.quantity} | Price: ₹${item.unit_price}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="form-label">Received Quantity *</label>
                            <input type="number" name="items[${index}][received_quantity]" 
                                   step="0.01" min="0" max="${item.quantity}" 
                                   class="form-input" required>
                            <input type="hidden" name="items[${index}][item_id]" value="${item.id}">
                        </div>
                        
                        <div>
                            <label class="form-label">Spoiled Quantity</label>
                            <input type="number" name="items[${index}][spoiled_quantity]" 
                                   step="0.01" min="0" class="form-input" value="0">
                        </div>
                        
                        <div>
                            <label class="form-label">Damaged Quantity</label>
                            <input type="number" name="items[${index}][damaged_quantity]" 
                                   step="0.01" min="0" class="form-input" value="0">
                        </div>
                        
                        <div>
                            <label class="form-label">Expected Weight (kg)</label>
                            <input type="number" name="items[${index}][expected_weight]" 
                                   step="0.001" min="0" class="form-input">
                        </div>
                        
                        <div>
                            <label class="form-label">Actual Weight (kg)</label>
                            <input type="number" name="items[${index}][actual_weight]" 
                                   step="0.001" min="0" class="form-input">
                        </div>
                        
                        <div class="md:col-span-3">
                            <label class="form-label">Quality Notes</label>
                            <textarea name="items[${index}][quality_notes]" rows="2" 
                                      class="form-input" placeholder="Any quality issues or notes"></textarea>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        itemsContainer.innerHTML = html;
    }
});
</script>
@endsection