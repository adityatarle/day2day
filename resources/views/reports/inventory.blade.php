@extends('layouts.app')

@section('title', 'Inventory Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Inventory Report</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Products with Stock</p>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format(($products ?? collect())->count()) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Low Stock Products</p>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format(($lowStockProducts ?? collect())->count()) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Estimated Inventory Value</p>
            <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($totalValue ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="table-container overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Branch</th>
                    <th>Stock</th>
                    <th>Selling Price</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($products ?? collect()) as $product)
                    @foreach(($product->branches ?? collect()) as $branch)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $branch->name }}</td>
                        <td>{{ $branch->pivot->current_stock }}</td>
                        <td>₹{{ number_format($branch->pivot->selling_price, 2) }}</td>
                        <td>₹{{ number_format($branch->pivot->current_stock * $branch->pivot->selling_price, 2) }}</td>
                    </tr>
                    @endforeach
                @empty
                <tr>
                    <td colspan="5" class="text-center text-gray-500 p-4">No inventory data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

