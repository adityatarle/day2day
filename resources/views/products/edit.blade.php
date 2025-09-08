@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="p-6">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
            <p class="text-gray-600 mt-1">Update product details, pricing and suppliers.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn-secondary">Back to Products</a>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
            <div class="font-medium">There were some problems with your input:</div>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Code</label>
                    <input type="text" value="{{ $product->code }}" class="form-input bg-gray-50" disabled>
                </div>
                <div>
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-input" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category', $product->category)===$cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-input">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Pricing & Stock</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="form-label">Weight Unit *</label>
                    <select name="weight_unit" class="form-input" required>
                        @foreach($weight_units as $unit)
                            <option value="{{ $unit }}" {{ old('weight_unit', $product->weight_unit)===$unit ? 'selected' : '' }}>{{ strtoupper($unit) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Purchase Price (₹) *</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">MRP (₹) *</label>
                    <input type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp', $product->mrp) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Default Selling Price (₹) *</label>
                    <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Low Stock Threshold *</label>
                    <input type="number" min="0" name="stock_threshold" value="{{ old('stock_threshold', $product->stock_threshold) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Shelf Life (days)</label>
                    <input type="number" min="0" name="shelf_life_days" value="{{ old('shelf_life_days', $product->shelf_life_days) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Storage Temperature</label>
                    <input type="text" name="storage_temperature" value="{{ old('storage_temperature', $product->storage_temperature) }}" class="form-input" placeholder="e.g., 2-8°C">
                </div>
                <div class="flex items-center mt-8">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_perishable" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded" {{ old('is_perishable', $product->is_perishable) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Perishable</span>
                    </label>
                </div>
                <div class="flex items-center mt-8">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Branch Pricing</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($branches as $branch)
                    @php
                        $existing = optional($product->branches->firstWhere('id', $branch->id))->pivot->selling_price ?? '';
                    @endphp
                    <div>
                        <label class="form-label">{{ $branch->name }} price (₹)</label>
                        <input type="number" step="0.01" min="0" name="branch_prices[{{ $branch->id }}]" value="{{ old('branch_prices.'.$branch->id, $existing) }}" class="form-input">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('products.show', $product) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Product</button>
        </div>
    </form>
</div>
@endsection

