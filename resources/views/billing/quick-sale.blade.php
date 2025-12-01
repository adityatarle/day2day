@extends('layouts.cashier')

@section('title', 'Quick Sale')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-3 sm:py-4 space-y-3 sm:space-y-0">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Quick Sale</h1>
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs sm:text-sm font-medium rounded-full self-start sm:self-auto">
                            {{ $branch->name }}
                        </span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="window.print()" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <div>
                        <strong>Error:</strong>
                        <ul class="list-disc list-inside mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- POS Interface -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Left Panel - Product Selection -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border order-2 lg:order-1">
                <div class="p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Products</h2>
                    
                    <!-- Search and Filter -->
                    <div class="mb-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                        <input type="text" id="product-search" placeholder="Search products by name or SKU..." 
                               class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">All Categories</option>
                            <option value="fruit">Fruit</option>
                            <option value="vegetable">Vegetable</option>
                            <option value="leafy">Leafy</option>
                            <option value="exotic">Exotic</option>
                        </select>
                    </div>

                    <!-- Category Tabs -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button class="category-tab px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg" data-category="">
                            All
                        </button>
                        <button class="category-tab px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200" data-category="fruit">
                            Fruit
                        </button>
                        <button class="category-tab px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200" data-category="vegetable">
                            Vegetable
                        </button>
                        <button class="category-tab px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200" data-category="leafy">
                            Leafy
                        </button>
                        <button class="category-tab px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200" data-category="exotic">
                            Exotic
                        </button>
                    </div>

                    <!-- Products Grid -->
                    <div id="products-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 max-h-[500px] overflow-y-auto pr-2">
                        @foreach($products as $product)
                            @foreach($product->branches as $branch)
                                @if($branch->pivot->current_stock > 0)
                                    <div class="product-card border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow"
                                         data-product-id="{{ $product->id }}"
                                         data-product-name="{{ $product->name }}"
                                         data-product-category="{{ strtolower($product->category ?? '') }}">
                                        <div class="text-sm font-medium text-gray-900 mb-1">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-500 mb-2">{{ $product->code }}</div>
                                        <div class="text-lg font-bold text-green-600">₹{{ number_format($branch->pivot->selling_price, 2) }}</div>
                                        <div class="text-xs text-gray-400 mt-1">Stock: {{ number_format($branch->pivot->current_stock, 2) }} {{ $product->weight_unit }}</div>
                                        <div class="text-xs text-blue-500 mb-2">{{ $product->category ?? 'fruit' }}</div>
                                        
                                        <div class="mt-2 space-y-2 border-t pt-2">
                                            <div class="flex items-center space-x-2">
                                                <label class="text-xs text-gray-600">Bill by:</label>
                                                <select class="billing-method flex-1 text-xs border border-gray-300 rounded px-2 py-1">
                                                    <option value="kg">kg</option>
                                                    <option value="gm">gm</option>
                                                    <option value="pcs">pcs</option>
                                                </select>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <input type="number" class="quantity-input w-full text-xs border border-gray-300 rounded px-2 py-1" 
                                                       step="0.01" min="0" placeholder="0" value="0"
                                                       data-product-id="{{ $product->id }}"
                                                       data-branch-id="{{ $branch->id }}"
                                                       data-product-name="{{ $product->name }}"
                                                       data-product-price="{{ $branch->pivot->selling_price }}"
                                                       data-product-stock="{{ $branch->pivot->current_stock }}"
                                                       data-product-unit="{{ $product->weight_unit }}">
                                            </div>
                                            <button onclick="addProductToCart(this)" 
                                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-1.5 px-2 rounded">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Panel - Cart and Checkout -->
            <div class="bg-white rounded-xl shadow-sm border order-1 lg:order-2 lg:sticky lg:top-6 lg:h-fit">
                <div class="p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Cart</h2>
                    
                    <!-- Customer Selection -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700">Customer</label>
                            <button onclick="openCustomerModal()" class="text-xs text-purple-600 hover:text-purple-800">
                                <i class="fas fa-plus mr-1"></i>Add
                            </button>
                        </div>
                        <select id="customerSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} - {{ $customer->phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Cart Items -->
                    <div id="cartItems" class="space-y-2 mb-4 max-h-64 overflow-y-auto border-t border-b py-3">
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                            <p class="text-sm">Cart is empty</p>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotal" class="font-medium">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax (0%):</span>
                            <span id="tax" class="font-medium">₹0.00</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span id="total" class="text-green-600">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-4">
                        <label for="paymentMethod" class="block text-sm font-semibold text-gray-700 mb-2">Payment Method</label>
                        <select id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        <button id="completeSale" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            <i class="fas fa-check-circle mr-2"></i>
                            Complete Sale
                        </button>
                        <button onclick="clearCart()" class="w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg">
                            <i class="fas fa-trash mr-2"></i>
                            Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
let subtotal = 0;
let tax = 0;
let total = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Category tabs
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.category-tab').forEach(t => {
                t.classList.remove('bg-gray-900', 'text-white');
                t.classList.add('bg-gray-100', 'text-gray-700');
            });
            this.classList.remove('bg-gray-100', 'text-gray-700');
            this.classList.add('bg-gray-900', 'text-white');
            
            const category = this.dataset.category;
            filterProducts(category);
        });
    });
    
    // Search
    document.getElementById('product-search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const category = document.getElementById('category-filter').value;
        filterProductsBySearchAndCategory(searchTerm, category);
    });
    
    // Category filter
    document.getElementById('category-filter').addEventListener('change', function() {
        const searchTerm = document.getElementById('product-search').value.toLowerCase();
        const category = this.value;
        filterProductsBySearchAndCategory(searchTerm, category);
    });
    
    // Complete sale
    document.getElementById('completeSale').addEventListener('click', completeSale);
    
    updateCart();
});

function filterProducts(category) {
    const products = document.querySelectorAll('.product-card');
    products.forEach(product => {
        const productCategory = product.dataset.productCategory;
        if (category === '' || productCategory === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

function filterProductsBySearchAndCategory(searchTerm, category) {
    const products = document.querySelectorAll('.product-card');
    products.forEach(product => {
        const productName = product.dataset.productName.toLowerCase();
        const productCategory = product.dataset.productCategory;
        
        const matchesSearch = searchTerm === '' || productName.includes(searchTerm);
        const matchesCategory = category === '' || productCategory === category;
        
        if (matchesSearch && matchesCategory) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

function addProductToCart(button) {
    const card = button.closest('.product-card');
    const input = card.querySelector('.quantity-input');
    const billingMethod = card.querySelector('.billing-method').value;
    
    const quantity = parseFloat(input.value);
    if (!quantity || quantity <= 0) {
        alert('Please enter a valid quantity');
        return;
    }
    
    const productId = input.dataset.productId;
    const branchId = input.dataset.branchId;
    const productName = input.dataset.productName;
    const productPrice = parseFloat(input.dataset.productPrice);
    const productStock = parseFloat(input.dataset.productStock);
    
    if (quantity > productStock) {
        alert('Quantity exceeds available stock');
        return;
    }
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.productId === productId && item.branchId === branchId);
    
    if (existingItem) {
        if (existingItem.quantity + quantity <= productStock) {
            existingItem.quantity += quantity;
        } else {
            alert('Total quantity would exceed available stock');
            return;
        }
    } else {
        cart.push({
            productId: productId,
            branchId: branchId,
            productName: productName,
            productPrice: productPrice,
            productStock: productStock,
            quantity: quantity,
            unit: billingMethod
        });
    }
    
    input.value = '0';
    updateCart();
}

function updateCart() {
    const cartContainer = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        cartContainer.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                <p class="text-sm">Cart is empty</p>
            </div>
        `;
    } else {
        cartContainer.innerHTML = '';
        subtotal = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.productPrice * item.quantity;
            subtotal += itemTotal;

            const cartItem = document.createElement('div');
            cartItem.className = 'flex justify-between items-center p-2 bg-gray-50 rounded border';
            cartItem.innerHTML = `
                <div class="flex-1 min-w-0 mr-2">
                    <div class="font-medium text-sm truncate">${item.productName}</div>
                    <div class="text-xs text-gray-500">₹${item.productPrice.toFixed(2)} × ${item.quantity} ${item.unit}</div>
                    <div class="text-xs font-medium text-green-600">₹${itemTotal.toFixed(2)}</div>
                </div>
                <button onclick="removeItem(${index})" class="w-7 h-7 bg-red-50 hover:bg-red-100 rounded text-red-600 text-sm font-bold">×</button>
            `;
            cartContainer.appendChild(cartItem);
        });
    }

    tax = 0; // No tax for quick sale
    total = subtotal + tax;

    document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `₹${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `₹${total.toFixed(2)}`;

    document.getElementById('completeSale').disabled = cart.length === 0;
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCart();
}

function clearCart() {
    if (cart.length > 0 && confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        updateCart();
    }
}

function completeSale() {
    if (cart.length === 0) {
        alert('Please add items to cart before completing sale.');
        return;
    }

    const submitBtn = document.getElementById('completeSale');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

    // Prepare form data
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("billing.quickSale.store") }}';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    form.appendChild(csrfInput);

    // Add customer_id
    const customerId = document.getElementById('customerSelect').value;
    if (customerId) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'customer_id';
        input.value = customerId;
        form.appendChild(input);
    }

    // Add payment_method
    const paymentMethodInput = document.createElement('input');
    paymentMethodInput.type = 'hidden';
    paymentMethodInput.name = 'payment_method';
    paymentMethodInput.value = document.getElementById('paymentMethod').value;
    form.appendChild(paymentMethodInput);

    // Add items
    cart.forEach((item, index) => {
        const productInput = document.createElement('input');
        productInput.type = 'hidden';
        productInput.name = `items[${index}][product_id]`;
        productInput.value = item.productId;
        form.appendChild(productInput);

        const quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = `items[${index}][quantity]`;
        quantityInput.value = item.quantity;
        form.appendChild(quantityInput);

        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = `items[${index}][unit_price]`;
        priceInput.value = item.productPrice;
        form.appendChild(priceInput);
    });

    document.body.appendChild(form);
    form.submit();
}

function openCustomerModal() {
    window.location.href = '{{ route("customers.create") }}?redirect_to=' + encodeURIComponent(window.location.href);
}
</script>

<style>
/* Custom scrollbar */
#products-grid::-webkit-scrollbar,
#cartItems::-webkit-scrollbar {
    width: 6px;
}

#products-grid::-webkit-scrollbar-track,
#cartItems::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#products-grid::-webkit-scrollbar-thumb,
#cartItems::-webkit-scrollbar-thumb {
    background: #9333ea;
    border-radius: 10px;
}

#products-grid::-webkit-scrollbar-thumb:hover,
#cartItems::-webkit-scrollbar-thumb:hover {
    background: #7c3aed;
}
</style>
@endsection
