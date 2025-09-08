@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-gray-800">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Order {{ $order->order_number }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->orderItems as $item)
                            <tr>
                                <td class="px-4 py-2">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-900">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-2 text-right text-sm font-medium text-gray-900">₹{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6 space-y-3">
            <h2 class="text-xl font-semibold text-gray-900">Summary</h2>
            <div class="text-sm text-gray-700">Branch: <span class="font-medium">{{ $order->branch->name }}</span></div>
            <div class="text-sm text-gray-700">Customer: <span class="font-medium">{{ optional($order->customer)->name ?? 'Walk-in Customer' }}</span></div>
            <div class="text-sm text-gray-700">Status: <span class="font-medium">{{ ucfirst($order->status) }}</span></div>
            <div class="border-t pt-3 mt-2 space-y-1">
                <div class="flex justify-between text-sm"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-sm"><span>Discount</span><span>- ₹{{ number_format($order->discount_amount, 2) }}</span></div>
                <div class="flex justify-between text-sm"><span>Tax</span><span>₹{{ number_format($order->tax_amount, 2) }}</span></div>
                <div class="flex justify-between text-base font-semibold border-t pt-2"><span>Total</span><span>₹{{ number_format($order->total_amount, 2) }}</span></div>
            </div>
            <div class="flex gap-2 pt-2">
                <a href="{{ route('orders.invoice', $order) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Invoice</a>
                <a href="{{ route('orders.edit', $order) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded">Edit</a>
            </div>
        </div>
    </div>
</div>
@endsection

