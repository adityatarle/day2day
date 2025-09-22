@extends('layouts.app')

@section('title', 'Quick Sale')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6 lg:py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Quick Sale</h1>
            <a href="{{ route('orders.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg text-sm sm:text-base touch-target">
                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
                <span class="hidden sm:inline">Back to Orders</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Product Selection -->
            <div class="lg:col-span-2 order-2 lg:order-1">
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Select Products</h2>
                    
                    <!-- Search Products -->
                    <div class="mb-3 sm:mb-4">
                        <input type="text" id="productSearch" placeholder="Search products..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 touch-target">
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 max-h-64 sm:max-h-96 overflow-y-auto">
                        @foreach($products as $product)
                            @foreach($product->branches as $branch)
                                @if($branch->pivot->current_stock > 0)
                                    <div class="border border-gray-200 rounded-lg p-3 sm:p-4 hover:border-blue-300 cursor-pointer product-item touch-target"
                                         data-product-id="{{ $product->id }}"
                                         data-branch-id="{{ $branch->id }}"
                                         data-product-name="{{ $product->name }}"
                                         data-product-price="{{ $branch->pivot->selling_price }}"
                                         data-product-stock="{{ $branch->pivot->current_stock }}"
                                         data-product-unit="{{ $product->weight_unit }}">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-medium text-gray-900 text-sm sm:text-base truncate mr-2">{{ $product->name }}</h3>
                                            <span class="text-xs sm:text-sm text-gray-500 flex-shrink-0">{{ $branch->name }}</span>
                                        </div>
                                        <div class="text-xs sm:text-sm text-gray-600 mb-2">{{ $product->code }}</div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-green-600 text-sm sm:text-base">₹{{ number_format($branch->pivot->selling_price, 2) }}</span>
                                            <span class="text-xs sm:text-sm text-gray-500">Stock: {{ $branch->pivot->current_stock }} {{ $product->weight_unit }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Cart -->
            <div class="lg:col-span-1 order-1 lg:order-2">
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Cart</h2>
                    
                    <div id="cartItems" class="space-y-2 sm:space-y-3 mb-4 sm:mb-6 max-h-48 sm:max-h-64 overflow-y-auto">
                        <!-- Cart items will be populated here -->
                    </div>

                    <!-- Cart Summary -->
                    <div class="border-t border-gray-200 pt-3 sm:pt-4 space-y-2 sm:space-y-3">
                        <div class="flex justify-between text-xs sm:text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotal" class="font-medium">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-xs sm:text-sm">
                            <span class="text-gray-600">Tax (5%):</span>
                            <span id="tax" class="font-medium">₹0.00</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between text-base sm:text-lg font-bold">
                                <span>Total:</span>
                                <span id="total" class="text-green-600">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Selection -->
                    <div class="mt-4 sm:mt-6">
                        <label for="customerSelect" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <select id="customerSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 touch-target">
                            <option value="">Walk-in Customer</option>
                            <!-- Customer options will be populated here -->
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div class="mt-3 sm:mt-4">
                        <label for="paymentMethod" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 touch-target">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <!-- Complete Sale Button -->
                    <button id="completeSale" class="w-full mt-4 sm:mt-6 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed touch-target">
                        <i class="fas fa-check mr-2"></i>
                        Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = [];
    let subtotal = 0;
    let tax = 0;
    let total = 0;

    // Product search functionality
    const productSearch = document.getElementById('productSearch');
    const productItems = document.querySelectorAll('.product-item');

    productSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        productItems.forEach(item => {
            const productName = item.dataset.productName.toLowerCase();
            if (productName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Add product to cart
    productItems.forEach(item => {
        item.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const branchId = this.dataset.branchId;
            const productName = this.dataset.productName;
            const productPrice = parseFloat(this.dataset.productPrice);
            const productStock = parseInt(this.dataset.productStock);
            const productUnit = this.dataset.productUnit;

            // Check if product is already in cart
            const existingItem = cart.find(item => item.productId === productId && item.branchId === branchId);
            
            if (existingItem) {
                if (existingItem.quantity < productStock) {
                    existingItem.quantity++;
                } else {
                    alert('Cannot add more items. Stock limit reached.');
                    return;
                }
            } else {
                cart.push({
                    productId: productId,
                    branchId: branchId,
                    productName: productName,
                    productPrice: productPrice,
                    productStock: productStock,
                    productUnit: productUnit,
                    quantity: 1
                });
            }

            updateCart();
        });
    });

    // Update cart display
    function updateCart() {
        const cartContainer = document.getElementById('cartItems');
        cartContainer.innerHTML = '';

        subtotal = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.productPrice * item.quantity;
            subtotal += itemTotal;

            const cartItem = document.createElement('div');
            cartItem.className = 'flex justify-between items-center p-2 sm:p-3 bg-gray-50 rounded-lg';
            cartItem.innerHTML = `
                <div class="flex-1 min-w-0 mr-3">
                    <div class="font-medium text-gray-900 text-sm sm:text-base truncate">${item.productName}</div>
                    <div class="text-xs sm:text-sm text-gray-500">₹${item.productPrice.toFixed(2)} × ${item.quantity} ${item.productUnit}</div>
                </div>
                <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0">
                    <button onclick="updateQuantity(${index}, -1)" class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 hover:bg-gray-300 rounded text-gray-600 hover:text-gray-800 text-sm sm:text-base touch-target">-</button>
                    <span class="font-medium text-sm sm:text-base w-6 sm:w-8 text-center">${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, 1)" class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 hover:bg-gray-300 rounded text-gray-600 hover:text-gray-800 text-sm sm:text-base touch-target">+</button>
                    <button onclick="removeItem(${index})" class="w-6 h-6 sm:w-8 sm:h-8 bg-red-200 hover:bg-red-300 rounded text-red-600 hover:text-red-800 text-sm sm:text-base ml-1 sm:ml-2 touch-target">×</button>
                </div>
            `;
            cartContainer.appendChild(cartItem);
        });

        tax = subtotal * 0.05;
        total = subtotal + tax;

        document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
        document.getElementById('tax').textContent = `₹${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `₹${total.toFixed(2)}`;

        // Enable/disable complete sale button
        document.getElementById('completeSale').disabled = cart.length === 0;
    }

    // Update quantity
    window.updateQuantity = function(index, change) {
        const item = cart[index];
        const newQuantity = item.quantity + change;
        
        if (newQuantity > 0 && newQuantity <= item.productStock) {
            item.quantity = newQuantity;
            updateCart();
        } else if (newQuantity === 0) {
            removeItem(index);
        }
    };

    // Remove item
    window.removeItem = function(index) {
        cart.splice(index, 1);
        updateCart();
    };

    // Complete sale
    document.getElementById('completeSale').addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Please add items to cart before completing sale.');
            return;
        }

        // Here you would typically send the cart data to the server
        // For now, we'll just show a success message
        alert('Sale completed successfully!');
        
        // Clear cart
        cart = [];
        updateCart();
    });

    // Initialize cart
    updateCart();
});
</script>
@endsection