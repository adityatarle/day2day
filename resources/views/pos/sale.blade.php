@extends('layouts.cashier')

@section('title', 'New Sale - POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">New Sale</h1>
                    <span class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                        {{ $branch->name }}
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    @if($session)
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-gray-700">Session Active</span>
                            <span class="text-sm text-gray-500">Terminal: {{ $session->terminal_id }}</span>
                        </div>
                        <a href="{{ route('pos.close-session') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">
                            Close Session
                        </a>
                    @else
                        <a href="{{ route('pos.start-session') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                            Start Session
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($session)
            <!-- POS Sale Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Panel - Product Selection -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Products</h2>
                            <div class="text-sm text-gray-500">
                                Available: <span id="available-count">{{ count($products) }}</span> items
                            </div>
                        </div>
                        
                        <!-- Search and Filters -->
                        <div class="mb-4 flex space-x-4">
                            <input type="text" id="product-search" placeholder="Search products by name or SKU..." 
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                            </select>
                            <button id="barcode-scan" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-barcode mr-2"></i>Scan
                            </button>
                        </div>

                        <!-- Category Tabs -->
                        <div id="category-tabs" class="flex flex-wrap gap-2 mb-4"></div>

                        <!-- Quick Add by SKU -->
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="flex space-x-2">
                                <input type="text" id="sku-input" placeholder="Enter SKU or scan barcode" 
                                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <input type="number" id="quantity-input" placeholder="Qty" min="1" value="1" 
                                       class="w-20 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button id="quick-add" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-plus mr-2"></i>Add
                                </button>
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <div id="products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-96 overflow-y-auto">
                            @foreach($products as $product)
                                <div class="product-card border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow" 
                                     data-product-id="{{ $product['id'] }}"
                                     data-product-name="{{ $product['name'] }}"
                                     data-product-price="{{ $product['selling_price'] }}"
                                     data-product-stock="{{ $product['current_stock'] }}"
                                     data-weight-unit="{{ $product['weight_unit'] }}">
                                    <div class="text-sm font-medium text-gray-900 mb-1 truncate" title="{{ $product['name'] }}">{{ $product['name'] }}</div>
                                    <div class="text-xs text-gray-500 mb-2">{{ $product['code'] }}</div>
                                    <div class="text-lg font-bold text-green-600">₹{{ number_format($product['selling_price'], 2) }}</div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Stock: 
                                        @if($product['weight_unit'] === 'pcs')
                                            {{ number_format($product['current_stock_kg'], 2) }} pcs
                                        @else
                                            {{ number_format($product['current_stock_kg'], 2) }} kg
                                        @endif
                                    </div>
                                    <div class="text-xs text-blue-500 mb-2">{{ $product['category'] }}</div>
                                    
                                    <!-- Quick Add Section -->
                                    <div class="mt-2 space-y-2 border-t pt-2">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <label class="text-xs text-gray-600">Bill by:</label>
                                            <select class="product-billing-method flex-1 text-xs border border-gray-300 rounded px-2 py-1" 
                                                    data-product-id="{{ $product['id'] }}">
                                                <option value="weight">Weight</option>
                                                <option value="count">Count</option>
                                            </select>
                                        </div>
                                        <div class="product-weight-inputs hidden">
                                            <div class="flex items-center space-x-1 mb-1">
                                                <input type="number" class="product-weight-value w-full text-xs border border-gray-300 rounded px-2 py-1" 
                                                       step="0.01" min="0" placeholder="0.00" data-product-id="{{ $product['id'] }}">
                                                <select class="product-weight-unit text-xs border border-gray-300 rounded px-1 py-1" 
                                                        data-product-id="{{ $product['id'] }}">
                                                    <option value="kg">kg</option>
                                                    <option value="gm">gm</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="product-count-inputs hidden">
                                            <input type="number" class="product-count-value w-full text-xs border border-gray-300 rounded px-2 py-1" 
                                                   step="1" min="1" value="1" placeholder="1" data-product-id="{{ $product['id'] }}">
                                        </div>
                                        <button onclick="addProductToCart({{ $product['id'] }})" 
                                                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-1.5 px-2 rounded">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Panel - Cart and Checkout -->
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Cart</h2>
                            <button id="clear-cart" class="text-red-600 hover:text-red-700 text-sm">
                                <i class="fas fa-trash mr-1"></i>Clear All
                            </button>
                        </div>
                        
                        <!-- Customer Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                            <div class="flex space-x-2">
                                <select id="customer-select" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Walk-in Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('customers.create') }}?redirect_to={{ urlencode(route('pos.sale')) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg inline-flex items-center justify-center" 
                                   title="Add New Customer">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Cart Items -->
                        <div id="cart-items" class="mb-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
                            <div id="empty-cart" class="text-center text-gray-500 py-8">
                                <i class="fas fa-shopping-cart text-3xl mb-2 opacity-50"></i>
                                <div>Cart is empty</div>
                                <div class="text-sm">Add products to start a sale</div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="border-t pt-4 mb-4">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Items:</span>
                                    <span id="total-items">0</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Subtotal:</span>
                                    <span id="subtotal">₹0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Discount:</span>
                                    <div class="flex items-center space-x-2">
                                        <input type="number" id="discount-amount" value="0" min="0" step="0.01"
                                               class="w-20 text-right border border-gray-300 rounded px-2 py-1 text-sm">
                                        <select id="discount-type" class="text-sm border border-gray-300 rounded px-2 py-1">
                                            <option value="amount">₹</option>
                                            <option value="percent">%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-between text-lg font-bold border-t pt-2">
                                    <span>Total:</span>
                                    <span id="total-amount">₹0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button class="payment-method-btn active" data-method="cash">
                                    <i class="fas fa-money-bill-wave mr-2"></i>Cash
                                </button>
                                <button class="payment-method-btn" data-method="card">
                                    <i class="fas fa-credit-card mr-2"></i>Card
                                </button>
                                <button class="payment-method-btn" data-method="upi">
                                    <i class="fab fa-google-pay mr-2"></i>UPI
                                </button>
                                <button class="payment-method-btn" data-method="credit">
                                    <i class="fas fa-clock mr-2"></i>Credit
                                </button>
                            </div>
                            <input type="hidden" id="payment-method" value="cash">
                        </div>

                        <!-- Payment Details -->
                        <div id="payment-details" class="mb-4 space-y-3">
                            <div id="amount-received-wrapper">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                                <input type="number" id="amount-received" value="0" min="0" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="mt-1 text-sm">
                                    <span class="text-gray-600">Change: </span>
                                    <span id="change-amount" class="font-medium text-green-600">₹0.00</span>
                                </div>
                            </div>
                            
                            <!-- Cash Denominations -->
                            <div id="cash-denominations-wrapper" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cash Denominations</label>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹2000</label>
                                        <input type="number" id="denom-2000" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹500</label>
                                        <input type="number" id="denom-500" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹200</label>
                                        <input type="number" id="denom-200" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹100</label>
                                        <input type="number" id="denom-100" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹50</label>
                                        <input type="number" id="denom-50" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹20</label>
                                        <input type="number" id="denom-20" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹10</label>
                                        <input type="number" id="denom-10" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹5</label>
                                        <input type="number" id="denom-5" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹2</label>
                                        <input type="number" id="denom-2" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">₹1</label>
                                        <input type="number" id="denom-1" value="0" min="0" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">Coins</label>
                                        <input type="number" id="denom-coins" value="0" min="0" step="0.01" class="w-full border border-gray-300 rounded px-2 py-1" onchange="calculateCashTotal()">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 mb-1">Total</label>
                                        <input type="text" id="cash-total" value="₹0.00" readonly class="w-full border border-gray-300 rounded px-2 py-1 bg-gray-50 font-semibold">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- UPI Details -->
                            <div id="upi-details-wrapper" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">UPI ID</label>
                                <input type="text" id="upi-id" placeholder="merchant@paytm or merchant@phonepe"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" id="generate-upi-qr" class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm">
                                    <i class="fas fa-qrcode mr-2"></i>Generate QR Code
                                </button>
                                <div id="upi-qr-display" class="mt-3 hidden text-center">
                                    <div id="qr-code-container" class="inline-block p-2 bg-white border rounded"></div>
                                </div>
                            </div>
                            
                            <div id="reference-number-wrapper" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                                <input type="text" id="reference-number" placeholder="Transaction ID / Reference"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <button id="process-sale" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <i class="fas fa-cash-register mr-2"></i>Process Sale
                            </button>
                            <div class="grid grid-cols-2 gap-2">
                                <button id="hold-sale" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 rounded-lg">
                                    <i class="fas fa-pause mr-2"></i>Hold
                                </button>
                                <button id="print-receipt" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg" disabled>
                                    <i class="fas fa-print mr-2"></i>Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Info -->
            <div class="mt-6 bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600" id="session-sales">₹{{ number_format($session->total_sales ?? 0, 2) }}</div>
                        <div class="text-sm text-gray-600">Total Sales</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600" id="session-transactions">{{ $session->total_transactions ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Transactions</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">₹{{ number_format($session->opening_cash, 2) }}</div>
                        <div class="text-sm text-gray-600">Opening Cash</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ $session->started_at->format('H:i') }}</div>
                        <div class="text-sm text-gray-600">Session Started</div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Active Session -->
            <div class="text-center py-12">
                <div class="bg-white rounded-xl shadow-sm border p-8 max-w-md mx-auto">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Active POS Session</h3>
                    <p class="text-gray-600 mb-6">You need to start a POS session before making sales</p>
                    <a href="{{ route('pos.start-session') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg">
                        Start POS Session
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@if($session)
<script>
let cart = [];
let products = @json($products);
let categories = [...new Set(products.map(p => p.category))].filter(Boolean).sort();
let activeCategory = '';

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    populateCategories();
    updateCartDisplay();
});

// Initialize all event listeners
function initializeEventListeners() {
    // Product search and filter
    document.getElementById('product-search').addEventListener('input', filterProducts);
    document.getElementById('category-filter').addEventListener('change', filterProducts);
    renderCategoryTabs();
    
    // Quick add functionality
    document.getElementById('sku-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            quickAddProduct();
        }
    });
    document.getElementById('quick-add').addEventListener('click', quickAddProduct);
    
    // Product billing method change handler
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-billing-method')) {
            const productId = e.target.dataset.productId;
            const billingMethod = e.target.value;
            const productCard = e.target.closest('.product-card');
            
            const weightInputs = productCard.querySelector('.product-weight-inputs');
            const countInputs = productCard.querySelector('.product-count-inputs');
            
            if (billingMethod === 'weight') {
                weightInputs.classList.remove('hidden');
                countInputs.classList.add('hidden');
            } else {
                weightInputs.classList.add('hidden');
                countInputs.classList.remove('hidden');
            }
        }
    });
    
    // Initialize product cards - show weight inputs by default
    document.querySelectorAll('.product-card').forEach(card => {
        const billingMethod = card.querySelector('.product-billing-method').value;
        const weightInputs = card.querySelector('.product-weight-inputs');
        const countInputs = card.querySelector('.product-count-inputs');
        
        if (billingMethod === 'weight') {
            weightInputs.classList.remove('hidden');
            countInputs.classList.add('hidden');
        } else {
            weightInputs.classList.add('hidden');
            countInputs.classList.remove('hidden');
        }
    });
    
    // Cart and payment
    document.getElementById('discount-amount').addEventListener('input', updateTotals);
    document.getElementById('discount-type').addEventListener('change', updateTotals);
    document.getElementById('clear-cart').addEventListener('click', clearCart);
    document.getElementById('process-sale').addEventListener('click', processSale);
    document.getElementById('amount-received').addEventListener('input', updateChangeToReturn);
    
    // Payment method buttons
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            selectPaymentMethod(this.dataset.method);
        });
    });
}

// Quick add product by SKU
function quickAddProduct() {
    const sku = document.getElementById('sku-input').value.trim();
    const quantity = parseInt(document.getElementById('quantity-input').value) || 1;
    
    if (!sku) return;
    
    const product = products.find(p => p.code.toLowerCase() === sku.toLowerCase());
    if (product) {
        for (let i = 0; i < quantity; i++) {
            addToCart(product);
        }
        document.getElementById('sku-input').value = '';
        document.getElementById('quantity-input').value = '1';
    } else {
        alert('Product not found with SKU: ' + sku);
    }
}

// Add product to cart with inline selection
function addProductToCart(productId) {
    const productCard = document.querySelector(`[data-product-id="${productId}"]`);
    if (!productCard) return;
    
    const product = products.find(p => p.id == productId);
    if (!product || product.current_stock <= 0) {
        alert('Product is out of stock');
        return;
    }
    
    const billingMethod = productCard.querySelector('.product-billing-method').value;
    const price = parseFloat(product.selling_price);
    const weightUnit = product.weight_unit || 'kg';
    
    const cartItem = {
        product_id: product.id,
        name: product.name,
        price: price,
        quantity: 1,
        weight_unit: weightUnit,
        max_stock: product.current_stock,
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
            weightInKg = weightValue / 1000; // Convert grams to kg
        }
        
        cartItem.actual_weight = weightInKg;
        cartItem.billed_weight = weightInKg;
        cartItem.weight_unit_display = weightUnitSelected; // Store display unit
        cartItem.weight_value_display = weightValue; // Store display value
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
    const emptyCart = document.getElementById('empty-cart');
    
    if (cart.length === 0) {
        emptyCart.style.display = 'block';
        document.getElementById('process-sale').disabled = true;
        return;
    }
    
    emptyCart.style.display = 'none';
    
    let cartHTML = '';
    cart.forEach((item, index) => {
        const billingMethod = item.billing_method || 'weight';
        const isCountBased = billingMethod === 'count';
        
        cartHTML += `
            <div class="p-3 border-b border-gray-100">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <div class="font-medium text-sm">${item.name}</div>
                        <div class="text-xs text-gray-500">
                            ₹${item.price.toFixed(2)} per ${isCountBased ? 'pcs' : (item.weight_unit_display || 'kg')}
                        </div>
                    </div>
                    <button onclick="removeFromCart(${index})" class="w-6 h-6 bg-red-200 hover:bg-red-300 text-red-600 rounded text-xs font-bold">×</button>
                </div>
                ${isCountBased ? `
                    <!-- Count-based product -->
                    <div class="mb-2">
                        <label class="block text-xs text-gray-600 mb-1">Count (pcs)</label>
                        <input type="number" value="${item.billed_count || item.actual_count || 1}" step="1" min="1" 
                               onchange="updateCartItemValue(${index}, 'count', this.value)"
                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                    </div>
                ` : `
                    <!-- Weight-based product -->
                    <div class="mb-2">
                        <label class="block text-xs text-gray-600 mb-1">Weight</label>
                        <div class="flex items-center space-x-1 mb-1">
                            <input type="number" value="${item.weight_value_display || (item.billed_weight ? (item.weight_unit_display === 'gm' ? (item.billed_weight * 1000).toFixed(0) : item.billed_weight.toFixed(2)) : '')}" 
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
                `}
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-xs text-gray-500">Total: ₹${item.total ? item.total.toFixed(2) : '0.00'}</span>
                    <div class="flex items-center space-x-1">
                        <button onclick="updateQuantity(${index}, -1)" class="w-6 h-6 bg-gray-200 hover:bg-gray-300 rounded text-xs font-bold">-</button>
                        <span class="w-8 text-center text-xs font-medium">${item.quantity}</span>
                        <button onclick="updateQuantity(${index}, 1)" class="w-6 h-6 bg-gray-200 hover:bg-gray-300 rounded text-xs font-bold" ${item.quantity >= item.max_stock ? 'disabled' : ''}>+</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    cartContainer.innerHTML = cartHTML;
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
            // Convert from kg to gm
            item.weight_value_display = item.billed_weight * 1000;
        } else if (oldUnit === 'gm' && unit === 'kg') {
            // Convert from gm to kg
            item.weight_value_display = item.billed_weight;
        } else {
            // Keep current display value
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
    const billingMethod = item.billing_method || (item.weight_unit === 'pcs' ? 'count' : 'weight');
    
    if (newQuantity <= 0) {
        cart.splice(index, 1);
    } else if (newQuantity <= item.max_stock) {
        item.quantity = newQuantity;
        // Recalculate total based on billing method
        if (billingMethod === 'count') {
            // For count-based billing, use billed_count
            if (item.billed_count) {
                item.total = item.billed_count * item.price;
            } else {
                item.billed_count = newQuantity;
                item.total = newQuantity * item.price;
            }
        } else {
            // For weight-based billing, use billed_weight
            if (item.billed_weight) {
                item.total = item.billed_weight * item.price;
            } else {
                item.total = item.quantity * item.price;
            }
        }
    } else {
        alert('Cannot exceed available stock');
        return;
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
    // Calculate subtotal based on billing method (weight or count)
    const subtotal = cart.reduce((sum, item) => {
        const billingMethod = item.billing_method || (item.weight_unit === 'pcs' ? 'count' : 'weight');
        let value = 0;
        
        if (billingMethod === 'count') {
            // For count-based billing, use billed_count
            value = item.billed_count || item.actual_count || item.quantity || 0;
        } else {
            // For weight-based billing, use billed_weight
            value = item.billed_weight || item.actual_weight || item.quantity || 0;
        }
        
        return sum + (value * item.price);
    }, 0);
    
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    let discountAmount = 0;
    const discountValue = parseFloat(document.getElementById('discount-amount').value) || 0;
    const discountType = document.getElementById('discount-type').value;
    
    if (discountType === 'percent') {
        discountAmount = (subtotal * discountValue) / 100;
    } else {
        discountAmount = discountValue;
    }
    
    // No GST
    const total = Math.max(subtotal - discountAmount, 0);
    
    document.getElementById('total-items').textContent = totalItems;
    document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
    document.getElementById('total-amount').textContent = `₹${total.toFixed(2)}`;
    
    updateChangeToReturn();
}

// Clear cart
function clearCart() {
    if (cart.length > 0 && !confirm('Are you sure you want to clear the cart?')) {
        return;
    }
    
    cart = [];
    updateCartDisplay();
    updateTotals();
    document.getElementById('discount-amount').value = '0';
    document.getElementById('amount-received').value = '0';
    document.getElementById('reference-number').value = '';
}

// Filter products
function filterProducts() {
    const search = document.getElementById('product-search').value.toLowerCase();
    const category = activeCategory || document.getElementById('category-filter').value;
    
    document.querySelectorAll('.product-card').forEach(card => {
        const productName = card.dataset.productName.toLowerCase();
        const productCode = card.querySelector('.text-xs').textContent.toLowerCase();
        const productCategory = card.querySelector('.text-blue-500').textContent;
        
        const matchesSearch = productName.includes(search) || productCode.includes(search);
        const matchesCategory = !category || productCategory === category;
        
        card.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
    });
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

// Render category tabs for quick switching
function renderCategoryTabs() {
    const container = document.getElementById('category-tabs');
    if (!container) return;
    container.innerHTML = '';
    const allBtn = document.createElement('button');
    allBtn.className = 'px-3 py-1 rounded-full border text-sm bg-blue-600 text-white border-blue-600';
    allBtn.textContent = 'All';
    allBtn.onclick = () => { activeCategory = ''; filterProducts(); highlightActiveTab(''); };
    container.appendChild(allBtn);
    categories.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'px-3 py-1 rounded-full border text-sm bg-white text-gray-700 border-gray-300';
        btn.textContent = cat;
        btn.onclick = () => { activeCategory = cat; filterProducts(); highlightActiveTab(cat); };
        container.appendChild(btn);
    });
}

function highlightActiveTab(cat) {
    const container = document.getElementById('category-tabs');
    if (!container) return;
    Array.from(container.children).forEach(btn => {
        const isActive = (btn.textContent === (cat || 'All'));
        btn.className = 'px-3 py-1 rounded-full border text-sm ' + (isActive ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300');
    });
}

// Select payment method
function selectPaymentMethod(method) {
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.querySelector(`[data-method="${method}"]`).classList.add('active');
    document.getElementById('payment-method').value = method;
    
    const amountWrapper = document.getElementById('amount-received-wrapper');
    const referenceWrapper = document.getElementById('reference-number-wrapper');
    const cashDenomWrapper = document.getElementById('cash-denominations-wrapper');
    const upiDetailsWrapper = document.getElementById('upi-details-wrapper');
    
    // Hide all first
    cashDenomWrapper.classList.add('hidden');
    upiDetailsWrapper.classList.add('hidden');
    referenceWrapper.classList.add('hidden');
    
    if (method === 'cash') {
        amountWrapper.style.display = 'block';
        cashDenomWrapper.classList.remove('hidden');
    } else if (method === 'upi') {
        amountWrapper.style.display = 'block';
        upiDetailsWrapper.classList.remove('hidden');
        referenceWrapper.classList.remove('hidden');
    } else if (method === 'card') {
        amountWrapper.style.display = 'block';
        referenceWrapper.classList.remove('hidden');
    } else if (method === 'credit') {
        amountWrapper.style.display = 'none';
    }
    
    updateChangeToReturn();
}

// Calculate cash total from denominations
function calculateCashTotal() {
    const denominations = {
        2000: parseFloat(document.getElementById('denom-2000').value) || 0,
        500: parseFloat(document.getElementById('denom-500').value) || 0,
        200: parseFloat(document.getElementById('denom-200').value) || 0,
        100: parseFloat(document.getElementById('denom-100').value) || 0,
        50: parseFloat(document.getElementById('denom-50').value) || 0,
        20: parseFloat(document.getElementById('denom-20').value) || 0,
        10: parseFloat(document.getElementById('denom-10').value) || 0,
        5: parseFloat(document.getElementById('denom-5').value) || 0,
        2: parseFloat(document.getElementById('denom-2').value) || 0,
        1: parseFloat(document.getElementById('denom-1').value) || 0,
        coins: parseFloat(document.getElementById('denom-coins').value) || 0
    };
    
    const total = (denominations[2000] * 2000) +
                  (denominations[500] * 500) +
                  (denominations[200] * 200) +
                  (denominations[100] * 100) +
                  (denominations[50] * 50) +
                  (denominations[20] * 20) +
                  (denominations[10] * 10) +
                  (denominations[5] * 5) +
                  (denominations[2] * 2) +
                  (denominations[1] * 1) +
                  denominations.coins;
    
    document.getElementById('cash-total').value = `₹${total.toFixed(2)}`;
    document.getElementById('amount-received').value = total.toFixed(2);
    updateChangeToReturn();
}

// Generate UPI QR Code
document.getElementById('generate-upi-qr')?.addEventListener('click', async function() {
    const upiId = document.getElementById('upi-id').value.trim();
    const totalAmount = parseFloat(document.getElementById('total-amount').textContent.replace('₹', ''));
    
    if (!upiId) {
        alert('Please enter UPI ID');
        return;
    }
    
    if (totalAmount <= 0) {
        alert('Total amount must be greater than 0');
        return;
    }
    
    try {
        const response = await fetch('/api/pos/generate-upi-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                amount: totalAmount,
                upi_id: upiId,
                merchant_name: 'Day2Day',
                transaction_note: 'POS Payment'
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.data.qr_code_svg) {
            document.getElementById('qr-code-container').innerHTML = data.data.qr_code_svg;
            document.getElementById('upi-qr-display').classList.remove('hidden');
        } else {
            alert('Error generating QR code: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error generating QR code:', error);
        alert('Error generating QR code. Please try again.');
    }
});

// Update change to return
function updateChangeToReturn() {
    const totalText = document.getElementById('total-amount').textContent.replace('₹', '').trim();
    const total = parseFloat(totalText) || 0;
    const amountReceived = parseFloat(document.getElementById('amount-received').value) || 0;
    const change = Math.max(amountReceived - total, 0);
    
    document.getElementById('change-amount').textContent = `₹${change.toFixed(2)}`;
    
    // Auto-fill amount received when total changes
    if (document.getElementById('payment-method').value === 'cash' && amountReceived === 0) {
        document.getElementById('amount-received').value = total.toFixed(2);
    }
}

// Process sale
async function processSale() {
    if (cart.length === 0) {
        alert('Cart is empty');
        return;
    }
    
    // Prepare items with proper weight/count handling based on billing method
    const items = cart.map(item => {
        const billingMethod = item.billing_method || (item.weight_unit === 'pcs' ? 'count' : 'weight');
        const itemData = {
            product_id: item.product_id,
            price: item.price,
            quantity: item.quantity
        };
        
        if (billingMethod === 'count') {
            // For count-based billing
            itemData.actual_weight = item.actual_count || item.quantity || 1;
            itemData.billed_weight = item.billed_count || item.quantity || 1;
        } else {
            // For weight-based billing
            itemData.actual_weight = item.actual_weight || item.quantity || 1;
            itemData.billed_weight = item.billed_weight || item.quantity || 1;
        }
        
        return itemData;
    });
    
    const paymentMethod = document.getElementById('payment-method').value;
    const totalAmount = parseFloat(document.getElementById('total-amount').textContent.replace('₹', ''));
    const amountReceived = parseFloat(document.getElementById('amount-received').value) || 0;
    
    // Validation
    if (paymentMethod === 'cash' && amountReceived < totalAmount) {
        alert('Amount received is less than total amount');
        return;
    }
    
    if ((paymentMethod === 'card' || paymentMethod === 'upi') && !document.getElementById('reference-number').value.trim()) {
        alert('Reference number is required for ' + paymentMethod.toUpperCase() + ' payments');
        return;
    }
    
    // Use the items array prepared above (already handles both weight and count)
    const itemsWithWeight = items;
    
    // Get cash denominations if payment is cash
    let cashDenominations = null;
    if (paymentMethod === 'cash') {
        cashDenominations = {
            denomination_2000: parseFloat(document.getElementById('denom-2000').value) || 0,
            denomination_500: parseFloat(document.getElementById('denom-500').value) || 0,
            denomination_200: parseFloat(document.getElementById('denom-200').value) || 0,
            denomination_100: parseFloat(document.getElementById('denom-100').value) || 0,
            denomination_50: parseFloat(document.getElementById('denom-50').value) || 0,
            denomination_20: parseFloat(document.getElementById('denom-20').value) || 0,
            denomination_10: parseFloat(document.getElementById('denom-10').value) || 0,
            denomination_5: parseFloat(document.getElementById('denom-5').value) || 0,
            denomination_2: parseFloat(document.getElementById('denom-2').value) || 0,
            denomination_1: parseFloat(document.getElementById('denom-1').value) || 0,
            coins: parseFloat(document.getElementById('denom-coins').value) || 0
        };
    }
    
    const saleData = {
        customer_id: document.getElementById('customer-select').value || null,
        items: itemsWithWeight,
        payment_method: paymentMethod,
        discount_amount: calculateDiscountAmount(),
        amount_received: paymentMethod === 'credit' ? totalAmount : amountReceived,
        reference_number: document.getElementById('reference-number').value || null,
        cash_denominations: cashDenominations,
        upi_id: paymentMethod === 'upi' ? document.getElementById('upi-id').value.trim() : null
    };
    
    try {
        document.getElementById('process-sale').disabled = true;
        document.getElementById('process-sale').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        const response = await fetch('/api/pos/process-sale', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(saleData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Sale processed successfully!\nOrder: ' + data.data.order_number);
            clearCart();
            updateSessionStats(data.data.session);
            
            // Open invoice in new tab
            if (data.data.invoice_url) {
                window.open(data.data.invoice_url, '_blank');
            }
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error processing sale:', error);
        alert('Error processing sale. Please try again.');
    } finally {
        document.getElementById('process-sale').disabled = false;
        document.getElementById('process-sale').innerHTML = '<i class="fas fa-cash-register mr-2"></i>Process Sale';
    }
}

// Calculate discount amount
function calculateDiscountAmount() {
    const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
    const discountValue = parseFloat(document.getElementById('discount-amount').value) || 0;
    const discountType = document.getElementById('discount-type').value;
    
    if (discountType === 'percent') {
        return (subtotal * discountValue) / 100;
    } else {
        return discountValue;
    }
}

// Update session statistics
function updateSessionStats(session) {
    document.getElementById('session-sales').textContent = `₹${parseFloat(session.total_sales).toFixed(2)}`;
    document.getElementById('session-transactions').textContent = session.total_transactions;
}
</script>

<style>
.payment-method-btn {
    @apply border border-gray-300 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors;
}

.payment-method-btn.active {
    @apply bg-blue-600 text-white border-blue-600;
}

.product-card:hover {
    transform: translateY(-2px);
}
</style>

@endif
@endsection