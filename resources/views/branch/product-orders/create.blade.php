@extends('layouts.app')

@section('title', 'Order Products')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('branch.product-orders.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Order Products</h1>
                <p class="text-gray-600">Request products from admin - admin will purchase materials from vendors</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('branch.product-orders.store') }}" id="product-order-form" class="space-y-8">
        @csrf

        <!-- Order Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="form-group">
                    <label for="branch_name" class="form-label">Branch</label>
                    <input type="text" id="branch_name" value="{{ $branch->name }}" class="form-input bg-gray-100" disabled>
                    <p class="text-sm text-gray-500 mt-1">Your branch is pre-selected</p>
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
                    <label for="priority" class="form-label">Priority *</label>
                    <select name="priority" id="priority" class="form-input @error('priority') border-red-500 @enderror" required>
                        <option value="">Select Priority</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group md:col-span-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3" 
                              class="form-input @error('notes') border-red-500 @enderror" 
                              placeholder="Any special instructions or reasons for this order">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Product Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Products to Order</h2>
                    <p class="text-gray-600">Select products and quantities needed - All products available</p>
                </div>
                <button type="button" id="add-item" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Product
                </button>
            </div>

            <!-- Product Search -->
            <div class="mb-6">
                <div class="relative">
                    <input type="text" id="product-search" placeholder="Search products by name or category..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" class="category-filter px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors" data-category="all">All</button>
                    <button type="button" class="category-filter px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors" data-category="fruit">Fruits</button>
                    <button type="button" class="category-filter px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors" data-category="vegetable">Vegetables</button>
                    <button type="button" class="category-filter px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors" data-category="leafy">Leafy</button>
                    <button type="button" class="category-filter px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors" data-category="exotic">Exotic</button>
                    <button type="button" class="category-filter px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors" data-category="herbs">Herbs</button>
                    <button type="button" class="category-filter px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors" data-category="dry_fruits">Dry Fruits</button>
                    <button type="button" class="category-filter px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors" data-category="organic">Organic</button>
                </div>
            </div>

            <div id="items-container" class="space-y-4">
                <!-- Items will be added here dynamically -->
            </div>
        </div>

        <!-- Item Template (hidden, outside form) -->
        <div id="item-template" class="hidden">
            <div class="item-row border border-gray-200 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="form-label">Product *</label>
                        <select name="items[INDEX][product_id]" class="form-input product-select">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-category="{{ $product->category }}">
                                    {{ $product->name }} ({{ $product->category }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="items[INDEX][quantity]" step="0.01" min="0.01" 
                               class="form-input quantity-input">
                    </div>
                    <div>
                        <label class="form-label">Reason for Request *</label>
                        <input type="text" name="items[INDEX][reason]" 
                               class="form-input reason-input" 
                               placeholder="e.g., Low stock, customer demand">
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

        <!-- Form Actions -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('branch.product-orders.index') }}" class="btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Send Order to Admin
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
    const productSearch = document.getElementById('product-search');
    const categoryFilters = document.querySelectorAll('.category-filter');
    
    let allProducts = @json($products);
    let filteredProducts = allProducts;
    let currentCategory = 'all';

    // Add first item by default
    addItem();

    addItemBtn.addEventListener('click', addItem);

    // Product search functionality
    productSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterProducts(searchTerm, currentCategory);
        updateAllProductSelects();
    });

    // Category filter functionality
    categoryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            // Update active filter
            categoryFilters.forEach(f => f.classList.remove('bg-blue-500', 'text-white'));
            this.classList.add('bg-blue-500', 'text-white');
            
            currentCategory = this.dataset.category;
            const searchTerm = productSearch.value.toLowerCase();
            filterProducts(searchTerm, currentCategory);
            updateAllProductSelects();
        });
    });

    function filterProducts(searchTerm, category) {
        filteredProducts = allProducts.filter(product => {
            const matchesSearch = product.name.toLowerCase().includes(searchTerm) || 
                                product.category.toLowerCase().includes(searchTerm);
            const matchesCategory = category === 'all' || product.category === category;
            return matchesSearch && matchesCategory;
        });
    }

    function updateAllProductSelects() {
        const productSelects = document.querySelectorAll('.product-select');
        productSelects.forEach(select => {
            const currentValue = select.value;
            updateProductSelectOptions(select);
            select.value = currentValue; // Restore selected value if it still exists
        });
    }

    function updateProductSelectOptions(select) {
        // Clear existing options except the first one
        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }

        // Add filtered products
        filteredProducts.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = `${product.name} (${product.category})`;
            option.dataset.category = product.category;
            select.appendChild(option);
        });
    }

    function addItem() {
        const template = itemTemplate.innerHTML;
        const newItem = template.replace(/INDEX/g, itemIndex);
        
        const div = document.createElement('div');
        div.innerHTML = newItem;
        const itemRow = div.firstElementChild;
        
        // Add required attributes to the dynamically created elements
        const productSelect = itemRow.querySelector('.product-select');
        const quantityInput = itemRow.querySelector('.quantity-input');
        const reasonInput = itemRow.querySelector('.reason-input');
        
        productSelect.setAttribute('required', 'required');
        quantityInput.setAttribute('required', 'required');
        reasonInput.setAttribute('required', 'required');
        
        // Update product select with filtered products
        updateProductSelectOptions(productSelect);
        
        itemsContainer.appendChild(itemRow);

        // Add event listeners
        const removeBtn = itemRow.querySelector('.remove-item');

        removeBtn.addEventListener('click', function() {
            itemRow.remove();
        });

        itemIndex++;
    }

    // Form validation
    document.getElementById('product-order-form').addEventListener('submit', function(e) {
        console.log('Form submit event triggered');
        
        const items = document.querySelectorAll('.item-row');
        console.log('Number of items found:', items.length);
        
        // Check if there are any items
        if (items.length === 0) {
            e.preventDefault();
            alert('Please add at least one product to the order.');
            return;
        }

        // Check if at least one item has all required fields filled
        let hasValidItem = false;
        let emptyFields = [];
        
        items.forEach(function(row, index) {
            const productId = row.querySelector('.product-select').value;
            const quantity = row.querySelector('.quantity-input').value;
            const reason = row.querySelector('.reason-input').value;
            
            console.log(`Item ${index + 1}: productId=${productId}, quantity=${quantity}, reason=${reason}`);
            
            if (productId && quantity && reason) {
                hasValidItem = true;
            } else {
                if (!productId) emptyFields.push(`Item ${index + 1}: Product not selected`);
                if (!quantity) emptyFields.push(`Item ${index + 1}: Quantity not entered`);
                if (!reason) emptyFields.push(`Item ${index + 1}: Reason not provided`);
            }
        });

        if (!hasValidItem) {
            e.preventDefault();
            alert('Please ensure at least one item has all fields filled:\n' + emptyFields.join('\n'));
            return;
        }

        // Debug: Log form data before submission
        const formData = new FormData(this);
        console.log('Form data being submitted:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        // If we get here, validation passed - allow form submission
        console.log('Form validation passed, submitting...');
    });
});
</script>
@endsection