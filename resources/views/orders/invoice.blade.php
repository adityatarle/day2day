@extends('layouts.app')

@section('title', 'Invoice ' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto p-6 print:p-0">
    <div class="bg-white shadow-sm border rounded-lg p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tax Invoice</h1>
                <p class="text-gray-600">Invoice #: {{ $order->order_number }}</p>
                <p class="text-gray-600">Date: {{ optional($order->order_date ?? $order->created_at)->format('d M Y, h:i A') }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-lg font-semibold text-gray-900">{{ $order->branch->name }}</h2>
                <p class="text-gray-600">{{ $order->branch->address }}</p>
                @if($order->branch->phone)
                    <p class="text-gray-600">Phone: {{ $order->branch->phone }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Bill To</h3>
                <p class="text-gray-900 font-medium">{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                @if(optional($order->customer)->phone)
                    <p class="text-gray-600">{{ $order->customer->phone }}</p>
                @endif
                @if(optional($order->customer)->address)
                    <p class="text-gray-600">{{ $order->customer->address }}</p>
                @endif
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Order Info</h3>
                <p class="text-gray-900"><span class="font-medium">Order #:</span> {{ $order->order_number }}</p>
                <p class="text-gray-900"><span class="font-medium">Payment Method:</span> {{ ucfirst($order->payment_method) }}</p>
                <p class="text-gray-900"><span class="font-medium">Payment Status:</span> {{ ucfirst($order->payment_status ?? 'pending') }}</p>
            </div>
        </div>

        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Notes</h3>
                <p class="text-gray-600 text-sm">Thank you for shopping with us!</p>
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <div class="flex justify-between text-sm mb-2">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span>Discount</span>
                    <span>- ₹{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span>Tax</span>
                    <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="border-t pt-2 mt-2 flex justify-between text-base font-semibold">
                    <span>Total</span>
                    <span>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>

                @php
                    $paid = $order->payments()->sum('amount');
                    $balance = max($order->total_amount - $paid, 0);
                @endphp

                <div class="mt-2 flex justify-between text-sm">
                    <span>Paid</span>
                    <span>₹{{ number_format($paid, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Balance</span>
                    <span>₹{{ number_format($balance, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-2 print:hidden">
            <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded">Back</a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Print</button>
        </div>
    </div>
</div>
@endsection

<style>
@media print {
    .print\:p-0 { padding: 0 !important; }
    .print\:hidden { display: none !important; }
}
</style>

