@extends('layouts.app')

@section('title', 'Vendor Details - ' . $vendor->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('vendors.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $vendor->name }}</h1>
                        <p class="text-gray-600">Vendor Code: {{ $vendor->code }}</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('vendors.analytics', $vendor) }}" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Analytics
                        </a>
                        <a href="{{ route('purchase-orders.create') }}?vendor={{ $vendor->id }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Purchase Order
                        </a>
                        <a href="{{ route('vendors.edit', $vendor) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Vendor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Information & Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Vendor Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Vendor Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Contact Email</label>
                            <p class="text-gray-900">{{ $vendor->email }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone Number</label>
                            <p class="text-gray-900">{{ $vendor->phone }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $vendor->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        @if($vendor->gst_number)
                        <div>
                            <label class="text-sm font-medium text-gray-500">GST Number</label>
                            <p class="text-gray-900">{{ $vendor->gst_number }}</p>
                        </div>
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Address</label>
                        <p class="text-gray-900">{{ $vendor->address }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="space-y-6">
            <!-- Key Metrics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Key Metrics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Purchase Orders</span>
                        <span class="text-xl font-bold text-gray-900">{{ $stats['order_count'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Purchase Value</span>
                        <span class="text-xl font-bold text-green-600">₹{{ number_format($stats['total_purchases'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Average Order Value</span>
                        <span class="text-xl font-bold text-blue-600">₹{{ number_format($stats['avg_order_value'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Credit Balance</span>
                        <span class="text-xl font-bold {{ $stats['credit_balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ₹{{ number_format(abs($stats['credit_balance']), 2) }}
                            @if($stats['credit_balance'] < 0) (Due) @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">On-time Delivery Rate</span>
                            <span class="font-medium">
                                {{ $performance['total_deliveries'] > 0 ? round(($performance['on_time_deliveries'] / $performance['total_deliveries']) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $performance['total_deliveries'] > 0 ? ($performance['on_time_deliveries'] / $performance['total_deliveries']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">This Month Orders</span>
                        <span class="font-bold text-gray-900">{{ $performance['this_month_orders'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">This Month Value</span>
                        <span class="font-bold text-green-600">₹{{ number_format($performance['this_month_value'], 2) }}</span>
                    </div>
                    @if($stats['last_order_date'])
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Last Order</span>
                        <span class="font-medium text-gray-900">{{ $stats['last_order_date']->diffForHumans() }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('vendors.credit-management', $vendor) }}" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-3 px-4 rounded-lg text-center transition-colors block">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        Manage Credit
                    </a>
                    <a href="{{ route('purchase-orders.index') }}?vendor_id={{ $vendor->id }}" class="w-full bg-green-50 hover:bg-green-100 text-green-700 font-medium py-3 px-4 rounded-lg text-center transition-colors block">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View All Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Supplied -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Products Supplied</h2>
        
        @if($vendor->products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($vendor->products as $product)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium category-{{ $product->category }}">
                                {{ ucfirst($product->category) }}
                            </span>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Supply Price:</span>
                                <span class="font-medium">₹{{ number_format($product->pivot->supply_price, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Primary Supplier:</span>
                                <span class="font-medium {{ $product->pivot->is_primary_supplier ? 'text-green-600' : 'text-gray-600' }}">
                                    {{ $product->pivot->is_primary_supplier ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No products assigned</h3>
                <p class="mt-1 text-sm text-gray-500">This vendor hasn't been assigned any products yet.</p>
                <div class="mt-6">
                    <a href="{{ route('vendors.edit', $vendor) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Assign Products
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Purchase Orders -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Recent Purchase Orders</h2>
            <a href="{{ route('purchase-orders.index') }}?vendor_id={{ $vendor->id }}" class="text-blue-600 hover:text-blue-800 font-medium">
                View All Orders →
            </a>
        </div>

        @if($vendor->purchaseOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Expected Delivery</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendor->purchaseOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ $order->po_number }}
                                    </a>
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium status-{{ $order->status }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="font-medium">₹{{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ $order->expected_delivery_date ? $order->expected_delivery_date->format('M d, Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No purchase orders</h3>
                <p class="mt-1 text-sm text-gray-500">No purchase orders have been created for this vendor yet.</p>
                <div class="mt-6">
                    <a href="{{ route('purchase-orders.create') }}?vendor={{ $vendor->id }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        Create First Purchase Order
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Credit Transactions -->
    @if($vendor->creditTransactions->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Recent Credit Transactions</h2>
            <a href="{{ route('vendors.credit-management', $vendor) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                View All Transactions →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendor->creditTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction->type === 'credit_received' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $transaction->type === 'credit_received' ? 'Credit Received' : 'Credit Paid' }}
                                </span>
                            </td>
                            <td class="font-medium {{ $transaction->type === 'credit_received' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'credit_received' ? '+' : '-' }}₹{{ number_format($transaction->amount, 2) }}
                            </td>
                            <td>{{ $transaction->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection