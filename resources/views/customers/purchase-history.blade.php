@extends('layouts.app')

@section('title', 'Purchase History - ' . $customer->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('customers.show', $customer) }}" class="text-gray-600 hover:text-gray-800 transition-colors">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Purchase History</h1>
    </div>

    <!-- Customer Info Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center space-x-4">
            <div class="h-12 w-12 rounded-full bg-blue-600 flex items-center justify-center">
                <span class="text-white font-bold text-lg">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h2>
                <p class="text-gray-500">{{ $customer->email ?: $customer->phone }}</p>
            </div>
        </div>
    </div>

    <!-- Purchase History -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Order History</h3>
        </div>

        @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $order->id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->branch->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->orderItems->count() }} items</div>
                                @if($order->products->count() > 0)
                                    <div class="text-xs text-gray-500">
                                        {{ $order->products->take(2)->pluck('name')->join(', ') }}
                                        @if($order->products->count() > 2)
                                            <span class="text-gray-400">+{{ $order->products->count() - 2 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($order->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                       ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-gray-900">₹{{ number_format($order->total_amount, 2) }}</div>
                                @if($order->discount_amount > 0)
                                    <div class="text-xs text-green-600">Saved ₹{{ number_format($order->discount_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        View
                                    </a>
                                    @if($order->status !== 'cancelled')
                                        <a href="{{ route('orders.invoice', $order) }}" class="text-green-600 hover:text-green-900 text-sm font-medium">
                                            Invoice
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @endif

            <!-- Summary Footer -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">
                        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                    </span>
                    <div class="flex space-x-6">
                        <span class="text-gray-600">
                            Total Spent: <span class="font-semibold text-gray-900">₹{{ number_format($orders->sum('total_amount'), 2) }}</span>
                        </span>
                        <span class="text-gray-600">
                            Average Order: <span class="font-semibold text-gray-900">₹{{ $orders->count() > 0 ? number_format($orders->sum('total_amount') / $orders->count(), 2) : '0.00' }}</span>
                        </span>
                    </div>
                </div>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-shopping-cart text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Purchase History</h3>
                <p class="text-gray-500 mb-6">This customer hasn't placed any orders yet.</p>
                <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Create First Order
                </a>
            </div>
        @endif
    </div>
</div>
@endsection