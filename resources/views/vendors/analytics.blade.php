@extends('layouts.app')

@section('title', 'Vendor Analytics - ' . $vendor->name)

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
                <h1 class="text-3xl font-bold text-gray-900">{{ $vendor->name }} Analytics</h1>
                <p class="text-gray-600">Performance metrics and purchase analysis</p>
            </div>
        </div>
    </div>

    <!-- Payment Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Paid</p>
                    <p class="text-2xl font-semibold text-green-600">₹{{ number_format($paymentStats['total_paid'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Credit Received</p>
                    <p class="text-2xl font-semibold text-blue-600">₹{{ number_format($paymentStats['total_received'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Payments</p>
                    <p class="text-2xl font-semibold text-orange-600">₹{{ number_format($paymentStats['pending_payments'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Purchase Trend -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Monthly Purchase Trend</h2>
        
        @if($monthlyData->count() > 0)
            <div class="overflow-x-auto">
                <div class="min-w-full">
                    <!-- Simple bar chart representation -->
                    <div class="space-y-4">
                        @foreach($monthlyData as $data)
                            @php
                                $maxValue = $monthlyData->max('total');
                                $percentage = $maxValue > 0 ? ($data->total / $maxValue) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-4">
                                <div class="w-20 text-sm text-gray-600">
                                    {{ date('M Y', mktime(0, 0, 0, $data->month, 1, $data->year)) }}
                                </div>
                                <div class="flex-1 bg-gray-200 rounded-full h-8 relative">
                                    <div class="bg-blue-600 h-8 rounded-full flex items-center justify-end pr-2" style="width: {{ $percentage }}%">
                                        <span class="text-white text-sm font-medium">₹{{ number_format($data->total / 1000, 1) }}K</span>
                                    </div>
                                </div>
                                <div class="w-16 text-sm text-gray-600 text-right">
                                    {{ $data->count }} orders
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No purchase data available</p>
            </div>
        @endif
    </div>

    <!-- Product-wise Purchase Analysis -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Product-wise Purchase Analysis</h2>
        
        @if($productAnalysis->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Total Quantity</th>
                            <th>Total Value</th>
                            <th>Average Price</th>
                            <th>Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productAnalysis as $product)
                            <tr>
                                <td class="font-medium">{{ $product->name }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium category-{{ $product->category }}">
                                        {{ ucfirst($product->category) }}
                                    </span>
                                </td>
                                <td>{{ number_format($product->total_quantity, 2) }} kg</td>
                                <td class="font-medium text-green-600">₹{{ number_format($product->total_value, 2) }}</td>
                                <td>₹{{ number_format($product->avg_price, 2) }}</td>
                                <td>{{ $product->order_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No product purchase data available</p>
            </div>
        @endif
    </div>
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
        const template = productTemplate.innerHTML;
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