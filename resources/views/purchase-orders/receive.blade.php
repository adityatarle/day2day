@extends('layouts.app')

@section('title', 'Receive Purchase Order - ' . $purchaseOrder->po_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Receive Materials (Convert to Received Order)</h1>
                <p class="text-gray-600">{{ $purchaseOrder->po_number }} - {{ $purchaseOrder->vendor->name }}</p>
                <p class="text-sm text-blue-600 mt-1">Converting Purchase Order to Received Order</p>
            </div>
        </div>
    </div>

    <!-- Order Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Vendor:</span>
                <p class="font-medium">{{ $purchaseOrder->vendor->name }}</p>
            </div>
            <div>
                <span class="text-gray-600">Branch:</span>
                <p class="font-medium">{{ $purchaseOrder->branch->name }}</p>
            </div>
            <div>
                <span class="text-gray-600">Expected Delivery:</span>
                <p class="font-medium">{{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}</p>
            </div>
            <div>
                <span class="text-gray-600">Total Amount:</span>
                <p class="font-medium text-green-600">₹{{ number_format($purchaseOrder->total_amount, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Receive Form -->
    <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}" class="space-y-8">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Received Items</h2>
            <p class="text-gray-600 mb-6">Enter the actual quantities received for each item. The system will automatically update inventory.</p>
            
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Ordered Quantity</th>
                            <th>Received Quantity *</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->purchaseOrderItems as $index => $item)
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                    <div class="text-sm text-gray-600">{{ ucfirst($item->product->category) }}</div>
                                </td>
                                <td class="font-medium">{{ number_format($item->quantity, 2) }} {{ $item->product->unit }}</td>
                                <td>
                                    <input type="hidden" name="received_items[{{ $index }}][item_id]" value="{{ $item->id }}">
                                    <input type="number" 
                                           name="received_items[{{ $index }}][received_quantity]" 
                                           value="{{ $item->quantity }}" 
                                           step="0.01" 
                                           min="0" 
                                           max="{{ $item->quantity * 1.1 }}"
                                           class="form-input received-quantity-input" 
                                           data-ordered="{{ $item->quantity }}"
                                           data-price="{{ $item->unit_price }}"
                                           data-index="{{ $index }}"
                                           required>
                                    <div class="text-xs text-gray-500 mt-1">Max: {{ number_format($item->quantity * 1.1, 2) }} (10% excess allowed)</div>
                                </td>
                                <td class="font-medium">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="font-semibold text-green-600 received-total-{{ $index }}">₹{{ number_format($item->total_price, 2) }}</td>
                                <td>
                                    <span class="received-status-{{ $index }} inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Complete
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Receiving Summary -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Ordered Total</h4>
                        <p class="text-2xl font-bold text-blue-600">₹{{ number_format($purchaseOrder->subtotal, 2) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="font-semibold text-green-900 mb-2">Received Total</h4>
                        <p class="text-2xl font-bold text-green-600" id="received-total-display">₹{{ number_format($purchaseOrder->subtotal, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Difference</h4>
                        <p class="text-2xl font-bold text-gray-600" id="difference-display">₹0.00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Receiving Notes</h2>
            
            <div class="form-group">
                <label for="receiving_notes" class="form-label">Notes (Optional)</label>
                <textarea name="receiving_notes" id="receiving_notes" rows="3" 
                          class="form-input" 
                          placeholder="Any notes about the delivery, quality, or discrepancies...">{{ old('receiving_notes') }}</textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-success">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Confirm Receipt & Update Inventory
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const receivedInputs = document.querySelectorAll('.received-quantity-input');
    
    receivedInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            updateItemReceiving(this);
            updateReceivingTotals();
        });
    });

    function updateItemReceiving(input) {
        const index = input.dataset.index;
        const ordered = parseFloat(input.dataset.ordered);
        const received = parseFloat(input.value) || 0;
        const price = parseFloat(input.dataset.price);
        
        // Update total for this item
        const receivedTotal = received * price;
        document.querySelector(`.received-total-${index}`).textContent = '₹' + receivedTotal.toFixed(2);
        
        // Update status
        const statusElement = document.querySelector(`.received-status-${index}`);
        if (received === 0) {
            statusElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
            statusElement.textContent = 'Not Received';
        } else if (received < ordered) {
            statusElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800';
            statusElement.textContent = 'Partial';
        } else if (received > ordered) {
            statusElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
            statusElement.textContent = 'Excess';
        } else {
            statusElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
            statusElement.textContent = 'Complete';
        }
    }

    function updateReceivingTotals() {
        let receivedTotal = 0;
        const orderedTotal = {{ $purchaseOrder->subtotal }};
        
        receivedInputs.forEach(function(input) {
            const received = parseFloat(input.value) || 0;
            const price = parseFloat(input.dataset.price);
            receivedTotal += received * price;
        });

        const difference = receivedTotal - orderedTotal;
        
        document.getElementById('received-total-display').textContent = '₹' + receivedTotal.toFixed(2);
        document.getElementById('difference-display').textContent = (difference >= 0 ? '+' : '') + '₹' + difference.toFixed(2);
        
        // Update difference color
        const diffElement = document.getElementById('difference-display');
        if (difference > 0) {
            diffElement.className = 'text-2xl font-bold text-blue-600';
        } else if (difference < 0) {
            diffElement.className = 'text-2xl font-bold text-red-600';
        } else {
            diffElement.className = 'text-2xl font-bold text-gray-600';
        }
    }

    // Initialize calculations
    updateReceivingTotals();
});
</script>
@endsection