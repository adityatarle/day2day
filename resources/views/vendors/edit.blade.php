@extends('layouts.app')

@section('title', 'Edit Vendor - ' . $vendor->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('vendors.show', $vendor) }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Vendor</h1>
                <p class="text-gray-600">Update vendor information and product pricing</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('vendors.update', $vendor) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Basic Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label for="name" class="form-label">Vendor Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $vendor->name) }}" 
                           class="form-input @error('name') border-red-500 @enderror" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">Vendor Code *</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $vendor->code) }}" 
                           class="form-input @error('code') border-red-500 @enderror" required>
                    @error('code')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $vendor->email) }}" 
                           class="form-input @error('email') border-red-500 @enderror" required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number *</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $vendor->phone) }}" 
                           class="form-input @error('phone') border-red-500 @enderror" required>
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group md:col-span-2">
                    <label for="address" class="form-label">Address *</label>
                    <textarea name="address" id="address" rows="3" 
                              class="form-input @error('address') border-red-500 @enderror" required>{{ old('address', $vendor->address) }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="gst_number" class="form-label">GST Number</label>
                    <input type="text" name="gst_number" id="gst_number" value="{{ old('gst_number', $vendor->gst_number) }}" 
                           class="form-input @error('gst_number') border-red-500 @enderror">
                    @error('gst_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', $vendor->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">Active Vendor</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Pricing -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Product Pricing</h2>
                    <p class="text-gray-600">Set supply prices for products this vendor provides</p>
                </div>
                <button type="button" id="add-product" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Product
                </button>
            </div>

            <div id="products-container" class="space-y-4">
                @foreach($vendor->products as $index => $product)
                    <div class="product-row border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="form-label">Product</label>
                                <select name="products[{{ $index }}][product_id]" class="form-input">
                                    <option value="">Select Product</option>
                                    @foreach($allProducts as $prod)
                                        <option value="{{ $prod->id }}" {{ $prod->id == $product->id ? 'selected' : '' }}>
                                            {{ $prod->name }} ({{ ucfirst($prod->category) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Supply Price (₹)</label>
                                <input type="number" name="products[{{ $index }}][supply_price]" 
                                       value="{{ $product->pivot->supply_price }}" 
                                       step="0.01" min="0" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Primary Supplier</label>
                                <div class="flex items-center h-10">
                                    <input type="checkbox" name="products[{{ $index }}][is_primary_supplier]" 
                                           value="1" {{ $product->pivot->is_primary_supplier ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-600">Primary supplier</span>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="remove-product w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg transition-colors">
                                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Product Template (hidden) -->
            <div id="product-template" class="hidden">
                <div class="product-row border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="form-label">Product</label>
                            <select name="products[INDEX][product_id]" class="form-input">
                                <option value="">Select Product</option>
                                @foreach($allProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ ucfirst($product->category) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Supply Price (₹)</label>
                            <input type="number" name="products[INDEX][supply_price]" step="0.01" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Primary Supplier</label>
                            <div class="flex items-center h-10">
                                <input type="checkbox" name="products[INDEX][is_primary_supplier]" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-600">Primary supplier</span>
                            </div>
                        </div>
                        <div>
                            <button type="button" class="remove-product w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg transition-colors">
                                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-between">
            <div>
                @if($vendor->purchaseOrders()->count() === 0)
                    <button type="button" onclick="confirmDelete()" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Vendor
                    </button>
                @endif
            </div>
            <div class="flex gap-4">
                <a href="{{ route('vendors.show', $vendor) }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Vendor
                </button>
            </div>
        </div>
    </form>

    <!-- Delete Form (hidden) -->
    <form id="delete-form" method="POST" action="{{ route('vendors.destroy', $vendor) }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let productIndex = {{ $vendor->products->count() }};
    const addProductBtn = document.getElementById('add-product');
    const productsContainer = document.getElementById('products-container');
    const productTemplate = document.getElementById('product-template');

    // Add existing remove functionality
    document.querySelectorAll('.remove-product').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.product-row').remove();
        });
    });

    addProductBtn.addEventListener('click', function() {
        const template = productTemplate.innerHTML.replace(/disabled/g, '');
        const newProduct = template.replace(/INDEX/g, productIndex);
        
        const div = document.createElement('div');
        div.innerHTML = newProduct;
        productsContainer.appendChild(div.firstElementChild);

        // Add remove functionality
        const removeBtn = productsContainer.lastElementChild.querySelector('.remove-product');
        removeBtn.addEventListener('click', function() {
            this.closest('.product-row').remove();
        });

        productIndex++;
    });
});

function confirmDelete() {
    if (confirm('Are you sure you want to delete this vendor? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection