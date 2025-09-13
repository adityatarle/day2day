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

        @if($errors->any())
        <div class="alert alert-error">
            <div class="font-semibold mb-2">Please fix the following issues:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li style="white-space: pre-line;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <div class="font-semibold mb-2">Error:</div>
            <div style="white-space: pre-line;">{{ session('error') }}</div>
        </div>
        @endif

        <div id="client-error" class="alert alert-error hidden"></div>

        <!-- Purchase Order Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Purchase Order Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @if(isset($branchRequest) && $branchRequest)
                <div class="form-group md:col-span-3">
                    <label class="form-label">Branch Request Reference</label>
                    <div class="p-4 border rounded-lg bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-600">Linked to Branch Request</div>
                                <div class="font-semibold text-gray-900">#{{ $branchRequest->po_number }} — {{ $branchRequest->branch->name }}</div>
                            </div>
                            <a href="{{ route('admin.branch-orders.show', $branchRequest) }}" class="text-blue-600 hover:text-blue-800 text-sm">View Request →</a>
                        </div>
                        <input type="hidden" name="branch_request_id" value="{{ $branchRequest->id }}">
                    </div>
                </div>
                @endif

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
                                <option value="{{ $branch->id }}" {{ old('branch_id', (isset($branchRequest) && $branchRequest) ? $branchRequest->branch_id : null) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('branch_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Delivery Address Options -->
                <div class="form-group md:col-span-3">
                    <label class="form-label">Delivery Address</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="delivery_address_type" value="admin_main" class="form-radio" {{ old('delivery_address_type', (isset($branchRequest) && $branchRequest) ? 'branch' : 'admin_main') === 'admin_main' ? 'checked' : '' }}>
                                <span>Admin Main Warehouse (default)</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="delivery_address_type" value="branch" class="form-radio" {{ old('delivery_address_type', (isset($branchRequest) && $branchRequest) ? 'branch' : null) === 'branch' ? 'checked' : '' }}>
                                <span>Deliver to Branch</span>
                            </label>
                            <div id="branch-address-picker" class="mt-2 {{ old('delivery_address_type', (isset($branchRequest) && $branchRequest) ? 'branch' : null) === 'branch' ? '' : 'hidden' }}">
                                <select name="ship_to_branch_id" class="form-input">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('ship_to_branch_id', (isset($branchRequest) && $branchRequest) ? $branchRequest->branch_id : null) == $branch->id ? 'selected' : '' }}>{{ $branch->name }} ({{ $branch->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="delivery_address_type" value="custom" class="form-radio" {{ old('delivery_address_type', (isset($branchRequest) && $branchRequest) ? 'branch' : null) === 'custom' ? 'checked' : '' }}>
                                <span>Custom Address</span>
                            </label>
                            <div id="custom-address-editor" class="mt-2 {{ old('delivery_address_type', (isset($branchRequest) && $branchRequest) ? 'branch' : null) === 'custom' ? '' : 'hidden' }}">
                                <textarea name="delivery_address" rows="3" class="form-input" placeholder="Enter delivery address">{{ old('delivery_address') }}</textarea>
                            </div>
                        </div>
                    </div>
                    @error('delivery_address_type')
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
                        <option value="7_days" {{ old('payment_terms') === '7_days' ? 'selected' : '' }}>Net 7 Days</option>
                        <option value="15_days" {{ old('payment_terms') === '15_days' ? 'selected' : '' }}>Net 15 Days</option>
                        <option value="30_days" {{ old('payment_terms') === '30_days' ? 'selected' : '' }}>Net 30 Days</option>
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
            <div id="item-template" class="hidden" aria-hidden="true">
                <div class="item-row border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div>
                            <label class="form-label">Product</label>
                            <select name="items[INDEX][product_id]" class="form-input product-select" required disabled>
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Quantity</label>
                            <input type="number" name="items[INDEX][quantity]" step="0.01" min="0.01" 
                                   class="form-input quantity-input" required disabled>
                        </div>
                        <div>
                            <label class="form-label">Unit Price (₹)</label>
                            <input type="number" name="items[INDEX][unit_price]" step="0.01" min="0" 
                                   class="form-input price-input" required disabled>
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
            <button type="submit" id="submit-btn" class="btn-primary">
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
    const branchAddressPicker = document.getElementById('branch-address-picker');
    const customAddressEditor = document.getElementById('custom-address-editor');
    const clientError = document.getElementById('client-error');
    const submitBtn = document.getElementById('submit-btn');
    let isSubmitting = false;

    // Delivery address toggles
    document.querySelectorAll('input[name="delivery_address_type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'branch') {
                branchAddressPicker.classList.remove('hidden');
                customAddressEditor.classList.add('hidden');
            } else if (this.value === 'custom') {
                branchAddressPicker.classList.add('hidden');
                customAddressEditor.classList.remove('hidden');
            } else {
                branchAddressPicker.classList.add('hidden');
                customAddressEditor.classList.add('hidden');
            }
        });
    });

    // Add first item by default when no prefill
    const hasBranchRequest = !!document.querySelector('input[name="branch_request_id"]');
    if (!hasBranchRequest) {
        addItem();
    }

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

        // Enable inputs now that row is live in the DOM
        itemRow.querySelectorAll('select, input').forEach(function(el){ el.disabled = false; });

        // Load vendor products if vendor is already selected
        const vendorId = vendorSelect.value;
        if (vendorId) {
            loadVendorProductsForItem(vendorId, itemRow);
        }

        itemIndex++;
    }

    function addPrefilledItem(productId, quantity) {
        addItem();
        const rows = document.querySelectorAll('.item-row');
        const itemRow = rows[rows.length - 1];
        const select = itemRow.querySelector('.product-select');
        // Store desired product id to select after vendor products load
        select.dataset.prefill = String(productId);
        const qtyInput = itemRow.querySelector('.quantity-input');
        qtyInput.value = quantity;
        updateItemTotal(itemRow);
        updateTotals();
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
                    let currentValue = select.value;
                    if (select.dataset.prefill) {
                        currentValue = select.dataset.prefill;
                    }
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

                    // If prefilled, trigger price fill and cleanup
                    if (select.dataset.prefill) {
                        const selectedOption = select.options[select.selectedIndex];
                        if (selectedOption && selectedOption.dataset.supplyPrice) {
                            const priceInput = select.closest('.item-row').querySelector('.price-input');
                            priceInput.value = selectedOption.dataset.supplyPrice;
                            updateItemTotal(select.closest('.item-row'));
                            updateTotals();
                        }
                        delete select.dataset.prefill;
                    }
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

                // If prefilled, select and set price
                if (select.dataset.prefill) {
                    const prefillId = select.dataset.prefill;
                    select.value = prefillId;
                    const selectedOption = select.options[select.selectedIndex];
                    if (selectedOption && selectedOption.dataset.supplyPrice) {
                        const priceInput = itemRow.querySelector('.price-input');
                        priceInput.value = selectedOption.dataset.supplyPrice;
                        updateItemTotal(itemRow);
                        updateTotals();
                    }
                    delete select.dataset.prefill;
                }
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

    // Helpers
    function showClientError(message) {
        if (!clientError) return;
        clientError.textContent = message;
        clientError.classList.remove('hidden');
        clientError.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function clearClientError() {
        if (!clientError) return;
        clientError.classList.add('hidden');
        clientError.textContent = '';
    }

    function startSubmitting() {
        if (!submitBtn) return;
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75');
        submitBtn.innerHTML = '<span class="spinner mr-2"></span>Creating...';
    }

    // Form validation + double-submit guard
    document.getElementById('purchase-order-form').addEventListener('submit', function(e) {
        clearClientError();

        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        // Required high-level fields
        if (!vendorSelect.value) {
            e.preventDefault();
            showClientError('Please select a vendor.');
            vendorSelect.focus();
            return;
        }

        const branchSelect = document.getElementById('branch_id') || document.querySelector('input[name="branch_id"]');
        if (branchSelect && !branchSelect.value) {
            e.preventDefault();
            showClientError('Please select a branch.');
            if (branchSelect.focus) branchSelect.focus();
            return;
        }

        const termsSelect = document.getElementById('payment_terms');
        if (!termsSelect.value) {
            e.preventDefault();
            showClientError('Please select payment terms.');
            termsSelect.focus();
            return;
        }

        const deliveryDate = document.getElementById('expected_delivery_date');
        if (!deliveryDate.value) {
            e.preventDefault();
            showClientError('Please select an expected delivery date.');
            deliveryDate.focus();
            return;
        }

        // Check delivery address type
        const deliveryTypeRadios = document.querySelectorAll('input[name="delivery_address_type"]');
        let deliveryTypeSelected = false;
        for (const radio of deliveryTypeRadios) {
            if (radio.checked) {
                deliveryTypeSelected = true;
                // Validate specific delivery address requirements
                if (radio.value === 'branch') {
                    const shipToBranch = document.querySelector('select[name="ship_to_branch_id"]');
                    if (!shipToBranch.value) {
                        e.preventDefault();
                        showClientError('Please select a branch for delivery.');
                        shipToBranch.focus();
                        return;
                    }
                } else if (radio.value === 'custom') {
                    const customAddress = document.querySelector('textarea[name="delivery_address"]');
                    if (!customAddress.value.trim()) {
                        e.preventDefault();
                        showClientError('Please enter a custom delivery address.');
                        customAddress.focus();
                        return;
                    }
                }
                break;
            }
        }
        if (!deliveryTypeSelected) {
            e.preventDefault();
            showClientError('Please select a delivery address type.');
            return;
        }

        // At least one complete item
        const items = document.querySelectorAll('.item-row');
        if (items.length === 0) {
            e.preventDefault();
            showClientError('Please add at least one item to the purchase order.');
            return;
        }

        let hasValidItems = false;
        let itemErrors = [];
        for (let i = 0; i < items.length; i++) {
            const row = items[i];
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');
            
            const productId = productSelect.value;
            const quantity = parseFloat(quantityInput.value);
            const price = parseFloat(priceInput.value);
            
            if (!productId) {
                itemErrors.push(`Item ${i + 1}: Please select a product`);
            }
            if (!quantity || quantity <= 0) {
                itemErrors.push(`Item ${i + 1}: Please enter a valid quantity`);
            }
            if (isNaN(price) || price < 0) {
                itemErrors.push(`Item ${i + 1}: Please enter a valid unit price`);
            }
            
            if (productId && quantity > 0 && price >= 0) {
                hasValidItems = true;
            }
        }
        
        if (itemErrors.length > 0) {
            e.preventDefault();
            showClientError('Item validation errors:\n' + itemErrors.join('\n'));
            return;
        }
        
        if (!hasValidItems) {
            e.preventDefault();
            showClientError('Please ensure at least one item has product, quantity and price.');
            return;
        }

        // Guard + UI feedback
        isSubmitting = true;
        startSubmitting();
        
        console.log('Form submission started with data:', {
            vendor_id: vendorSelect.value,
            branch_id: branchSelect ? branchSelect.value : 'N/A',
            payment_terms: termsSelect.value,
            expected_delivery_date: deliveryDate.value,
            items_count: items.length
        });
    });

    // Prefill items from branch request if available
    @if(isset($branchRequest) && $branchRequest)
        @foreach($branchRequest->purchaseOrderItems as $reqItem)
            addPrefilledItem({{ $reqItem->product_id }}, {{ rtrim(rtrim(number_format($reqItem->quantity, 2, '.', ''), '0'), '.') }});
        @endforeach
    @endif
});
</script>
@endsection