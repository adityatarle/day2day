@extends('layouts.app')

@section('title', 'Purchase Orders Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Purchase Orders Dashboard</h1>
            <p class="text-gray-600">Overview of outgoing Purchase Orders and incoming Received Orders</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('purchase-orders.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                View All Orders
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create Purchase Order
            </a>
        </div>
    </div>

    <!-- Status Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                <p class="text-sm text-gray-600">Total Orders</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-600">{{ $stats['draft_orders'] }}</p>
                <p class="text-sm text-gray-600">Draft</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['sent_orders'] }}</p>
                <p class="text-sm text-gray-600">Sent</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="h-12 w-12 rounded-lg bg-orange-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-orange-600">{{ $stats['confirmed_orders'] }}</p>
                <p class="text-sm text-gray-600">Confirmed</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ $stats['received_orders'] }}</p>
                <p class="text-sm text-gray-600">Received Orders</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="h-12 w-12 rounded-lg bg-red-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled_orders'] }}</p>
                <p class="text-sm text-gray-600">Cancelled</p>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Financial Overview</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Purchase Value</span>
                    <span class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['total_value'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">This Month Value</span>
                    <span class="text-xl font-semibold text-green-600">₹{{ number_format($stats['this_month_value'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Average Order Value</span>
                    <span class="text-lg font-medium text-blue-600">
                        ₹{{ $stats['total_orders'] > 0 ? number_format($stats['total_value'] / $stats['total_orders'], 2) : '0.00' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="{{ route('purchase-orders.create') }}" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-3 px-4 rounded-lg text-center transition-colors block">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create New Purchase Order
                </a>
                <a href="{{ route('vendors.index') }}" class="w-full bg-green-50 hover:bg-green-100 text-green-700 font-medium py-3 px-4 rounded-lg text-center transition-colors block">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Manage Vendors
                </a>
                <a href="{{ route('purchase-orders.index') }}?status=confirmed" class="w-full bg-orange-50 hover:bg-orange-100 text-orange-700 font-medium py-3 px-4 rounded-lg text-center transition-colors block">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    Pending Deliveries
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Orders & Top Vendors -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Recent Purchase Orders</h2>
                <a href="{{ route('purchase-orders.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    View All →
                </a>
            </div>

            @if($recentOrders->count() > 0)
                <div class="space-y-4">
                    @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('purchase-orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{ $order->po_number }}
                                    </a>
                                    <p class="text-sm text-gray-600">{{ $order->vendor->name }} • {{ $order->branch->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium status-{{ $order->status }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <p class="text-sm font-semibold text-gray-900 mt-1">₹{{ number_format($order->total_amount, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No recent orders</p>
                </div>
            @endif
        </div>

        <!-- Top Vendors -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Top Vendors by Value</h2>
                <a href="{{ route('vendors.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    View All →
                </a>
            </div>

            @if($topVendors->count() > 0)
                <div class="space-y-4">
                    @foreach($topVendors as $vendor)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                                        <span class="text-green-600 font-bold text-sm">{{ strtoupper(substr($vendor->name, 0, 2)) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('vendors.show', $vendor) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{ $vendor->name }}
                                    </a>
                                    <p class="text-sm text-gray-600">{{ $vendor->code }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">₹{{ number_format($vendor->purchase_orders_sum_total_amount ?? 0, 2) }}</p>
                                <p class="text-sm text-gray-600">Total Value</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No vendor data available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Pending Deliveries -->
    @if($pendingDeliveries->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Pending Deliveries (Next 3 Days)</h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                {{ $pendingDeliveries->count() }} pending
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Vendor</th>
                        <th>Branch</th>
                        <th>Expected Delivery</th>
                        <th>Amount</th>
                        <th>Days Left</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingDeliveries as $order)
                        @php
                            $daysLeft = now()->diffInDays($order->expected_delivery_date, false);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td>
                                <a href="{{ route('purchase-orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                    {{ $order->po_number }}
                                </a>
                            </td>
                            <td>{{ $order->vendor->name }}</td>
                            <td>{{ $order->branch->name }}</td>
                            <td>
                                <span class="{{ $daysLeft < 0 ? 'text-red-600 font-semibold' : ($daysLeft <= 1 ? 'text-orange-600 font-medium' : 'text-gray-900') }}">
                                    {{ $order->expected_delivery_date->format('M d, Y') }}
                                    @if($daysLeft < 0)
                                        ({{ abs($daysLeft) }} days overdue)
                                    @endif
                                </span>
                            </td>
                            <td class="font-semibold">₹{{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $daysLeft < 0 ? 'bg-red-100 text-red-800' : ($daysLeft <= 1 ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800') }}">
                                    @if($daysLeft < 0)
                                        Overdue
                                    @elseif($daysLeft == 0)
                                        Today
                                    @elseif($daysLeft == 1)
                                        Tomorrow
                                    @else
                                        {{ $daysLeft }} days
                                    @endif
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('purchase-orders.receive-form', $order) }}" 
                                   class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Mark Received
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection