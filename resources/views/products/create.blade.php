@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="p-6">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>
                <p class="text-gray-600 mt-1">Create a product and configure branch pricing and suppliers.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn-secondary">Back to Products</a>
        </div>
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

    <form method="POST" action="{{ route('products.store') }}" class="space-y-8">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Code *</label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-input" placeholder="e.g., PRD001" required>
                </div>
                <div>
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-input" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category')===$cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-input" placeholder="Short description...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Pricing & Stock</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="form-label">Weight Unit *</label>
                    <select name="weight_unit" class="form-input" required>
                        <option value="">Select Unit</option>
                        @foreach($weight_units as $unit)
                            <option value="{{ $unit }}" {{ old('weight_unit')===$unit ? 'selected' : '' }}>{{ strtoupper($unit) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Purchase Price (₹) *</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price') }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">MRP (₹) *</label>
                    <input type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp') }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Default Selling Price (₹) *</label>
                    <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price') }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Low Stock Threshold *</label>
                    <input type="number" min="0" name="stock_threshold" value="{{ old('stock_threshold', 1) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Shelf Life (days)</label>
                    <input type="number" min="0" name="shelf_life_days" value="{{ old('shelf_life_days') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Storage Temperature</label>
                    <input type="text" name="storage_temperature" value="{{ old('storage_temperature') }}" class="form-input" placeholder="e.g., 2-8°C">
                </div>
                <div class="flex items-center mt-8">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_perishable" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded" {{ old('is_perishable') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Perishable</span>
                    </label>
                </div>
                <div class="flex items-center mt-8">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Branch Pricing</h2>
                <p class="text-sm text-gray-500">Leave blank to use default selling price</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($branches as $branch)
                    <div>
                        <label class="form-label">{{ $branch->name }} price (₹)</label>
                        <input type="number" step="0.01" min="0" name="branch_prices[{{ $branch->id }}]" value="{{ old('branch_prices.'.$branch->id) }}" class="form-input">
                    </div>
                @endforeach
            </div>
        </div>

        @if($vendors->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Vendor Supplies</h2>
                <button type="button" id="add-vendor" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Add Vendor</button>
            </div>
            <div id="vendor-container" class="space-y-4">
                <!-- Rows injected dynamically -->
            </div>
            <template id="vendor-row-template">
                <div class="border border-gray-200 rounded-lg p-4 vendor-row">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="form-label">Vendor</label>
                            <select name="vendor_supplies[INDEX][vendor_id]" class="form-input">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Supply Price (₹)</label>
                            <input type="number" step="0.01" min="0" name="vendor_supplies[INDEX][supply_price]" class="form-input">
                        </div>
                        <div class="flex items-center h-10 mt-6">
                            <input type="checkbox" name="vendor_supplies[INDEX][is_primary_supplier]" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Primary supplier</span>
                        </div>
                        <div>
                            <button type="button" class="remove-vendor w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg">Remove</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        @endif

        <div class="flex justify-end gap-4">
            <a href="{{ route('products.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Product</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addVendorBtn = document.getElementById('add-vendor');
    if (!addVendorBtn) return;
    const container = document.getElementById('vendor-container');
    const template = document.getElementById('vendor-row-template');
    let index = 0;

    addVendorBtn.addEventListener('click', function () {
        const clone = template.content.cloneNode(true);
        const html = document.createElement('div');
        html.appendChild(clone);
        html.innerHTML = html.innerHTML.replaceAll('INDEX', index);
        const node = html.firstElementChild;
        container.appendChild(node);
        node.querySelector('.remove-vendor').addEventListener('click', function () {
            node.remove();
        });
        index++;
    });
});
</script>
@endsection

