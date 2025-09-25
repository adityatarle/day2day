@extends('layouts.app')

@section('title', 'Edit Product Order')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('branch.product-orders.show', $productOrder) }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Order Details
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Order #{{ $productOrder->po_number }}</h1>
                <p class="text-gray-600">Only draft orders can be edited</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
        </div>

        <form method="POST" action="{{ route('branch.product-orders.update', $productOrder) }}" id="product-order-form" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="form-label">Branch</label>
                    <input type="text" value="{{ $branch->name }}" class="form-input bg-gray-100" disabled>
                </div>
                <div>
                    <label for="expected_delivery_date" class="form-label">Expected Delivery Date *</label>
                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" 
                           value="{{ old('expected_delivery_date', optional($productOrder->expected_delivery_date)->format('Y-m-d')) }}" 
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="form-input @error('expected_delivery_date') border-red-500 @enderror" required>
                    @error('expected_delivery_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="priority" class="form-label">Priority *</label>
                    <select name="priority" id="priority" class="form-input @error('priority') border-red-500 @enderror" required>
                        @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ old('priority', $productOrder->priority) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="form-input @error('notes') border-red-500 @enderror">{{ old('notes', $productOrder->notes) }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Products</h2>
                        <p class="text-gray-600 text-sm">Update quantities and reasons - All products available</p>
                    </div>
                    <button type="button" id="add-item" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg">Add Product</button>
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
                    @foreach($productOrder->purchaseOrderItems as $idx => $item)
                    <div class="item-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="form-label">Product *</label>
                                <select name="items[{{ $idx }}][product_id]" class="form-input product-select" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->category }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Quantity *</label>
                                <input type="number" name="items[{{ $idx }}][quantity]" step="0.01" min="0.01" value="{{ $item->quantity }}" class="form-input quantity-input" required>
                            </div>
                            <div>
                                <label class="form-label">Reason *</label>
                                <input type="text" name="items[{{ $idx }}][reason]" value="{{ $item->notes }}" class="form-input reason-input" required>
                            </div>
                            <div>
                                <button type="button" class="remove-item w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg">Remove</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('branch.product-orders.show', $productOrder) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Order</button>
            </div>
        </form>
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
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->category }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Quantity *</label>
                <input type="number" name="items[INDEX][quantity]" step="0.01" min="0.01" class="form-input quantity-input">
            </div>
            <div>
                <label class="form-label">Reason *</label>
                <input type="text" name="items[INDEX][reason]" class="form-input reason-input">
            </div>
            <div>
                <button type="button" class="remove-item w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg">Remove</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ $productOrder->purchaseOrderItems->count() }};
    const addItemBtn = document.getElementById('add-item');
    const itemsContainer = document.getElementById('items-container');
    const itemTemplate = document.getElementById('item-template');
    const productSearch = document.getElementById('product-search');
    const categoryFilters = document.querySelectorAll('.category-filter');
    
    let allProducts = @json($products);
    let filteredProducts = allProducts;
    let currentCategory = 'all';

    addItemBtn.addEventListener('click', addItem);

    itemsContainer.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-item')) {
            e.target.closest('.item-row').remove();
        }
    });

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
        itemIndex++;
    }
});
</script>
@endsection

