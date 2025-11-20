@extends('layouts.app')

@section('title', 'Create Order')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Order</h1>
                <p class="text-gray-600">Create a new customer order</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Details</h2>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form id="order-form" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Select Customer</label>
                    <select id="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Walk-in / New Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                    <input type="text" id="customer_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="If no customer selected">
                </div>

                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">Customer Phone</label>
                    <input type="text" id="customer_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="If no customer selected">
                </div>

                <div>
                    <label for="order_type" class="block text-sm font-medium text-gray-700 mb-1">Order Type</label>
                    <select id="order_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="on_shop">On Shop</option>
                        <option value="online">Online</option>
                        <option value="wholesale">Wholesale</option>
                    </select>
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select id="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="card">Card</option>
                        <option value="cod">COD</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select id="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Items</h3>
                        <p class="text-gray-600 text-sm">Add products to this order</p>
                    </div>
                    <button type="button" id="add-item" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg">Add Item</button>
                </div>

                <div id="items-container" class="space-y-4"></div>

                <div id="item-template" class="hidden">
                    <div class="item-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                <select class="product-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        @foreach($product->branches as $pBranch)
                                            <option value="{{ $product->id }}" data-branch-id="{{ $pBranch->id }}" data-price="{{ $pBranch->pivot->selling_price }}">
                                                {{ $product->name }} - {{ $pBranch->name }} (₹{{ number_format($pBranch->pivot->selling_price, 2) }})
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <input type="number" step="0.01" min="0.01" class="quantity-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (₹)</label>
                                <input type="number" step="0.01" min="0" class="price-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                                <input type="text" class="total-display w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                            </div>
                            <div>
                                <button type="button" class="remove-item w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-sm space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotal-display" class="font-medium">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax (auto-calculated):</span>
                            <span id="tax-display" class="font-medium">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold border-t border-gray-200 pt-2">
                            <span>Total:</span>
                            <span id="total-display" class="text-green-600">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-4">
                <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Create Order</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;
    const itemsContainer = document.getElementById('items-container');
    const itemTemplate = document.getElementById('item-template');
    const addItemBtn = document.getElementById('add-item');

    function addItem() {
        const template = itemTemplate.innerHTML;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.trim();
        const itemRow = wrapper.firstElementChild;
        itemsContainer.appendChild(itemRow);

        const productSelect = itemRow.querySelector('.product-select');
        const quantityInput = itemRow.querySelector('.quantity-input');
        const priceInput = itemRow.querySelector('.price-input');
        const totalDisplay = itemRow.querySelector('.total-display');
        const removeBtn = itemRow.querySelector('.remove-item');

        // Auto price based on selected option
        productSelect.addEventListener('change', function() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.price) {
                priceInput.value = parseFloat(selectedOption.dataset.price).toFixed(2);
                updateItemTotal();
            }
        });

        function updateItemTotal() {
            const qty = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            totalDisplay.value = '₹' + (qty * price).toFixed(2);
            updateTotals();
        }

        quantityInput.addEventListener('input', updateItemTotal);
        priceInput.addEventListener('input', updateItemTotal);

        removeBtn.addEventListener('click', function() {
            itemRow.remove();
            updateTotals();
        });

        itemIndex++;
    }

    function updateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(function(row) {
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            subtotal += qty * price;
        });
        const tax = subtotal * 0.0; // Backend computes actual tax; keep 0 here
        const total = subtotal + tax;
        document.getElementById('subtotal-display').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('tax-display').textContent = '₹' + tax.toFixed(2);
        document.getElementById('total-display').textContent = '₹' + total.toFixed(2);
    }

    addItemBtn.addEventListener('click', addItem);
    addItem();

    document.getElementById('order-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value;
        const xsrfCookie = (document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN=')) || '').split('=')[1];
        const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : '';

        const items = [];
        document.querySelectorAll('.item-row').forEach(function(row) {
            const productId = row.querySelector('.product-select').value;
            const quantity = row.querySelector('.quantity-input').value;
            const unitPrice = row.querySelector('.price-input').value;
            if (productId && quantity && unitPrice) {
                items.push({
                    product_id: parseInt(productId),
                    quantity: parseFloat(quantity),
                    unit_price: parseFloat(unitPrice)
                });
            }
        });

        if (items.length === 0) {
            alert('Please add at least one valid item.');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }

        // Validate that all items have valid data
        for (let i = 0; i < items.length; i++) {
            if (!items[i].product_id || !items[i].quantity || !items[i].unit_price) {
                alert('Please ensure all items have product, quantity, and price filled in.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
            if (parseFloat(items[i].quantity) <= 0) {
                alert('Quantity must be greater than 0 for all items.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
            if (parseFloat(items[i].unit_price) < 0) {
                alert('Unit price cannot be negative.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
        }

        const payload = {
            customer_id: document.getElementById('customer_id').value || null,
            customer_name: document.getElementById('customer_name').value || null,
            customer_phone: document.getElementById('customer_phone').value || null,
            order_type: document.getElementById('order_type').value,
            payment_method: document.getElementById('payment_method').value,
            items: items
        };

        // Show processing state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Processing...';

        try {
            // Use form submission instead of fetch for web route
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("orders.store") }}';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = token;
            form.appendChild(csrfInput);

            // Add customer_id if selected
            if (payload.customer_id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'customer_id';
                input.value = payload.customer_id;
                form.appendChild(input);
            }

            // Add customer details if new customer
            if (!payload.customer_id) {
                if (payload.customer_name) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'customer_name';
                    input.value = payload.customer_name;
                    form.appendChild(input);
                }
                if (payload.customer_phone) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'customer_phone';
                    input.value = payload.customer_phone;
                    form.appendChild(input);
                }
            }

            // Add order details
            const orderTypeInput = document.createElement('input');
            orderTypeInput.type = 'hidden';
            orderTypeInput.name = 'order_type';
            orderTypeInput.value = payload.order_type;
            form.appendChild(orderTypeInput);

            const paymentMethodInput = document.createElement('input');
            paymentMethodInput.type = 'hidden';
            paymentMethodInput.name = 'payment_method';
            paymentMethodInput.value = payload.payment_method;
            form.appendChild(paymentMethodInput);

            // Add branch_id if available
            const branchId = document.getElementById('branch_id')?.value;
            if (branchId) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'branch_id';
                input.value = branchId;
                form.appendChild(input);
            }

            // Add items
            payload.items.forEach((item, index) => {
                const productInput = document.createElement('input');
                productInput.type = 'hidden';
                productInput.name = `items[${index}][product_id]`;
                productInput.value = item.product_id;
                form.appendChild(productInput);

                const quantityInput = document.createElement('input');
                quantityInput.type = 'hidden';
                quantityInput.name = `items[${index}][quantity]`;
                quantityInput.value = item.quantity;
                form.appendChild(quantityInput);

                const priceInput = document.createElement('input');
                priceInput.type = 'hidden';
                priceInput.name = `items[${index}][unit_price]`;
                priceInput.value = item.unit_price;
                form.appendChild(priceInput);
            });

            document.body.appendChild(form);
            form.submit();
        } catch (error) {
            console.error('Error creating order:', error);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            alert('An unexpected error occurred: ' + error.message);
        }
    });
});
</script>
@endsection

