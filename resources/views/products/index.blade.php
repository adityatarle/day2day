@extends('layouts.app')

@section('title', 'Product Management')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
                <p class="text-gray-600 mt-1">Manage your product catalog, pricing, and inventory across all branches.</p>
            </div>
            <div class="flex items-center space-x-3">
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <a href="{{ route('products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Product
                </a>
                @else
                <div class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Branch View (Read Only)
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Product Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $products->total() }}</p>
                </div>
            </div>
        </div>

        @foreach($categories as $cat)
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-lg
                    {{ $cat === 'fruit' ? 'bg-orange-100' : 
                       ($cat === 'vegetable' ? 'bg-green-100' : 
                       ($cat === 'leafy' ? 'bg-emerald-100' : 'bg-purple-100')) }}">
                    <svg class="w-6 h-6 
                        {{ $cat === 'fruit' ? 'text-orange-600' : 
                           ($cat === 'vegetable' ? 'text-green-600' : 
                           ($cat === 'leafy' ? 'text-emerald-600' : 'text-purple-600')) }}" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ ucfirst($cat) }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $products->where('category', $cat)->count() }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label">Search Products</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-input pl-10" placeholder="Search by name, code, or description">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="form-label">Category</label>
                    <select name="category" class="form-input">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-input">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn-primary flex-1">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('products.index') }}" class="btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover-lift">
                <!-- Product Header -->
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center
                                {{ $product->category === 'fruit' ? 'bg-orange-100' : 
                                   ($product->category === 'vegetable' ? 'bg-green-100' : 
                                   ($product->category === 'leafy' ? 'bg-emerald-100' : 'bg-purple-100')) }}">
                                <span class="text-lg font-bold
                                    {{ $product->category === 'fruit' ? 'text-orange-600' : 
                                       ($product->category === 'vegetable' ? 'text-green-600' : 
                                       ($product->category === 'leafy' ? 'text-emerald-600' : 'text-purple-600')) }}">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $product->code }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            {{ $product->category === 'fruit' ? 'bg-orange-100 text-orange-800' : 
                               ($product->category === 'vegetable' ? 'bg-green-100 text-green-800' : 
                               ($product->category === 'leafy' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800')) }}">
                            {{ ucfirst($product->category) }}
                        </span>
                    </div>
                    
                    @if($product->description)
                    <p class="text-sm text-gray-600 mb-4">{{ Str::limit($product->description, 80) }}</p>
                    @endif
                    
                    <!-- Pricing Information -->
                    <div class="space-y-2 mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Purchase:</span>
                            <span class="font-medium">₹{{ number_format($product->purchase_price, 2) }}/{{ $product->weight_unit }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">MRP:</span>
                            <span class="font-medium">₹{{ number_format($product->mrp, 2) }}/{{ $product->weight_unit }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Selling:</span>
                            <span class="font-semibold text-green-600">₹{{ number_format($product->selling_price, 2) }}/{{ $product->weight_unit }}</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                            <span class="text-gray-600">Margin:</span>
                            <span class="font-semibold text-blue-600">
                                {{ round((($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100, 1) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Stock Information -->
                    <div class="mb-4">
                        @php
                            $totalStock = $product->branches->sum('pivot.current_stock');
                            $lowStockThreshold = $product->branches->min('pivot.stock_threshold') ?? 0;
                        @endphp
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Total Stock</span>
                            <span class="text-sm font-bold 
                                {{ $totalStock <= $lowStockThreshold ? 'text-red-600' : 'text-green-600' }}">
                                {{ $totalStock }}{{ $product->weight_unit }}
                            </span>
                        </div>
                        @if($totalStock <= $lowStockThreshold)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-2">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L5.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <span class="text-xs text-red-800 font-medium">Low Stock Alert</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Branch Stock Details -->
                    @if($product->branches->count() > 0)
                    <div class="mb-4">
                        <h5 class="text-xs font-medium text-gray-700 mb-2">Branch Stock:</h5>
                        <div class="space-y-1">
                            @foreach($product->branches->take(3) as $branch)
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">{{ $branch->name }}:</span>
                                <span class="font-medium {{ ($branch->pivot->current_stock ?? 0) <= ($branch->pivot->stock_threshold ?? 0) ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $branch->pivot->current_stock ?? 0 }}{{ $product->weight_unit }}
                                </span>
                            </div>
                            @endforeach
                            @if($product->branches->count() > 3)
                            <div class="text-xs text-gray-500">+{{ $product->branches->count() - 3 }} more branches</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Vendor Information -->
                    @if($product->vendors->count() > 0)
                    <div class="mb-4">
                        <h5 class="text-xs font-medium text-gray-700 mb-2">Suppliers:</h5>
                        <div class="flex flex-wrap gap-1">
                            @foreach($product->vendors->take(2) as $vendor)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                {{ $vendor->name }}
                            </span>
                            @endforeach
                            @if($product->vendors->count() > 2)
                            <span class="text-xs text-gray-500">+{{ $product->vendors->count() - 2 }} more</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Product Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('products.show', $product) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </a>
                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                            <a href="{{ route('products.edit', $product) }}" 
                               class="text-green-600 hover:text-green-800 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>
                            @endif
                        </div>
                        <div class="flex items-center">
                            @if($product->is_active)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <p class="text-gray-500 mb-6">Get started by adding your first product to the catalog.</p>
                    <a href="{{ route('products.create') }}" class="btn-primary">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Your First Product
                    </a>
                    @else
                    <p class="text-gray-500 mb-6">No products are available in the system yet. Please contact admin to add products.</p>
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Branch Manager View
                    </div>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection