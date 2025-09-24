@extends('layouts.app')

@section('title', 'Product Orders')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Product Orders</h1>
            <p class="text-gray-600">Request products from admin - admin will purchase materials from vendors</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('branch.product-orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Order Products
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Draft</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Sent</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['approved_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11m-6 0a2 2 0 012-2h4a2 2 0 012 2m-6 0h6" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">This Month</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['this_month_orders'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <form method="GET" action="{{ route('branch.product-orders.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="form-label">Search Order Number</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           class="form-input" placeholder="e.g., BR-2024-001">
                </div>

                <div>
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full btn-primary">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Filter
                    </button>
                </div>
            </div>

            @if(request()->hasAny(['search', 'status']))
                <div class="flex justify-end">
                    <a href="{{ route('branch.product-orders.index') }}" class="btn-secondary">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Product Orders Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
 cursor/fetch-product-orders-from-local-server-fa27
        @if($productOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Items</th>
                            <th>Expected Delivery</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td>
                                    <a href="{{ route('branch.product-orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{ $order->po_number }}
                                    </a>
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $order->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($order->status === 'sent' ? 'bg-blue-100 text-blue-800' : 
                                           ($order->status === 'fulfilled' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $order->priority === 'urgent' ? 'bg-red-100 text-red-800' : 
                                           ($order->priority === 'high' ? 'bg-orange-100 text-orange-800' : 
                                           ($order->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($order->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-gray-900 font-medium">{{ $order->purchase_order_items_count }}</span>
                                    <span class="text-gray-500 text-sm">items</span>
                                </td>
                                <td>
                                    @if($order->expected_delivery_date)
                                        <span class="text-gray-900">{{ $order->expected_delivery_date->format('M d, Y') }}</span>
                                        @if($order->expected_delivery_date->isPast() && $order->status !== 'fulfilled')
                                            <span class="block text-xs text-red-600 font-medium">Overdue</span>
                                        @endif
                                    @else
                                        <span class="text-gray-500">-</span>

    @if($productOrders->count() > 0)
        <div class="overflow-x-auto -webkit-overflow-scrolling-touch">
            <table class="min-w-full table-auto border-collapse" style="min-width: 600px;">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap">Order Number</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap">Date</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap">Status</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap">Priority</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap">Items</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap">Expected Delivery</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($productOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <a href="{{ route('branch.product-orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-xs sm:text-sm">
                                    {{ $order->po_number }}
                                </a>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm whitespace-nowrap">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-medium 
                                    {{ $order->status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                       ($order->status === 'sent' ? 'bg-blue-100 text-blue-800' :
                                       ($order->status === 'fulfilled' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-medium 
                                    {{ $order->priority === 'urgent' ? 'bg-red-100 text-red-800' :
                                       ($order->priority === 'high' ? 'bg-orange-100 text-orange-800' :
                                       ($order->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($order->priority) }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="text-gray-900 font-medium text-xs sm:text-sm">{{ $order->purchase_order_items_count }}</span>
                                <span class="text-gray-500 text-[10px] sm:text-sm">items</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                @if($order->expected_delivery_date)
                                    <span class="text-gray-900 text-xs sm:text-sm">{{ $order->expected_delivery_date->format('M d, Y') }}</span>
                                    @if($order->expected_delivery_date->isPast() && $order->status !== 'fulfilled')
                                        <span class="block text-[10px] sm:text-xs text-red-600 font-medium">Overdue</span>
main
                                    @endif
                                @else
                                    <span class="text-gray-500 text-xs sm:text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                                    <a href="{{ route('branch.product-orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-xs sm:text-sm font-medium">
                                        View
                                    </a>
                                    @if($order->status === 'draft')
                                        <a href="{{ route('branch.product-orders.edit', $order) }}" class="text-green-600 hover:text-green-800 text-xs sm:text-sm font-medium">
                                            Edit
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
        @if($productOrders->hasPages())
            <div class="p-4 sm:p-6 border-t border-gray-200">
                {{ $productOrders->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-8 sm:py-12">
            <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No product orders found</h3>
            <p class="mt-1 text-xs sm:text-sm text-gray-500">
                @if(request()->hasAny(['search', 'status']))
                    No product orders match your current filters.
                @else
                    Get started by ordering your first products from admin.
                @endif
            </p>
            <div class="mt-4 sm:mt-6">
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('branch.product-orders.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 border border-transparent shadow-sm text-xs sm:text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                        Clear Filters
                    </a>
                @else
                    <a href="{{ route('branch.product-orders.create') }}" class="inline-flex items-center px-3 sm:px-4 py-2 border border-transparent shadow-sm text-xs sm:text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Order Products
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>

</div>
@endsection