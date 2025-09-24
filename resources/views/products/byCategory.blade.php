@extends('layouts.app')

@section('title', 'Products - '.ucfirst($category))

@section('content')
<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Category: {{ ucfirst($category) }}</h1>
        <a href="{{ route('products.index') }}" class="btn-secondary">Back to Products</a>
    </div>

    @if($products->isEmpty())
        <div class="bg-white p-12 rounded-xl shadow-sm border border-gray-200 text-center">
            <p class="text-gray-600 mb-4">No products found in this category.</p>
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <a href="{{ route('products.create') }}" class="btn-primary">Add Product</a>
            @else
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Branch Manager View
            </div>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $product->code }}</p>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                    </div>
                    <div class="mt-4 text-sm text-gray-700">
                        <div>Purchase: ₹{{ number_format($product->purchase_price, 2) }}</div>
                        <div>MRP: ₹{{ number_format($product->mrp, 2) }}</div>
                        <div>Selling: ₹{{ number_format($product->selling_price, 2) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection

