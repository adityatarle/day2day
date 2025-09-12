@extends('layouts.app')

@section('title', 'Inventory Valuation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Inventory Valuation</h1>
            <p class="text-gray-600 mt-1">Total value based on current stock and selling price</p>
        </div>
        @if(auth()->user()->hasRole('branch_manager') || auth()->user()->hasRole('cashier'))
            <a href="{{ route('branch.inventory.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
            </a>
        @else
            <a href="{{ route('inventory.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-600">Total Inventory Value</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($totalValue, 2) }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-600">Products In Stock</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">{{ $products->count() }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-600">Average Value / Product</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($products->count() ? $totalValue / $products->count() : 0, 2) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Product-wise Valuation</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branches</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                        @php
                            $totalStock = $product->branches->sum(fn($b) => $b->pivot->current_stock);
                            $value = $product->branches->sum(fn($b) => $b->pivot->current_stock * ($b->pivot->selling_price ?? $product->selling_price));
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst($product->category) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product->branches->pluck('name')->join(', ') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($totalStock, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₹{{ number_format($product->selling_price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">₹{{ number_format($value, 2) }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

