@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
            <p class="text-gray-600">Code: {{ $product->code }} • {{ ucfirst($product->category) }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('products.edit', $product) }}" class="btn-primary">Edit</a>
            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Delete</button>
            </form>
            <a href="{{ route('products.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-600">Weight Unit:</span> <span class="font-medium">{{ strtoupper($product->weight_unit) }}</span></div>
                <div><span class="text-gray-600">Purchase Price:</span> <span class="font-medium">₹{{ number_format($product->purchase_price, 2) }}</span></div>
                <div><span class="text-gray-600">MRP:</span> <span class="font-medium">₹{{ number_format($product->mrp, 2) }}</span></div>
                <div><span class="text-gray-600">Selling Price:</span> <span class="font-medium">₹{{ number_format($product->selling_price, 2) }}</span></div>
                <div><span class="text-gray-600">Low Stock Threshold:</span> <span class="font-medium">{{ $product->stock_threshold }}</span></div>
                <div><span class="text-gray-600">Perishable:</span> <span class="font-medium">{{ $product->is_perishable ? 'Yes' : 'No' }}</span></div>
                <div><span class="text-gray-600">Status:</span> <span class="font-medium">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></div>
                @if($product->shelf_life_days)
                <div><span class="text-gray-600">Shelf Life:</span> <span class="font-medium">{{ $product->shelf_life_days }} days</span></div>
                @endif
                @if($product->storage_temperature)
                <div><span class="text-gray-600">Storage Temp:</span> <span class="font-medium">{{ $product->storage_temperature }}</span></div>
                @endif
            </div>
            @if($product->description)
                <div class="mt-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Description</h3>
                    <p class="text-gray-700">{{ $product->description }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Stock Summary</h2>
            @php $totalStock = $product->branches->sum('pivot.current_stock'); @endphp
            <div class="text-sm text-gray-700">Total stock across branches: <span class="font-semibold">{{ $totalStock }}{{ $product->weight_unit }}</span></div>
            <div class="mt-4 space-y-2">
                @forelse($product->branches as $branch)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $branch->name }}</span>
                        <span class="font-medium">{{ $branch->pivot->current_stock }} • ₹{{ number_format($branch->pivot->selling_price, 2) }}</span>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">No branch pricing configured.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Vendors</h2>
        <div class="space-y-2">
            @forelse($product->vendors as $vendor)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700">{{ $vendor->name }}</span>
                    <span class="text-gray-600">₹{{ number_format($vendor->pivot->supply_price, 2) }} {{ $vendor->pivot->is_primary_supplier ? '• Primary' : '' }}</span>
                </div>
            @empty
                <div class="text-sm text-gray-500">No vendors linked.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

