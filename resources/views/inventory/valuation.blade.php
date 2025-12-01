@extends(auth()->user()->hasRole('cashier') ? 'layouts.cashier' : (auth()->user()->hasRole('branch_manager') ? 'layouts.branch-manager' : (auth()->user()->hasRole('super_admin') ? 'layouts.super-admin' : 'layouts.app')))

@section('title', 'Inventory Valuation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                @if(auth()->user()->hasRole('cashier') || auth()->user()->hasRole('branch_manager'))
                    Branch Inventory Valuation
                @else
                    Inventory Valuation
                @endif
            </h1>
            <p class="text-gray-600 mt-1">
                @if(auth()->user()->hasRole('cashier') || auth()->user()->hasRole('branch_manager'))
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-medium text-gray-700">{{ auth()->user()->branch->name ?? 'N/A' }}</span>
                        <span class="mx-2">•</span>
                        <span class="text-gray-600">Total value of your branch inventory</span>
                    </span>
                @else
                    Total value based on current stock and selling price
                @endif
            </p>
        </div>
        @if(auth()->user()->hasRole('branch_manager') || auth()->user()->hasRole('cashier'))
            <a href="{{ route('cashier.inventory.view') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
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
                        @if(!auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('branch_manager'))
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branches</th>
                        @endif
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
                            @if(!auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('branch_manager'))
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product->branches->pluck('name')->join(', ') }}</div>
                            </td>
                            @endif
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

