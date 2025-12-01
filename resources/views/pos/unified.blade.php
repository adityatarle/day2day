@extends('layouts.cashier')

@section('title', 'POS System - Main')

@section('content')
<div x-data="posSystem()" class="min-h-screen bg-gray-50 -mx-4 sm:-mx-6 lg:-mx-8 -mt-4 sm:-mt-6 lg:-mt-8">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">POS System</h1>
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs sm:text-sm font-medium rounded-md">
                        {{ $branch->name }}
                    </span>
                    @if($currentSession)
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                            <span class="text-xs sm:text-sm text-gray-600">Session Active</span>
                            <span class="text-xs text-gray-500">Terminal: {{ $currentSession->terminal_id ?? 'POS001' }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('billing.quickSale') }}" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-bolt mr-1"></i>Quick Sale
                    </a>
                    @if($currentSession)
                        <a href="{{ route('pos.close-session') }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                            Close Session
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main POS Interface -->
    <div class="flex h-[calc(100vh-120px)] relative">
        <!-- Center: Products Section -->
        <div class="flex-1 overflow-hidden flex flex-col min-w-0 z-10">
            <div class="bg-white border-b p-4">
                <!-- Search and Category Filter -->
                <div class="flex flex-col sm:flex-row gap-3 mb-3">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            @input="filterProducts()"
                            placeholder="Search products by name or SKU..." 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                        >
                    </div>
                    <select 
                        x-model="selectedCategory"
                        @change="filterProducts()"
                        class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                    >
                        <option value="">All Categories</option>
                        <template x-for="category in categories" :key="category">
                            <option :value="category" x-text="category.charAt(0).toUpperCase() + category.slice(1)"></option>
                        </template>
                    </select>
                    <button class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-barcode mr-1"></i>Scan
                    </button>
                </div>
                
                <!-- Category Tabs -->
                <div class="flex flex-wrap gap-2">
                    <button 
                        @click="selectedCategory = ''; filterProducts()"
                        :class="selectedCategory === '' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                    >
                        All
                    </button>
                    <template x-for="category in categories" :key="category">
                        <button 
                            @click="selectedCategory = category; filterProducts()"
                            :class="selectedCategory === category ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                            x-text="category.charAt(0).toUpperCase() + category.slice(1)"
                        ></button>
                    </template>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                            <div class="text-sm font-medium text-gray-900 mb-1 truncate" x-text="product.name"></div>
                            <div class="text-xs text-gray-500 mb-2" x-text="product.code"></div>
                            <div class="text-base font-semibold text-gray-900 mb-2">₹<span x-text="formatPrice(product.selling_price)"></span></div>
                            <div class="text-xs text-gray-600 mb-2">
                                Stock: <span x-text="formatStock(product.current_stock, product.weight_unit)"></span>
                            </div>
                            <div class="text-xs text-blue-600 mb-3" x-text="product.category"></div>
                            
                            <!-- Unit Selection -->
                            <div class="mb-2">
                                <label class="text-xs text-gray-600 mb-1 block">Bill by:</label>
                                <select 
                                    x-model="product.selectedUnit"
                                    class="w-full text-xs border border-gray-300 rounded px-2 py-1"
                                    @change="updateProductUnit(product)"
                                >
                                    <template x-if="product.bill_by === 'weight'">
                                        <template x-for="unit in ['kg', 'gram', 'piece', 'dozen']" :key="unit">
                                            <option :value="unit" x-text="unit"></option>
                                        </template>
                                    </template>
                                    <template x-if="product.bill_by === 'count'">
                                        <template x-for="unit in ['piece', 'packet', 'box', 'dozen']" :key="unit">
                                            <option :value="unit" x-text="unit"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>
                            
                            <!-- Quantity Input -->
                            <div class="mb-2">
                                <input 
                                    type="number" 
                                    x-model="product.quantity"
                                    :placeholder="getUnitPlaceholder(product.selectedUnit)"
                                    step="0.01"
                                    min="0"
                                    class="w-full text-xs border border-gray-300 rounded px-2 py-1"
                                >
                            </div>
                            
                            <!-- Add to Cart Button -->
                            <button 
                                @click="addToCart(product)"
                                :disabled="!product.quantity || product.quantity <= 0 || product.current_stock <= 0"
                                class="w-full bg-gray-900 hover:bg-gray-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-medium py-2 rounded-md transition-colors"
                            >
                                Add to Cart
                            </button>
                        </div>
                    </template>
                </div>
                
                <div x-show="filteredProducts.length === 0" class="text-center py-12 text-gray-500">
                    <i class="fas fa-box-open text-4xl mb-3"></i>
                    <p>No products found</p>
                </div>
            </div>
        </div>

        <!-- Right: Cart Section -->
        <div class="w-96 bg-white border-l-2 border-gray-300 flex flex-col h-full z-50 shadow-xl flex-shrink-0">
            <div class="p-4 border-b flex items-center justify-between flex-shrink-0">
                <h2 class="text-lg font-semibold text-gray-900">Cart</h2>
                <button 
                    @click="clearCart()"
                    x-show="cart.length > 0"
                    class="text-red-600 hover:text-red-700 text-sm font-medium"
                >
                    <i class="fas fa-trash mr-1"></i>Clear All
                </button>
            </div>

            <!-- Customer Selection -->
            <div class="p-4 border-b flex-shrink-0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                <div class="flex space-x-2">
                    <select 
                        x-model="selectedCustomer"
                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                    >
                        <option value="">Walk-in Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    <button class="bg-gray-900 hover:bg-gray-800 text-white px-3 py-2 rounded-md text-sm">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-x-hidden p-4 overflow-y-auto" :style="cart.length > 0 ? 'min-height: 200px;' : 'min-height: 400px;'">
                <template x-if="cart.length === 0">
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                        <p>Cart is empty</p>
                    </div>
                </template>
                
                <div class="space-y-3" x-show="cart.length > 0">
                    <template x-for="(item, index) in cart" :key="item.cartId">
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 w-full">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <div class="font-medium text-sm text-gray-900" x-text="item.name"></div>
                                <div class="text-xs text-gray-500" x-text="item.code"></div>
                            </div>
                            <button 
                                @click="removeFromCart(index)"
                                class="text-red-600 hover:text-red-700 text-sm"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-2">
                            <!-- Quantity and Unit -->
                            <div class="flex items-center space-x-2 min-w-0">
                                <input 
                                    type="number" 
                                    x-model="item.originalQuantity"
                                    @input="updateCartItem(index)"
                                    :placeholder="getUnitPlaceholder(item.unit)"
                                    step="0.01"
                                    min="0"
                                    class="flex-1 text-xs border border-gray-300 rounded px-2 py-1 min-w-0"
                                >
                                <select 
                                    x-model="item.unit"
                                    @change="updateCartItemUnit(index)"
                                    class="text-xs border border-gray-300 rounded px-2 py-1 w-20 flex-shrink-0"
                                >
                                    <template x-if="item.billBy === 'weight'">
                                        <template x-for="unit in ['kg', 'gram', 'piece', 'dozen']" :key="unit">
                                            <option :value="unit" :selected="item.unit === unit" x-text="unit"></option>
                                        </template>
                                    </template>
                                    <template x-if="item.billBy === 'count'">
                                        <template x-for="unit in ['piece', 'packet', 'box', 'dozen']" :key="unit">
                                            <option :value="unit" :selected="item.unit === unit" x-text="unit"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>
                            
                            <!-- Price Display -->
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Price:</span>
                                <span class="font-medium text-gray-900">₹<span x-text="formatPrice(item.unitPrice)"></span> per <span x-text="item.baseUnit"></span></span>
                            </div>
                            
                            <!-- Total for this item -->
                            <div class="flex justify-between text-sm font-semibold border-t pt-2">
                                <span>Total:</span>
                                <span class="text-gray-900">₹<span x-text="formatPrice(item.total)"></span></span>
                            </div>
                        </div>
                    </div>
                    </template>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="border-t p-4 space-y-3 bg-gray-50">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Items:</span>
                    <span class="font-medium text-gray-900" x-text="cart.length"></span>
                </div>
                
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium text-gray-900">₹<span x-text="formatPrice(subtotal)"></span></span>
                </div>
                
                <!-- Discount -->
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Discount:</label>
                    <div class="flex space-x-2">
                        <input 
                            type="number" 
                            x-model="discount"
                            @input="calculateTotals()"
                            min="0"
                            step="0.01"
                            class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm"
                        >
                        <select 
                            x-model="discountType"
                            @change="calculateTotals()"
                            class="border border-gray-300 rounded px-2 py-1 text-sm"
                        >
                            <option value="fixed">₹</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                </div>
                
                <!-- Tax -->
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Tax:</label>
                    <div class="flex space-x-2">
                        <input 
                            type="number" 
                            x-model="tax"
                            @input="calculateTotals()"
                            min="0"
                            step="0.01"
                            class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm"
                        >
                        <select 
                            x-model="taxType"
                            @change="calculateTotals()"
                            class="border border-gray-300 rounded px-2 py-1 text-sm"
                        >
                            <option value="fixed">₹</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                </div>
                
                <div class="border-t pt-3">
                    <div class="flex justify-between text-lg font-semibold">
                        <span>Total:</span>
                        <span class="text-gray-900">₹<span x-text="formatPrice(total)"></span></span>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2">Payment Method</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-300 text-gray-700'"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            <i class="fas fa-money-bill-wave mr-1"></i>Cash
                        </button>
                        <button 
                            @click="paymentMethod = 'card'"
                            :class="paymentMethod === 'card' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-300 text-gray-700'"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            <i class="fas fa-credit-card mr-1"></i>Card
                        </button>
                    </div>
                </div>
                
                <!-- Checkout Button -->
                <button 
                    @click="processCheckout()"
                    :disabled="cart.length === 0 || total <= 0"
                    class="w-full bg-gray-900 hover:bg-gray-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-md transition-colors"
                >
                    <i class="fas fa-check mr-2"></i>Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function posSystem() {
    return {
        // Products data
        products: @json($products),
        filteredProducts: [],
        searchQuery: '',
        selectedCategory: '',
        categories: [],
        
        // Cart data
        cart: [],
        cartIdCounter: 0,
        selectedCustomer: '',
        
        // Order totals
        subtotal: 0,
        discount: 0,
        discountType: 'fixed',
        tax: 0,
        taxType: 'fixed',
        total: 0,
        paymentMethod: 'cash',
        
        init() {
            // Initialize products with default values
            this.products = this.products.map(p => ({
                ...p,
                selectedUnit: p.weight_unit || 'kg',
                quantity: 0
            }));
            
            // Extract unique categories
            this.categories = [...new Set(this.products.map(p => p.category))];
            this.filteredProducts = this.products;
            this.calculateTotals();
        },
        
        filterProducts() {
            this.filteredProducts = this.products.filter(product => {
                const matchesSearch = !this.searchQuery || 
                    product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    product.code.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesCategory = !this.selectedCategory || product.category === this.selectedCategory;
                return matchesSearch && matchesCategory;
            });
        },
        
        getUnitPlaceholder(unit) {
            const placeholders = {
                'kg': '0.00 kg',
                'gram': '0 g',
                'piece': '0 pcs',
                'dozen': '0 dz',
                'packet': '0 pkt',
                'box': '0 box'
            };
            return placeholders[unit] || '0';
        },
        
        convertToBaseUnit(quantity, fromUnit, baseUnit) {
            // Convert quantity to base unit (kg for weight-based, piece for count-based)
            let inBase = parseFloat(quantity) || 0;
            
            // If already in base unit, return as is
            if (fromUnit === baseUnit) {
                return inBase;
            }
            
            // Weight-based conversions (base unit is kg)
            if (baseUnit === 'kg' || baseUnit === 'gram') {
                if (fromUnit === 'gram') {
                    return inBase / 1000; // Convert grams to kg
                } else if (fromUnit === 'piece' && baseUnit === 'kg') {
                    // For weight products sold by piece, assume 1 piece = some weight
                    // This might need product-specific configuration
                    return inBase; // Keep as is for now
                }
            }
            
            // Count-based conversions (base unit is piece)
            if (baseUnit === 'piece' || baseUnit === 'pcs') {
                if (fromUnit === 'dozen') {
                    return inBase * 12; // Convert dozen to pieces
                } else if (fromUnit === 'packet' || fromUnit === 'box') {
                    // Assume 1 packet/box = 1 piece (can be configured per product)
                    return inBase;
                }
            }
            
            return inBase;
        },
        
        updateProductUnit(product) {
            // Reset quantity when unit changes
            product.quantity = 0;
        },
        
        addToCart(product) {
            if (!product.quantity || product.quantity <= 0) return;
            
            const quantity = parseFloat(product.quantity) || 0;
            const selectedUnit = product.selectedUnit || product.weight_unit;
            
            // Convert quantity to base unit for calculation
            const baseQuantity = this.convertToBaseUnit(
                quantity, 
                selectedUnit, 
                product.weight_unit
            );
            
            // Price is always per base unit (stored in product.selling_price)
            // We convert quantity to base unit and multiply by base price
            const basePrice = product.selling_price;
            
            // Check if product already in cart with same unit
            const existingIndex = this.cart.findIndex(item => item.id === product.id && item.unit === selectedUnit);
            
            if (existingIndex >= 0) {
                // Update existing item with same unit
                const existing = this.cart[existingIndex];
                const newBaseQty = this.convertToBaseUnit(quantity, selectedUnit, existing.baseUnit);
                existing.quantity = parseFloat(existing.quantity) + parseFloat(newBaseQty);
                existing.originalQuantity = parseFloat(existing.originalQuantity || 0) + quantity;
                existing.total = existing.quantity * existing.unitPrice;
            } else {
                // Add new item - store original quantity and unit for display, but calculate using base
                this.cart.push({
                    cartId: ++this.cartIdCounter,
                    id: product.id,
                    name: product.name,
                    code: product.code,
                    quantity: baseQuantity, // Store in base unit for calculation
                    originalQuantity: quantity, // Store original for display
                    unit: selectedUnit,
                    baseUnit: product.weight_unit,
                    unitPrice: basePrice, // Always base price
                    billBy: product.bill_by,
                    total: baseQuantity * basePrice
                });
            }
            
            // Reset product quantity
            product.quantity = 0;
            this.calculateTotals();
        },
        
        updateCartItem(index) {
            const item = this.cart[index];
            // Initialize originalQuantity if not set (for backward compatibility)
            if (item.originalQuantity === undefined) {
                item.originalQuantity = item.quantity;
            }
            const originalQty = parseFloat(item.originalQuantity) || 0;
            
            if (originalQty <= 0) {
                this.removeFromCart(index);
                return;
            }
            
            // Convert original quantity to base unit
            const baseQuantity = this.convertToBaseUnit(originalQty, item.unit, item.baseUnit);
            
            // Update item quantity (in base unit) and total
            item.quantity = baseQuantity;
            item.total = baseQuantity * item.unitPrice;
            this.calculateTotals();
        },
        
        updateCartItemUnit(index) {
            const item = this.cart[index];
            // When unit changes, convert the base quantity back to the new unit for display
            // We need to reverse convert: from base unit to new unit
            const baseQty = item.quantity; // This is in base unit
            
            // Convert base quantity to new unit for display
            if (item.unit === 'gram' && item.baseUnit === 'kg') {
                item.originalQuantity = baseQty * 1000;
            } else if (item.unit === 'dozen' && (item.baseUnit === 'piece' || item.billBy === 'count')) {
                item.originalQuantity = baseQty / 12;
            } else if (item.unit === item.baseUnit) {
                item.originalQuantity = baseQty;
            } else {
                // For other conversions, keep the same
                item.originalQuantity = baseQty;
            }
            
            // Recalculate total (quantity in base unit stays the same, just display changes)
            item.total = item.quantity * item.unitPrice;
            this.calculateTotals();
        },
        
        removeFromCart(index) {
            this.cart.splice(index, 1);
            this.calculateTotals();
        },
        
        clearCart() {
            if (confirm('Are you sure you want to clear the cart?')) {
                this.cart = [];
                this.calculateTotals();
            }
        },
        
        calculateTotals() {
            // Calculate subtotal
            this.subtotal = this.cart.reduce((sum, item) => sum + item.total, 0);
            
            // Calculate discount
            let discountAmount = 0;
            if (this.discountType === 'percent') {
                discountAmount = (this.subtotal * this.discount) / 100;
            } else {
                discountAmount = this.discount;
            }
            
            // Calculate tax
            let taxAmount = 0;
            const taxableAmount = this.subtotal - discountAmount;
            if (this.taxType === 'percent') {
                taxAmount = (taxableAmount * this.tax) / 100;
            } else {
                taxAmount = this.tax;
            }
            
            // Calculate total
            this.total = taxableAmount + taxAmount;
        },
        
        formatPrice(price) {
            return parseFloat(price || 0).toFixed(2);
        },
        
        formatStock(stock, unit) {
            return parseFloat(stock || 0).toFixed(2) + ' ' + unit;
        },
        
        async processCheckout() {
            if (this.cart.length === 0) {
                alert('Cart is empty');
                return;
            }
            
            // Prepare order data
            const orderData = {
                customer_id: this.selectedCustomer || null,
                items: this.cart.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    unit: item.unit,
                    unit_price: item.unitPrice,
                    total_price: item.total
                })),
                subtotal: this.subtotal,
                discount: this.discountType === 'percent' ? (this.subtotal * this.discount) / 100 : this.discount,
                tax: this.taxType === 'percent' ? ((this.subtotal - this.discount) * this.tax) / 100 : this.tax,
                total: this.total
            };
            
            // Store order data in session and redirect to payment page
            try {
                const response = await fetch('/api/pos/prepare-order', {
                    method: 'POST',
                    credentials: 'same-origin', // Include cookies/session
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(orderData)
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server error:', errorText);
                    alert('Server error: ' + response.status + '. Please check console for details.');
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Small delay to ensure session is saved before redirect
                    await new Promise(resolve => setTimeout(resolve, 100));
                    
                    // Redirect to payment page
                    window.location.href = '/pos/payment?order_token=' + result.data.order_token;
                } else {
                    alert('Error: ' + (result.message || 'Failed to prepare order'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to prepare order: ' + error.message + '. Please check console for details.');
            }
        }
    }
}
</script>
@endsection

