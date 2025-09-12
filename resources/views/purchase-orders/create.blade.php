@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('purchase-orders.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Purchase Order</h1>
                <p class="text-gray-600">Create a new purchase order for vendor procurement</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('purchase-orders.store') }}" id="purchase-order-form" class="space-y-8">
        @csrf

        <!-- Purchase Order Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Purchase Order Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="form-group">
                    <label for="vendor_id" class="form-label">Vendor *</label>
                    <select name="vendor_id" id="vendor_id" class="form-input @error('vendor_id') border-red-500 @enderror" required>
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id', request('vendor')) == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }} ({{ $vendor->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="branch_id" class="form-label">Branch *</label>
                    @if(isset($selectedBranch) && $selectedBranch)
                        <select id="branch_id_display" class="form-input bg-gray-100" disabled>
                            @foreach($branches as $branch)
                                @if($branch->id == $selectedBranch)
                                    <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" name="branch_id" value="{{ $selectedBranch }}">
                        <p class="text-sm text-gray-500 mt-1">Branch is pre-selected for your role</p>
                    @else
                        <select name="branch_id" id="branch_id" class="form-input @error('branch_id') border-red-500 @enderror" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('branch_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="expected_delivery_date" class="form-label">Expected Delivery Date *</label>
                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" 
                           value="{{ old('expected_delivery_date', now()->addDays(3)->format('Y-m-d')) }}" 
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="form-input @error('expected_delivery_date') border-red-500 @enderror" required>
                    @error('expected_delivery_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="payment_terms" class="form-label">Payment Terms *</label>
                    <select name="payment_terms" id="payment_terms" class="form-input @error('payment_terms') border-red-500 @enderror" required>
                        <option value="">Select Payment Terms</option>
                        <option value="immediate" {{ old('payment_terms') === 'immediate' ? 'selected' : '' }}>Immediate</option>
                        <option value="net_7" {{ old('payment_terms') === 'net_7' ? 'selected' : '' }}>Net 7 Days</option>
                        <option value="net_15" {{ old('payment_terms') === 'net_15' ? 'selected' : '' }}>Net 15 Days</option>
                        <option value="net_30" {{ old('payment_terms') === 'net_30' ? 'selected' : '' }}>Net 30 Days</option>
                        <option value="net_60" {{ old('payment_terms') === 'net_60' ? 'selected' : '' }}>Net 60 Days</option>
                        <option value="advance" {{ old('payment_terms') === 'advance' ? 'selected' : '' }}>Advance Payment</option>
                    </select>
                    @error('payment_terms')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="transport_cost" class="form-label">Transport Cost (₹)</label>
                    <input type="number" name="transport_cost" id="transport_cost" value="{{ old('transport_cost', 0) }}" 
                           step="0.01" min="0" class="form-input @error('transport_cost') border-red-500 @enderror">
                    @error('transport_cost')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3" 
                              class="form-input @error('notes') border-red-500 @enderror" 
                              placeholder="Any special instructions or notes">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Purchase Order Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Purchase Order Items</h2>
                    <p class="text-gray-600">Add products to this purchase order</p>
                </div>
                <button type="button" id="add-item" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Item
                </button>
            </div>

            <div id="items-container" class="space-y-4">
                <!-- Items will be added here dynamically -->
            </div>

            <!-- Totals -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <div class="flex justify-end">
                    <div class="w-full max-w-md space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotal-display" class="font-medium">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">GST (18%):</span>
                            <span id="tax-display" class="font-medium">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Transport Cost:</span>
                            <span id="transport-display" class="font-medium">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold border-t border-gray-200 pt-3">
                            <span class="text-gray-900">Total Amount:</span>
                            <span id="total-display" class="text-green-600">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Template (hidden) -->
            <div id="item-template" class="hidden">
                <div class="item-row border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div>
                            <label class="form-label">Product</label>
                            <select name="items[INDEX][product_id]" class="form-input product-select" required>
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Quantity</label>
                            <input type="number" name="items[INDEX][quantity]" step="0.01" min="0.01" 
                                   class="form-input quantity-input" required>
                        </div>
                        <div>
                            <label class="form-label">Unit Price (₹)</label>
                            <input type="number" name="items[INDEX][unit_price]" step="0.01" min="0" 
                                   class="form-input price-input" required>
                        </div>
                        <div>
                            <label class="form-label">Total Price</label>
                            <input type="text" class="form-input total-price-display" readonly>
                        </div>
                        <div>
                            <button type="button" class="remove-item w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg transition-colors">
                                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Create Purchase Order
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;
    const addItemBtn = document.getElementById('add-item');
    const itemsContainer = document.getElementById('items-container');
    const itemTemplate = document.getElementById('item-template');
    const vendorSelect = document.getElementById('vendor_id');
    const transportCostInput = document.getElementById('transport_cost');

    // Add first item by default
    addItem();

    addItemBtn.addEventListener('click', addItem);

    // Update totals when transport cost changes
    transportCostInput.addEventListener('input', updateTotals);

    // Load vendor products when vendor is selected
    vendorSelect.addEventListener('change', function() {
        const vendorId = this.value;
        if (vendorId) {
            loadVendorProducts(vendorId);
        } else {
            clearProductOptions();
        }
    });

    function addItem() {
        const template = itemTemplate.innerHTML;
        const newItem = template.replace(/INDEX/g, itemIndex);
        
        const div = document.createElement('div');
        div.innerHTML = newItem;
        const itemRow = div.firstElementChild;
        itemsContainer.appendChild(itemRow);

        // Add event listeners
        const removeBtn = itemRow.querySelector('.remove-item');
        const quantityInput = itemRow.querySelector('.quantity-input');
        const priceInput = itemRow.querySelector('.price-input');

        removeBtn.addEventListener('click', function() {
            itemRow.remove();
            updateTotals();
        });

        quantityInput.addEventListener('input', function() {
            updateItemTotal(itemRow);
            updateTotals();
        });

        priceInput.addEventListener('input', function() {
            updateItemTotal(itemRow);
            updateTotals();
        });

        // Load vendor products if vendor is already selected
        const vendorId = vendorSelect.value;
        if (vendorId) {
            loadVendorProductsForItem(vendorId, itemRow);
        }

        itemIndex++;
    }

    function updateItemTotal(itemRow) {
        const quantity = parseFloat(itemRow.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(itemRow.querySelector('.price-input').value) || 0;
        const total = quantity * price;
        
        itemRow.querySelector('.total-price-display').value = '₹' + total.toFixed(2);
    }

    function updateTotals() {
        let subtotal = 0;
        
        document.querySelectorAll('.item-row').forEach(function(row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            subtotal += quantity * price;
        });

        const transportCost = parseFloat(transportCostInput.value) || 0;
        const taxAmount = subtotal * 0.18; // 18% GST
        const totalAmount = subtotal + taxAmount + transportCost;

        document.getElementById('subtotal-display').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('tax-display').textContent = '₹' + taxAmount.toFixed(2);
        document.getElementById('transport-display').textContent = '₹' + transportCost.toFixed(2);
        document.getElementById('total-display').textContent = '₹' + totalAmount.toFixed(2);
    }

    function loadVendorProducts(vendorId) {
        fetch(`/api/vendors/${vendorId}/products`)
            .then(response => response.json())
            .then(products => {
                document.querySelectorAll('.product-select').forEach(function(select) {
                    const currentValue = select.value;
                    select.innerHTML = '<option value="">Select Product</option>';
                    
                    products.forEach(function(product) {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = `${product.name} (${product.category}) - ₹${product.supply_price}`;
                        option.dataset.supplyPrice = product.supply_price;
                        if (product.id == currentValue) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });

                    // Auto-fill price when product is selected
                    select.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        if (selectedOption.dataset.supplyPrice) {
                            const priceInput = this.closest('.item-row').querySelector('.price-input');
                            priceInput.value = selectedOption.dataset.supplyPrice;
                            updateItemTotal(this.closest('.item-row'));
                            updateTotals();
                        }
                    });
                });
            })
            .catch(error => {
                console.error('Error loading vendor products:', error);
            });
    }

    function loadVendorProductsForItem(vendorId, itemRow) {
        fetch(`/api/vendors/${vendorId}/products`)
            .then(response => response.json())
            .then(products => {
                const select = itemRow.querySelector('.product-select');
                select.innerHTML = '<option value="">Select Product</option>';
                
                products.forEach(function(product) {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = `${product.name} (${product.category}) - ₹${product.supply_price}`;
                    option.dataset.supplyPrice = product.supply_price;
                    select.appendChild(option);
                });

                // Auto-fill price when product is selected
                select.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.dataset.supplyPrice) {
                        const priceInput = itemRow.querySelector('.price-input');
                        priceInput.value = selectedOption.dataset.supplyPrice;
                        updateItemTotal(itemRow);
                        updateTotals();
                    }
                });
            })
            .catch(error => {
                console.error('Error loading vendor products:', error);
            });
    }

    function clearProductOptions() {
        document.querySelectorAll('.product-select').forEach(function(select) {
            select.innerHTML = '<option value="">Select Vendor First</option>';
        });
    }

    // Form validation
    document.getElementById('purchase-order-form').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.item-row');
        if (items.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the purchase order.');
            return;
        }

        let hasValidItems = false;
        items.forEach(function(row) {
            const productId = row.querySelector('.product-select').value;
            const quantity = row.querySelector('.quantity-input').value;
            const price = row.querySelector('.price-input').value;
            
            if (productId && quantity && price) {
                hasValidItems = true;
            }
        });

        if (!hasValidItems) {
            e.preventDefault();
            alert('Please ensure all items have product, quantity, and price filled.');
        }
    });
});
</script>
@endsection