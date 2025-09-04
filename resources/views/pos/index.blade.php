@extends('layouts.app')

@section('title', 'POS System')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">POS System</h1>
                    <span class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                        {{ $branch->name }} - {{ $branch->city->name ?? 'No City' }}
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    @if($currentSession)
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-gray-700">Session Active</span>
                            <span class="text-sm text-gray-500">Terminal: {{ $currentSession->terminal_id }}</span>
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
        @if($currentSession)
            <!-- POS Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Panel - Product Selection -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Products</h2>
                        
                        <!-- Search and Filters -->
                        <div class="mb-4 flex space-x-4">
                            <input type="text" id="product-search" placeholder="Search products..." 
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                            </select>
                        </div>

                        <!-- Product Grid -->
                        <div id="products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <!-- Products will be loaded here via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Right Panel - Cart and Checkout -->
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Cart</h2>
                        
                        <!-- Customer Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                            <select id="customer-select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Walk-in Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cart Items -->
                        <div id="cart-items" class="mb-4 max-h-64 overflow-y-auto">
                            <!-- Cart items will be populated here -->
                        </div>

                        <!-- Totals -->
                        <div class="border-t pt-4 mb-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal">₹0.00</span>
                            </div>
                            <div class="flex justify-between text-sm mb-2">
                                <span>Discount:</span>
                                <input type="number" id="discount-amount" value="0" min="0" step="0.01"
                                       class="w-20 text-right border border-gray-300 rounded px-2 py-1 text-sm">
                            </div>
                            <div class="flex justify-between text-sm mb-2">
                                <span>Tax (18%):</span>
                                <span id="tax-amount">₹0.00</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total:</span>
                                <span id="total-amount">₹0.00</span>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select id="payment-method" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="upi">UPI</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <button id="process-sale" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg disabled:opacity-50" disabled>
                                Process Sale
                            </button>
                            <button id="clear-cart" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 rounded-lg">
                                Clear Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Info -->
            <div class="mt-6 bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600" id="session-sales">₹{{ number_format($currentSession->total_sales, 2) }}</div>
                        <div class="text-sm text-gray-600">Total Sales</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600" id="session-transactions">{{ $currentSession->total_transactions }}</div>
                        <div class="text-sm text-gray-600">Transactions</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">₹{{ number_format($currentSession->opening_cash, 2) }}</div>
                        <div class="text-sm text-gray-600">Opening Cash</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ $currentSession->started_at->format('H:i') }}</div>
                        <div class="text-sm text-gray-600">Session Started</div>
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

// Load products on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    updateCartDisplay();
    
    // Event listeners
    document.getElementById('product-search').addEventListener('input', filterProducts);
    document.getElementById('category-filter').addEventListener('change', filterProducts);
    document.getElementById('discount-amount').addEventListener('input', updateTotals);
    document.getElementById('process-sale').addEventListener('click', processSale);
    document.getElementById('clear-cart').addEventListener('click', clearCart);
});

// Load products from API
async function loadProducts() {
    try {
        const response = await fetch('/api/pos/products', {
            headers: {
                'Authorization': 'Bearer ' + '{{ auth()->user()->createToken("pos")->plainTextToken }}',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            products = data.data;
            displayProducts(products);
            populateCategories();
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
        productCard.className = 'border border-gray-200 rounded-lg p-3 hover:shadow-md cursor-pointer transition-shadow';
        productCard.onclick = () => addToCart(product);
        
        productCard.innerHTML = `
            <div class="text-sm font-medium text-gray-900 mb-1">${product.name}</div>
            <div class="text-xs text-gray-500 mb-2">${product.code}</div>
            <div class="text-lg font-bold text-green-600">₹${parseFloat(product.city_price || product.selling_price).toFixed(2)}</div>
            ${!product.is_available_in_city ? '<div class="text-xs text-red-500 mt-1">Not available in this city</div>' : ''}
        `;
        
        if (!product.is_available_in_city) {
            productCard.className += ' opacity-50 cursor-not-allowed';
            productCard.onclick = null;
        }
        
        grid.appendChild(productCard);
    });
}

// Add product to cart
function addToCart(product) {
    if (!product.is_available_in_city) return;
    
    const existingItem = cart.find(item => item.product_id === product.id);
    const price = parseFloat(product.city_price || product.selling_price);
    
    if (existingItem) {
        existingItem.quantity += 1;
        existingItem.total = existingItem.quantity * existingItem.price;
    } else {
        cart.push({
            product_id: product.id,
            name: product.name,
            price: price,
            quantity: 1,
            total: price
        });
    }
    
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
        cartItem.className = 'flex justify-between items-center py-2 border-b border-gray-100';
        
        cartItem.innerHTML = `
            <div class="flex-1">
                <div class="font-medium text-sm">${item.name}</div>
                <div class="text-xs text-gray-500">₹${item.price.toFixed(2)} each</div>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="updateQuantity(${index}, -1)" class="w-6 h-6 bg-gray-200 rounded text-xs">-</button>
                <span class="w-8 text-center text-sm">${item.quantity}</span>
                <button onclick="updateQuantity(${index}, 1)" class="w-6 h-6 bg-gray-200 rounded text-xs">+</button>
                <button onclick="removeFromCart(${index})" class="w-6 h-6 bg-red-200 text-red-600 rounded text-xs">×</button>
            </div>
        `;
        
        cartContainer.appendChild(cartItem);
    });
    
    document.getElementById('process-sale').disabled = false;
}

// Update item quantity
function updateQuantity(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    } else {
        cart[index].total = cart[index].quantity * cart[index].price;
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
    const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
    const discount = parseFloat(document.getElementById('discount-amount').value) || 0;
    const taxAmount = (subtotal - discount) * 0.18;
    const total = subtotal - discount + taxAmount;
    
    document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
    document.getElementById('tax-amount').textContent = `₹${taxAmount.toFixed(2)}`;
    document.getElementById('total-amount').textContent = `₹${total.toFixed(2)}`;
}

// Clear cart
function clearCart() {
    cart = [];
    updateCartDisplay();
    updateTotals();
    document.getElementById('discount-amount').value = 0;
}

// Filter products
function filterProducts() {
    const search = document.getElementById('product-search').value.toLowerCase();
    const category = document.getElementById('category-filter').value;
    
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

// Process sale
async function processSale() {
    if (cart.length === 0) return;
    
    const customerId = document.getElementById('customer-select').value || null;
    const paymentMethod = document.getElementById('payment-method').value;
    const discountAmount = parseFloat(document.getElementById('discount-amount').value) || 0;
    
    const saleData = {
        customer_id: customerId,
        items: cart,
        payment_method: paymentMethod,
        discount_amount: discountAmount,
        tax_amount: parseFloat(document.getElementById('tax-amount').textContent.replace('₹', ''))
    };
    
    try {
        const response = await fetch('/api/pos/process-sale', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + '{{ auth()->user()->createToken("pos")->plainTextToken }}',
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
</script>
@endif
@endsection