@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Analytics</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Top Selling Products</h2>
            <ul class="divide-y">
                @forelse(($topProducts ?? []) as $product)
                <li class="py-2 flex justify-between">
                    <span>{{ $product->name }}</span>
                    <span class="font-semibold">{{ number_format($product->total_sold) }}</span>
                </li>
                @empty
                <li class="py-2 text-gray-500">No data</li>
                @endforelse
            </ul>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Sales by Category</h2>
            <ul class="divide-y">
                @forelse(($salesByCategory ?? []) as $item)
                <li class="py-2 flex justify-between">
                    <span>{{ $item->category }}</span>
                    <span class="font-semibold">{{ number_format($item->total_sold) }}</span>
                </li>
                @empty
                <li class="py-2 text-gray-500">No data</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

