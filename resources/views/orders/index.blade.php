@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6 lg:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Orders</h1>
        <a href="{{ route('orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg touch-target text-sm sm:text-base mobile-full-width sm:w-auto text-center">
            <i class="fas fa-plus mr-1 sm:mr-2"></i>
            <span class="hidden sm:inline">Create New Order</span>
            <span class="sm:hidden">New Order</span>
        </a>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       class="form-input touch-target text-sm"
                       placeholder="Order number or customer name">
            </div>
            <div>
                <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Status</label>
                <select name="status" id="status" class="form-input touch-target text-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="branch_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Branch</label>
                <select name="branch_id" id="branch_id" class="form-input touch-target text-sm">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end space-x-2 sm:col-span-2 lg:col-span-1">
                <button type="submit" class="btn-primary flex-1 text-sm touch-target">
                    <i class="fas fa-search mr-1 sm:mr-2"></i>
                    Filter
                </button>
                <a href="{{ route('orders.index') }}" class="btn-secondary text-sm touch-target">Clear</a>
            </div>
        </form>
    </div>

    <!-- Mobile Card Layout (Hidden on larger screens) -->
    <div class="lg:hidden space-y-3 sm:space-y-4">
        @forelse($orders as $order)
            <div class="mobile-table-card">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-receipt text-blue-600"></i>
                        </div>
                        <div>
                            <div class="mobile-table-title">{{ $order->order_number }}</div>
                            <div class="mobile-table-subtitle">Order #{{ $order->id }}</div>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                        @if($order->status == 'completed') bg-green-100 text-green-800
                        @elseif($order->status == 'processing') bg-yellow-100 text-yellow-800
                        @elseif($order->status == 'pending') bg-blue-100 text-blue-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                
                <div class="space-y-2">
                    <div class="mobile-table-row">
                        <span class="mobile-table-label">Customer</span>
                        <div class="text-right">
                            <div class="mobile-table-value">{{ $order->customer->name }}</div>
                            <div class="text-xs text-gray-500">{{ $order->customer->phone }}</div>
                        </div>
                    </div>
                    <div class="mobile-table-row">
                        <span class="mobile-table-label">Branch</span>
                        <span class="mobile-table-value">{{ $order->branch->name }}</span>
                    </div>
                    <div class="mobile-table-row">
                        <span class="mobile-table-label">Total</span>
                        <span class="mobile-table-value font-bold text-green-600">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="mobile-table-row">
                        <span class="mobile-table-label">Date</span>
                        <div class="text-right">
                            <div class="mobile-table-value">{{ $order->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-2 mt-4 pt-3 border-t border-gray-100">
                    <a href="{{ route('orders.show', $order) }}" class="flex-1 bg-blue-50 text-blue-700 text-center py-2 px-3 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors touch-target">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <a href="{{ route('orders.edit', $order) }}" class="flex-1 bg-green-50 text-green-700 text-center py-2 px-3 rounded-lg text-sm font-medium hover:bg-green-100 transition-colors touch-target">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <a href="{{ route('orders.invoice', $order) }}" class="flex-1 bg-purple-50 text-purple-700 text-center py-2 px-3 rounded-lg text-sm font-medium hover:bg-purple-100 transition-colors touch-target">
                        <i class="fas fa-file-invoice mr-1"></i>Invoice
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
                <p class="text-sm text-gray-500 mb-6">Get started by creating a new order.</p>
                <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors touch-target">
                    <i class="fas fa-plus mr-2"></i>Create Order
                </a>
            </div>
        @endforelse
    </div>

    <!-- Desktop Table Layout (Hidden on mobile) -->
    <div class="hidden lg:block bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden">
        <div class="table-container">
            <table class="data-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                                <div class="text-sm text-gray-500">{{ $order->id }}</div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</div>
                                <div class="text-sm text-gray-500">{{ $order->customer->phone }}</div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->branch->name }}</div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($order->status == 'completed') bg-green-100 text-green-800
                                    @elseif($order->status == 'processing') bg-yellow-100 text-yellow-800
                                    @elseif($order->status == 'pending') bg-blue-100 text-blue-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">₹{{ number_format($order->total_amount, 2) }}</div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $order->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 transition-colors">View</a>
                                <a href="{{ route('orders.edit', $order) }}" class="text-green-600 hover:text-green-900 transition-colors">Edit</a>
                                <a href="{{ route('orders.invoice', $order) }}" class="text-purple-600 hover:text-purple-900 transition-colors">Invoice</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new order.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                            Create Order
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection