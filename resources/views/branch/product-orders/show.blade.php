@extends('layouts.app')

@section('title', 'Product Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('branch.product-orders.index') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Product Orders
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $productOrder->po_number }}</h1>
                        <p class="text-gray-600">Branch: {{ $productOrder->branch->name }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productOrder->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : ($productOrder->status === 'sent' ? 'bg-blue-100 text-blue-800' : ($productOrder->status === 'fulfilled' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                            {{ ucfirst($productOrder->status) }}
                        </span>
                        @if($productOrder->priority)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productOrder->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($productOrder->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($productOrder->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($productOrder->priority) }} Priority
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Requested By</div>
                        <div class="text-sm font-medium text-gray-900">{{ $productOrder->user->name }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Expected Delivery</div>
                        <div class="text-sm font-medium text-gray-900">{{ optional($productOrder->expected_delivery_date)->format('M d, Y') ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Status</div>
                        <div class="text-sm font-medium text-gray-900">{{ ucfirst($productOrder->status) }}</div>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Items</h2>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($productOrder->purchaseOrderItems as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->category }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $item->notes }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Order Status</h2>
                @if($productOrder->status === 'draft')
                    <div class="alert alert-info">Your order has been sent to admin. Admin will purchase materials and fulfill your request.</div>
                    <a href="{{ route('branch.product-orders.edit', $productOrder) }}" class="btn btn-primary w-full text-center">Edit Order</a>
                @elseif($productOrder->status === 'sent')
                    <div class="alert alert-success">Admin approved your order. Awaiting delivery.</div>
                    <div class="text-sm text-gray-600">Admin is processing your request and will purchase materials from vendors.</div>
                @elseif($productOrder->status === 'fulfilled')
                    <div class="alert alert-success">Order delivered by admin. Please record receipt in Purchase Entry if not done yet.</div>
                @elseif($productOrder->status === 'cancelled')
                    <div class="alert alert-error">Order was cancelled by admin.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

