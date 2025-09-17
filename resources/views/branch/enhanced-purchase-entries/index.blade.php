@extends('layouts.app')

@section('title', 'Enhanced Purchase Entries')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Purchase Entries & Tracking</h1>
            <p class="text-gray-600">Comprehensive tracking of received quantities against purchase orders with detailed remaining items</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('enhanced-purchase-entries.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create Entry
            </a>
            <a href="{{ route('enhanced-purchase-entries.report') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Detailed Report
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
                    <div class="h-12 w-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Complete Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['complete_orders'] }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Total Entries</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_entries'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <form method="GET" action="{{ route('enhanced-purchase-entries.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="form-label">Search Order Number</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           class="form-input" placeholder="e.g., BR-2024-001">
                </div>

                <div>
                    <label for="status" class="form-label">Order Status</label>
                    <select name="status" id="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="complete" {{ request('status') === 'complete' ? 'selected' : '' }}>Complete</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
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
                    <a href="{{ route('enhanced-purchase-entries.index') }}" class="btn-secondary">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Purchase Orders with Detailed Tracking -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($purchaseOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Vendor</th>
                            <th>Order Date</th>
                            <th>Expected Qty</th>
                            <th>Received Qty</th>
                            <th>Remaining Qty</th>
                            <th>Completion %</th>
                            <th>Receipts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td>
                                    <a href="{{ route('enhanced-purchase-entries.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{ $order->po_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-gray-900 font-medium">{{ $order->vendor ? $order->vendor->name : 'Admin' }}</span>
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="text-gray-900 font-medium">{{ number_format($order->total_expected, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-green-600 font-medium">{{ number_format($order->total_received, 2) }}</span>
                                </td>
                                <td>
                                    @if($order->total_remaining > 0)
                                        <span class="text-orange-600 font-medium">{{ number_format($order->total_remaining, 2) }}</span>
                                    @else
                                        <span class="text-green-600 font-medium">0</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(100, $order->completion_percentage) }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ number_format($order->completion_percentage, 1) }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-gray-900 font-medium">{{ $order->receipt_count }}</span>
                                    <span class="text-gray-500 text-sm">entries</span>
                                </td>
                                <td>
                                    @if($order->completion_percentage >= 100)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Complete
                                        </span>
                                    @elseif($order->completion_percentage > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            Partial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('enhanced-purchase-entries.show', $order) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Details
                                        </a>
                                        @if($order->completion_percentage < 100)
                                            <a href="{{ route('enhanced-purchase-entries.create') }}?order_id={{ $order->id }}" 
                                               class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                Add Entry
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
            @if($purchaseOrders->hasPages())
                <div class="p-6 border-t border-gray-200">
                    {{ $purchaseOrders->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No purchase orders found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status']))
                        No purchase orders match your current filters.
                    @else
                        No purchase orders found. Orders will appear here once they are created.
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status']))
                    <div class="mt-6">
                        <a href="{{ route('enhanced-purchase-entries.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                            Clear Filters
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection