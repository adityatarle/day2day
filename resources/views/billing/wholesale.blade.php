@extends('layouts.app')

@section('title', 'Wholesale Order')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Wholesale Order</h1>
            <a href="{{ route('orders.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Orders
            </a>
        </div>

        <form id="wholesaleForm" class="space-y-6">
            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customerName" class="block text-sm font-medium text-gray-700 mb-2">Customer Name *</label>
                        <input type="text" id="customerName" name="customerName" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="customerPhone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" id="customerPhone" name="customerPhone" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="customerEmail" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="customerEmail" name="customerEmail"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="customerAddress" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea id="customerAddress" name="customerAddress" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="orderDate" class="block text-sm font-medium text-gray-700 mb-2">Order Date *</label>
                        <input type="date" id="orderDate" name="orderDate" required
                               value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="deliveryDate" class="block text-sm font-medium text-gray-700 mb-2">Delivery Date</label>
                        <input type="date" id="deliveryDate" name="deliveryDate"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="branch" class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                        <select id="branch" name="branch" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product Selection -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Product Selection</h2>
                
                <!-- Search Products -->
                <div class="mb-4">
                    <input type="text" id="productSearch" placeholder="Search products..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto mb-6">
                    @foreach($products as $product)
                        @foreach($product->branches as $branch)
                            @if($branch->pivot->current_stock > 0)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 cursor-pointer product-item"
                                     data-product-id="{{ $product->id }}"
                                     data-branch-id="{{ $branch->id }}"
                                     data-product-name="{{ $product->name }}"
                                     data-product-code="{{ $product->code }}"
                                     data-product-price="{{ $branch->pivot->selling_price }}"
                                     data-product-stock="{{ $branch->pivot->current_stock }}"
                                     data-product-unit="{{ $product->weight_unit }}"
                                     data-product-category="{{ $product->category }}">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-medium text-gray-900">{{ $product->name }}</h3>
                                        <span class="text-xs px-2 py-1 rounded-full 
                                            @if($product->category == 'fruit') bg-orange-100 text-orange-800
                                            @elseif($product->category == 'vegetable') bg-green-100 text-green-800
                                            @elseif($product->category == 'leafy') bg-emerald-100 text-emerald-800
                                            @else bg-purple-100 text-purple-800
                                            @endif">
                                            {{ ucfirst($product->category) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-2">{{ $product->code }}</div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-medium text-green-600">₹{{ number_format($branch->pivot->selling_price, 2) }}</span>
                                        <span class="text-sm text-gray-500">Stock: {{ $branch->pivot->current_stock }} {{ $product->weight_unit }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $branch->name }}</div>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>

                <!-- Selected Products Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="selectedProducts" class="bg-white divide-y divide-gray-200">
                            <!-- Selected products will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotal" class="font-medium">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Wholesale Discount (10%):</span>
                            <span id="discount" class="font-medium text-green-600">-₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax (5%):</span>
                            <span id="tax" class="font-medium">₹0.00</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span id="total" class="text-green-600">₹0.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label for="paymentTerms" class="block text-sm font-medium text-gray-700 mb-2">Payment Terms</label>
                            <select id="paymentTerms" name="paymentTerms"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="immediate">Immediate Payment</option>
                                <option value="7days">7 Days</option>
                                <option value="15days">15 Days</option>
                                <option value="30days">30 Days</option>
                            </select>
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Order Notes</label>
                            <textarea id="notes" name="notes" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Any special instructions or notes..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="resetForm()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">
                    Reset
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Create Wholesale Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedProducts = [];
    let subtotal = 0;
    let discount = 0;
    let tax = 0;
    let total = 0;

    // Product search functionality
    const productSearch = document.getElementById('productSearch');
    const productItems = document.querySelectorAll('.product-item');

    productSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        productItems.forEach(item => {
            const productName = item.dataset.productName.toLowerCase();
            const productCode = item.dataset.productCode.toLowerCase();
            if (productName.includes(searchTerm) || productCode.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Add product to selection
    productItems.forEach(item => {
        item.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const branchId = this.dataset.branchId;
            const productName = this.dataset.productName;
            const productCode = this.dataset.productCode;
            const productPrice = parseFloat(this.dataset.productPrice);
            const productStock = parseInt(this.dataset.productStock);
            const productUnit = this.dataset.productUnit;
            const productCategory = this.dataset.productCategory;

            // Check if product is already selected
            const existingItem = selectedProducts.find(item => item.productId === productId && item.branchId === branchId);
            
            if (existingItem) {
                if (existingItem.quantity < productStock) {
                    existingItem.quantity++;
                } else {
                    alert('Cannot add more items. Stock limit reached.');
                    return;
                }
            } else {
                selectedProducts.push({
                    productId: productId,
                    branchId: branchId,
                    productName: productName,
                    productCode: productCode,
                    productPrice: productPrice,
                    productStock: productStock,
                    productUnit: productUnit,
                    productCategory: productCategory,
                    quantity: 1
                });
            }

            updateSelectedProducts();
        });
    });

    // Update selected products display
    function updateSelectedProducts() {
        const tableBody = document.getElementById('selectedProducts');
        tableBody.innerHTML = '';

        subtotal = 0;

        selectedProducts.forEach((item, index) => {
            const itemTotal = item.productPrice * item.quantity;
            subtotal += itemTotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${item.productName}</div>
                    <div class="text-sm text-gray-500">${item.productCategory}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.productCode}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹${item.productPrice.toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-2">
                        <button onclick="updateQuantity(${index}, -1)" class="text-gray-500 hover:text-gray-700">-</button>
                        <span class="font-medium">${item.quantity} ${item.productUnit}</span>
                        <button onclick="updateQuantity(${index}, 1)" class="text-gray-500 hover:text-gray-700">+</button>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₹${itemTotal.toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="removeProduct(${index})" class="text-red-600 hover:text-red-900">Remove</button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        discount = subtotal * 0.10;
        tax = (subtotal - discount) * 0.05;
        total = subtotal - discount + tax;

        document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
        document.getElementById('discount').textContent = `-₹${discount.toFixed(2)}`;
        document.getElementById('tax').textContent = `₹${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `₹${total.toFixed(2)}`;
    }

    // Update quantity
    window.updateQuantity = function(index, change) {
        const item = selectedProducts[index];
        const newQuantity = item.quantity + change;
        
        if (newQuantity > 0 && newQuantity <= item.productStock) {
            item.quantity = newQuantity;
            updateSelectedProducts();
        } else if (newQuantity === 0) {
            removeProduct(index);
        }
    };

    // Remove product
    window.removeProduct = function(index) {
        selectedProducts.splice(index, 1);
        updateSelectedProducts();
    };

    // Reset form
    window.resetForm = function() {
        document.getElementById('wholesaleForm').reset();
        selectedProducts = [];
        updateSelectedProducts();
    };

    // Form submission
    document.getElementById('wholesaleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (selectedProducts.length === 0) {
            alert('Please select products before creating the order.');
            return;
        }

        // Here you would typically send the form data to the server
        // For now, we'll just show a success message
        alert('Wholesale order created successfully!');
        
        // Reset form
        resetForm();
    });

    // Initialize
    updateSelectedProducts();
});
</script>
@endsection