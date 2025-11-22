@extends('layouts.cashier')

@section('title', 'POS System')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-3 sm:py-4 space-y-3 sm:space-y-0">
                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">POS System</h1>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs sm:text-sm font-medium rounded-full self-start sm:self-auto">
                        {{ $branch->name }} - {{ $branch->city->name ?? 'No City' }}
                    </span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                    @if($currentSession)
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs sm:text-sm font-medium text-gray-700">Session Active</span>
                            <span class="text-xs sm:text-sm text-gray-500 hidden sm:inline">Terminal: {{ $currentSession->terminal_id }}</span>
                            <span class="text-xs sm:text-sm text-purple-600 hidden sm:inline">| Handled by: {{ $currentSession->handled_by }}</span>
                        </div>
                        <a href="{{ route('pos.close-session') }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 sm:px-4 rounded-lg font-medium text-sm sm:text-base text-center">
                            Close Session
                        </a>
                    @else
                        <a href="{{ route('pos.start-session') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 sm:px-4 rounded-lg font-medium text-sm sm:text-base text-center">
                            Start Session
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($currentSession)
            <!-- Session Info Panel -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-4 sm:p-6 text-white mb-6 shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-4 mb-3 sm:mb-0">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-check text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Active POS Session</h3>
                            <p class="text-purple-100 text-sm">
                                <i class="fas fa-user mr-1"></i>
                                Handled by: <span class="font-semibold">{{ $currentSession->handled_by }}</span>
                            </p>
                            <p class="text-purple-100 text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                Started: {{ $currentSession->started_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-2xl font-bold">₹{{ number_format($currentSession->total_sales, 2) }}</div>
                            <div class="text-purple-100 text-sm">Session Sales</div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold">{{ $currentSession->total_transactions }}</div>
                            <div class="text-purple-100 text-sm">Transactions</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- POS Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Left Panel - Product Selection -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border order-2 lg:order-1">
                    <div class="p-4 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Products</h2>
                        
                        <!-- Search and Filters -->
                        <div class="mb-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                            <input type="text" id="product-search" placeholder="Search products..." 
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                            </select>
                        </div>

                        <!-- Category Tabs -->
                        <div id="category-tabs" class="flex flex-wrap gap-2 mb-3"></div>

                        <!-- Product Grid -->
                        <div id="products-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4"></div>
                    </div>
                </div>

                <!-- Right Panel - Cart and Checkout -->
                <div class="bg-white rounded-xl shadow-sm border order-1 lg:order-2">
                    <div class="p-4 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Cart</h2>
                        
                        <!-- Customer Selection -->
                        <div class="mb-3 sm:mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Customer</label>
                            <select id="customer-select" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Walk-in Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cart Items -->
                        <div id="cart-items" class="mb-3 sm:mb-4 max-h-48 sm:max-h-64 overflow-y-auto">
                            <!-- Cart items will be populated here -->
                        </div>

                        <!-- Totals -->
                        <div class="border-t pt-3 sm:pt-4 mb-3 sm:mb-4">
                            <div class="flex justify-between text-xs sm:text-sm mb-1 sm:mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal">₹0.00</span>
                            </div>
                            <div class="flex justify-between text-xs sm:text-sm mb-1 sm:mb-2">
                                <span>Discount:</span>
                                <input type="number" id="discount-amount" value="0" min="0" step="0.01"
                                       class="w-16 sm:w-20 text-right border border-gray-300 rounded px-1 sm:px-2 py-1 text-xs sm:text-sm">
                            </div>
                            <div class="flex justify-between text-xs sm:text-sm mb-1 sm:mb-2">
                                <span>Tax:</span>
                                <span id="tax-amount">₹0.00</span>
                            </div>
                            <div class="flex justify-between text-sm sm:text-lg font-bold border-t pt-1 sm:pt-2">
                                <span>Total:</span>
                                <span id="total-amount">₹0.00</span>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-3 sm:mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Payment Method</label>
                            <select id="payment-method" class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="upi">UPI</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>

                        <!-- Payment Details -->
                        <div id="payment-details" class="mb-3 sm:mb-4 space-y-2 sm:space-y-3">
                            <div id="amount-received-wrapper">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Amount Received</label>
                                <input type="number" id="amount-received" value="0" min="0" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="mt-1 text-xs sm:text-sm text-gray-600">Change: <span id="change-amount">₹0.00</span></div>
                            </div>
                            <div id="reference-number-wrapper" class="hidden">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Reference Number</label>
                                <input type="text" id="reference-number" placeholder="Txn ID or card ref"
                                       class="w-full border border-gray-300 rounded-lg px-2 sm:px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <button id="process-sale" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 sm:py-3 rounded-lg disabled:opacity-50 text-sm sm:text-base" disabled>
                                Process Sale
                            </button>
                            <button id="clear-cart" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 rounded-lg text-sm sm:text-base">
                                Clear Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Info -->
            <div class="mt-4 sm:mt-6 bg-white rounded-xl shadow-sm border p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Session Information</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div class="bg-blue-50 p-3 sm:p-4 rounded-lg">
                        <div class="text-lg sm:text-2xl font-bold text-blue-600" id="session-sales">₹{{ number_format($currentSession->total_sales, 2) }}</div>
                        <div class="text-xs sm:text-sm text-gray-600">Total Sales</div>
                    </div>
                    <div class="bg-green-50 p-3 sm:p-4 rounded-lg">
                        <div class="text-lg sm:text-2xl font-bold text-green-600" id="session-transactions">{{ $currentSession->total_transactions }}</div>
                        <div class="text-xs sm:text-sm text-gray-600">Transactions</div>
                    </div>
                    <div class="bg-purple-50 p-3 sm:p-4 rounded-lg">
                        <div class="text-lg sm:text-2xl font-bold text-purple-600">₹{{ number_format($currentSession->opening_cash, 2) }}</div>
                        <div class="text-xs sm:text-sm text-gray-600">Opening Cash</div>
                    </div>
                    <div class="bg-yellow-50 p-3 sm:p-4 rounded-lg">
                        <div class="text-lg sm:text-2xl font-bold text-yellow-600">{{ $currentSession->started_at->format('H:i') }}</div>
                        <div class="text-xs sm:text-sm text-gray-600">Session Started</div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Active Session -->
            <div class="text-center py-12">
                <div class="bg-white rounded-xl shadow-sm border p-8 max-w-md mx-auto">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-cash-register text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Active POS Session</h3>
                    <p class="text-gray-600 mb-6">Start a new POS session to begin processing sales</p>
                    <a href="{{ route('pos.start-session') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg">
                        Start POS Session
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@if($currentSession)
<script>
let cart = [];
let products = [];
let categories = [];
let activeCategory = '';

// Load products on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('POS Terminal loaded');
    console.log('Current session:', @json($currentSession));
    loadProducts();
    updateCartDisplay();
    
    // Event listeners
    document.getElementById('product-search').addEventListener('input', filterProducts);
    document.getElementById('category-filter').addEventListener('change', filterProducts);
    document.getElementById('discount-amount').addEventListener('input', updateTotals);
    document.getElementById('process-sale').addEventListener('click', processSale);
    document.getElementById('clear-cart').addEventListener('click', clearCart);
    document.getElementById('payment-method').addEventListener('change', onPaymentMethodChange);
    document.getElementById('amount-received').addEventListener('input', updateChangeToReturn);
});

// Load products from API
async function loadProducts() {
    try {
        const response = await fetch('/api/pos/products', {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        console.log('Products API response:', data);
        if (data.success) {
            products = data.data;
            console.log('Products loaded:', products.length);
            categories = [...new Set(products.map(p => p.category))].filter(Boolean).sort();
            renderCategoryTabs();
            displayProducts(products);
            populateCategories();
        } else {
            console.error('Failed to load products:', data);
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Display products in grid
function displayProducts(productsToShow) {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = '';
    
    productsToShow.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow';
        productCard.dataset.productId = product.id;
        
        const weightUnit = product.weight_unit || 'kg';
        const stockLabel = weightUnit === 'pcs' ? parseFloat(product.current_stock).toFixed(0) + ' pcs' : parseFloat(product.current_stock).toFixed(2) + ' ' + weightUnit;
        
        productCard.innerHTML = `
            <div class="text-sm font-medium text-gray-900 mb-1">${product.name}</div>
            <div class="text-xs text-gray-500 mb-2">${product.code}</div>
            <div class="text-lg font-bold text-green-600">₹${parseFloat(product.city_price || product.selling_price).toFixed(2)}</div>
            <div class="text-xs text-gray-400 mt-1">Stock: ${stockLabel}</div>
            <div class="text-xs text-blue-500 mb-2">${product.category || ''}</div>
            
            ${(!product.is_available_in_city || parseFloat(product.current_stock) <= 0) ? 
                '<div class="text-xs text-red-500 mt-1">Out of stock</div>' : 
                `
                <div class="mt-2 space-y-2 border-t pt-2">
                    <div class="flex items-center space-x-2">
                        <label class="text-xs text-gray-600">Bill by:</label>
                        <select class="product-billing-method flex-1 text-xs border border-gray-300 rounded px-2 py-1" 
                                data-product-id="${product.id}">
                            <option value="weight">Weight</option>
                            <option value="count">Count</option>
                        </select>
                    </div>
                    <div class="product-weight-inputs">
                        <div class="flex items-center space-x-1">
                            <input type="number" class="product-weight-value w-full text-xs border border-gray-300 rounded px-2 py-1" 
                                   step="0.01" min="0" placeholder="0.00" data-product-id="${product.id}">
                            <select class="product-weight-unit text-xs border border-gray-300 rounded px-1 py-1" 
                                    data-product-id="${product.id}">
                                <option value="kg">kg</option>
                                <option value="gm">gm</option>
                            </select>
                        </div>
                    </div>
                    <div class="product-count-inputs hidden">
                        <input type="number" class="product-count-value w-full text-xs border border-gray-300 rounded px-2 py-1" 
                               step="1" min="1" value="1" placeholder="1" data-product-id="${product.id}">
                    </div>
                    <button onclick="addProductToCart(${product.id})" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-1.5 px-2 rounded">
                        Add to Cart
                    </button>
                </div>
                `
            }
        `;
        
        if (!product.is_available_in_city || parseFloat(product.current_stock) <= 0) {
            productCard.className += ' opacity-50 cursor-not-allowed';
        }
        
        grid.appendChild(productCard);
    });
    
    // Initialize billing method handlers
    document.querySelectorAll('.product-billing-method').forEach(select => {
        select.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const billingMethod = this.value;
            const productCard = this.closest('[data-product-id]');
            
            const weightInputs = productCard.querySelector('.product-weight-inputs');
            const countInputs = productCard.querySelector('.product-count-inputs');
            
            if (billingMethod === 'weight') {
                weightInputs.classList.remove('hidden');
                countInputs.classList.add('hidden');
            } else {
                weightInputs.classList.add('hidden');
                countInputs.classList.remove('hidden');
            }
        });
    });
}

// Add product to cart with inline selection
function addProductToCart(productId) {
    const productCard = document.querySelector(`[data-product-id="${productId}"]`);
    if (!productCard) return;
    
    const product = products.find(p => p.id == productId);
    if (!product || !product.is_available_in_city || parseFloat(product.current_stock) <= 0) {
        alert('Product is out of stock');
        return;
    }
    
    const billingMethod = productCard.querySelector('.product-billing-method').value;
    const price = parseFloat(product.city_price || product.selling_price);
    const weightUnit = product.weight_unit || 'kg';
    
    const cartItem = {
        product_id: product.id,
        name: product.name,
        price: price,
        quantity: 1,
        weight_unit: weightUnit,
        max_stock: parseFloat(product.current_stock || 0),
        billing_method: billingMethod
    };
    
    if (billingMethod === 'weight') {
        const weightValue = parseFloat(productCard.querySelector('.product-weight-value').value) || 0;
        const weightUnitSelected = productCard.querySelector('.product-weight-unit').value;
        
        if (weightValue <= 0) {
            alert('Please enter a valid weight');
            return;
        }
        
        // Convert to kg for storage
        let weightInKg = weightValue;
        if (weightUnitSelected === 'gm') {
            weightInKg = weightValue / 1000;
        }
        
        cartItem.actual_weight = weightInKg;
        cartItem.billed_weight = weightInKg;
        cartItem.weight_unit_display = weightUnitSelected;
        cartItem.weight_value_display = weightValue;
        cartItem.total = weightInKg * price;
    } else {
        const countValue = parseInt(productCard.querySelector('.product-count-value').value) || 1;
        
        if (countValue <= 0) {
            alert('Please enter a valid count');
            return;
        }
        
        cartItem.actual_count = countValue;
        cartItem.billed_count = countValue;
        cartItem.total = countValue * price;
    }
    
    // Check if item already exists with same billing method
    const existingItem = cart.find(item => item.product_id === product.id && 
                                          item.billing_method === billingMethod);
    
    if (existingItem) {
        existingItem.quantity += 1;
        if (billingMethod === 'weight') {
            existingItem.actual_weight = cartItem.actual_weight;
            existingItem.billed_weight = cartItem.billed_weight;
            existingItem.weight_unit_display = cartItem.weight_unit_display;
            existingItem.weight_value_display = cartItem.weight_value_display;
            existingItem.total = cartItem.billed_weight * price;
        } else {
            existingItem.actual_count = cartItem.actual_count;
            existingItem.billed_count = cartItem.billed_count;
            existingItem.total = cartItem.billed_count * price;
        }
    } else {
        cart.push(cartItem);
    }
    
    // Reset inputs
    productCard.querySelector('.product-weight-value').value = '';
    productCard.querySelector('.product-count-value').value = '1';
    
    updateCartDisplay();
    updateTotals();
}

// Update cart display
function updateCartDisplay() {
    const cartContainer = document.getElementById('cart-items');
    cartContainer.innerHTML = '';
    
    if (cart.length === 0) {
        cartContainer.innerHTML = '<div class="text-center text-gray-500 py-4">Cart is empty</div>';
        document.getElementById('process-sale').disabled = true;
        return;
    }
    
    cart.forEach((item, index) => {
        const cartItem = document.createElement('div');
        cartItem.className = 'py-2 border-b border-gray-100';
        
        const billingMethod = item.billing_method || 'weight';
        const isCountBased = billingMethod === 'count';
        
        let inputFields = '';
        if (isCountBased) {
            inputFields = `
                <div class="mb-2">
                    <label class="block text-xs text-gray-600 mb-1">Count (pcs)</label>
                    <input type="number" value="${item.billed_count || item.actual_count || 1}" step="1" min="1" 
                           onchange="updateCartItemValue(${index}, 'count', this.value)"
                           class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                </div>
            `;
        } else {
            const weightValue = item.weight_value_display || (item.billed_weight ? (item.weight_unit_display === 'gm' ? (item.billed_weight * 1000).toFixed(0) : item.billed_weight.toFixed(2)) : '');
            inputFields = `
                <div class="mb-2">
                    <label class="block text-xs text-gray-600 mb-1">Weight</label>
                    <div class="flex items-center space-x-1 mb-1">
                        <input type="number" value="${weightValue}" 
                               step="${item.weight_unit_display === 'gm' ? '1' : '0.01'}" min="0" 
                               onchange="updateCartItemValue(${index}, 'weight', this.value)"
                               class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm">
                        <select onchange="updateCartItemUnit(${index}, this.value)" 
                                class="text-xs border border-gray-300 rounded px-2 py-1">
                            <option value="kg" ${(item.weight_unit_display || 'kg') === 'kg' ? 'selected' : ''}>kg</option>
                            <option value="gm" ${item.weight_unit_display === 'gm' ? 'selected' : ''}>gm</option>
                        </select>
                    </div>
                    ${item.billed_weight ? `
                        <div class="text-xs text-gray-500">
                            <span class="font-medium">${item.billed_weight.toFixed(3)} kg</span> 
                            <span class="text-gray-400">(${(item.billed_weight * 1000).toFixed(0)} gm)</span>
                        </div>
                    ` : ''}
                </div>
            `;
        }
        
        cartItem.innerHTML = `
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                    <div class="font-medium text-sm">${item.name}</div>
                    <div class="text-xs text-gray-500">₹${item.price.toFixed(2)} per ${isCountBased ? 'pcs' : (item.weight_unit_display || 'kg')}</div>
                </div>
                <button onclick="removeFromCart(${index})" class="w-6 h-6 bg-red-200 text-red-600 rounded text-xs">×</button>
            </div>
            ${inputFields}
            <div class="mt-2 flex items-center justify-between">
                <span class="text-xs text-gray-500">Total: ₹${item.total ? item.total.toFixed(2) : '0.00'}</span>
                <div class="flex items-center space-x-2">
                    <button onclick="updateQuantity(${index}, -1)" class="w-6 h-6 bg-gray-200 rounded text-xs">-</button>
                    <span class="w-8 text-center text-sm">${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, 1)" class="w-6 h-6 bg-gray-200 rounded text-xs">+</button>
                </div>
            </div>
        `;
        
        cartContainer.appendChild(cartItem);
    });
    
    document.getElementById('process-sale').disabled = false;
}

// Update cart item value (weight or count)
function updateCartItemValue(index, type, value) {
    const item = cart[index];
    
    if (type === 'weight') {
        const weightValue = parseFloat(value) || 0;
        const unit = item.weight_unit_display || 'kg';
        
        if (weightValue <= 0) {
            alert('Please enter a valid weight');
            return;
        }
        
        // Convert to kg for storage
        let weightInKg = weightValue;
        if (unit === 'gm') {
            weightInKg = weightValue / 1000;
        }
        
        item.actual_weight = weightInKg;
        item.billed_weight = weightInKg;
        item.weight_value_display = weightValue;
        item.weight_unit_display = unit;
        item.total = weightInKg * item.price;
    } else if (type === 'count') {
        const count = parseInt(value) || 1;
        
        if (count <= 0) {
            alert('Please enter a valid count');
            return;
        }
        
        item.actual_count = count;
        item.billed_count = count;
        item.total = count * item.price;
    }
    
    updateCartDisplay();
    updateTotals();
}

// Update cart item unit (kg/gm)
function updateCartItemUnit(index, unit) {
    const item = cart[index];
    const oldUnit = item.weight_unit_display || 'kg';
    item.weight_unit_display = unit;
    
    // Convert display value based on unit change
    if (item.billed_weight) {
        if (oldUnit === 'kg' && unit === 'gm') {
            item.weight_value_display = item.billed_weight * 1000;
        } else if (oldUnit === 'gm' && unit === 'kg') {
            item.weight_value_display = item.billed_weight;
        } else {
            if (unit === 'gm') {
                item.weight_value_display = item.billed_weight * 1000;
            } else {
                item.weight_value_display = item.billed_weight;
            }
        }
    }
    
    updateCartDisplay();
}

// Update item quantity
function updateQuantity(index, change) {
    const item = cart[index];
    const newQuantity = item.quantity + change;
    const billingMethod = item.billing_method || 'weight';
    
    if (newQuantity <= 0) {
        cart.splice(index, 1);
    } else {
        item.quantity = newQuantity;
        // Recalculate total based on billing method
        if (billingMethod === 'count') {
            if (item.billed_count) {
                item.total = item.billed_count * item.price;
            } else {
                item.billed_count = newQuantity;
                item.total = newQuantity * item.price;
            }
        } else {
            if (item.billed_weight) {
                item.total = item.billed_weight * item.price;
            } else {
                item.total = newQuantity * item.price;
            }
        }
    }
    updateCartDisplay();
    updateTotals();
}

// Remove item from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
    updateTotals();
}

// Update totals
function updateTotals() {
    // Calculate subtotal based on billing method
    const subtotal = cart.reduce((sum, item) => {
        const billingMethod = item.billing_method || 'weight';
        let value = 0;
        
        if (billingMethod === 'count') {
            value = item.billed_count || item.actual_count || item.quantity || 0;
        } else {
            value = item.billed_weight || item.actual_weight || item.quantity || 0;
        }
        
        return sum + (value * item.price);
    }, 0);
    
    const discount = parseFloat(document.getElementById('discount-amount').value) || 0;
    const taxAmount = 0; // No GST
    const total = subtotal - discount + taxAmount;
    
    document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
    document.getElementById('tax-amount').textContent = `₹${taxAmount.toFixed(2)}`;
    document.getElementById('total-amount').textContent = `₹${total.toFixed(2)}`;
    updateChangeToReturn();
}

// Clear cart
function clearCart() {
    cart = [];
    updateCartDisplay();
    updateTotals();
    document.getElementById('discount-amount').value = 0;
    document.getElementById('amount-received').value = 0;
    document.getElementById('reference-number').value = '';
}

// Filter products
function filterProducts() {
    const search = document.getElementById('product-search').value.toLowerCase();
    const category = activeCategory || document.getElementById('category-filter').value;
    
    let filtered = products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(search) || 
                            product.code.toLowerCase().includes(search);
        const matchesCategory = !category || product.category === category;
        return matchesSearch && matchesCategory;
    });
    
    displayProducts(filtered);
}

// Populate category filter
function populateCategories() {
    const categoryFilter = document.getElementById('category-filter');
    const categories = [...new Set(products.map(p => p.category))];
    
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categoryFilter.appendChild(option);
    });
}

// Render category tabs (modern POS switch)
function renderCategoryTabs() {
    const container = document.getElementById('category-tabs');
    container.innerHTML = '';
    const allBtn = document.createElement('button');
    allBtn.className = 'px-3 py-1 rounded-full border text-sm ' + (activeCategory === '' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300');
    allBtn.textContent = 'All';
    allBtn.onclick = () => { activeCategory = ''; filterProducts(); highlightActiveTab(''); };
    container.appendChild(allBtn);
    categories.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'px-3 py-1 rounded-full border text-sm ' + (activeCategory === cat ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300');
        btn.textContent = cat;
        btn.onclick = () => { activeCategory = cat; filterProducts(); highlightActiveTab(cat); };
        container.appendChild(btn);
    });
}

function highlightActiveTab(cat) {
    const container = document.getElementById('category-tabs');
    Array.from(container.children).forEach(btn => {
        const isActive = (btn.textContent === (cat || 'All'));
        btn.className = 'px-3 py-1 rounded-full border text-sm ' + (isActive ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300');
    });
}

// Payment method change handler
function onPaymentMethodChange() {
    const method = document.getElementById('payment-method').value;
    const amountWrapper = document.getElementById('amount-received-wrapper');
    const referenceWrapper = document.getElementById('reference-number-wrapper');

    if (method === 'cash') {
        amountWrapper.classList.remove('hidden');
        referenceWrapper.classList.add('hidden');
    } else if (method === 'card' || method === 'upi') {
        amountWrapper.classList.remove('hidden');
        referenceWrapper.classList.remove('hidden');
    } else if (method === 'credit') {
        amountWrapper.classList.add('hidden');
        referenceWrapper.classList.add('hidden');
        document.getElementById('amount-received').value = 0;
        document.getElementById('reference-number').value = '';
    }
    updateChangeToReturn();
}

// Update change to return for cash/card
function updateChangeToReturn() {
    const totalText = document.getElementById('total-amount').textContent.replace('₹', '').trim();
    const total = parseFloat(totalText) || 0;
    const amountReceived = parseFloat(document.getElementById('amount-received').value) || 0;
    const change = Math.max(amountReceived - total, 0);
    document.getElementById('change-amount').textContent = `₹${change.toFixed(2)}`;
}

// Process sale
async function processSale() {
    if (cart.length === 0) return;
    
    const customerId = document.getElementById('customer-select').value || null;
    const paymentMethod = document.getElementById('payment-method').value;
    const discountAmount = parseFloat(document.getElementById('discount-amount').value) || 0;
    const amountReceived = paymentMethod === 'credit' ? 0 : (parseFloat(document.getElementById('amount-received').value) || 0);
    const referenceNumber = document.getElementById('reference-number').value || null;
    
    const saleData = {
        customer_id: customerId,
        items: cart,
        payment_method: paymentMethod,
        discount_amount: discountAmount,
        tax_amount: parseFloat(document.getElementById('tax-amount').textContent.replace('₹', '')),
        amount_received: amountReceived,
        reference_number: referenceNumber
    };
    
    try {
        const response = await fetch('/api/pos/process-sale', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(saleData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Sale processed successfully!');
            clearCart();
            // Update session stats
            updateSessionStats(data.data.session);
            if (data.data.invoice_url) {
                window.open(data.data.invoice_url, '_blank');
            }
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error processing sale:', error);
        alert('Error processing sale');
    }
}

// Update session statistics
function updateSessionStats(session) {
    document.getElementById('session-sales').textContent = `₹${parseFloat(session.total_sales).toFixed(2)}`;
    document.getElementById('session-transactions').textContent = session.total_transactions;
}

// Subscribe to branch private channel for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    const branchId = {{ (int) $branch->id }};
    if (window.Echo) {
        window.Echo.private(`branch.${branchId}`)
            .listen('.sale.processed', (e) => {
                if (e.session) {
                    updateSessionStats(e.session);
                }
            })
            .listen('.stock.updated', async (e) => {
                try {
                    await loadProducts();
                    filterProducts();
                } catch (err) {
                    console.error('Failed to refresh products after stock update', err);
                }
            });
    }
});

</script>
@endif
@endsection