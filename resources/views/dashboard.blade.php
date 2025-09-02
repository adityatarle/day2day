@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->name }}! Here's what's happening with your business today.</p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="bg-white px-4 py-2 rounded-lg shadow border">
                    <div class="text-sm text-gray-500">Today</div>
                    <div class="text-lg font-semibold text-gray-900">{{ Carbon\Carbon::now()->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl text-white shadow-lg hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Revenue</p>
                    <p class="text-2xl font-bold">₹{{ number_format($stats['total_revenue'], 2) }}</p>
                    <p class="text-blue-100 text-xs mt-1">This Month: ₹{{ number_format($stats['monthly_revenue'], 2) }}</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl text-white shadow-lg hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Orders</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['total_orders']) }}</p>
                    <p class="text-green-100 text-xs mt-1">All time orders</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl text-white shadow-lg hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Total Products</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['total_products']) }}</p>
                    <p class="text-purple-100 text-xs mt-1">Active products</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-xl text-white shadow-lg hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Total Customers</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['total_customers']) }}</p>
                    <p class="text-orange-100 text-xs mt-1">Registered customers</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover-lift">
            <div class="flex items-center">
                <div class="bg-indigo-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Branches</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_branches'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover-lift">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover-lift">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending POs</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_purchase_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover-lift">
            <div class="flex items-center">
                <div class="bg-gray-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Expenses</p>
                    <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($stats['total_expenses'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Alerts -->
    @if($inventory_alerts['low_stock'] > 0 || $inventory_alerts['out_of_stock'] > 0 || $inventory_alerts['expiring_soon'] > 0)
    <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
        <div class="flex items-center mb-4">
            <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L5.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-red-800">Inventory Alerts</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if($inventory_alerts['low_stock'] > 0)
            <div class="bg-white p-4 rounded-lg border border-red-200">
                <div class="text-sm text-red-600 font-medium">Low Stock Items</div>
                <div class="text-2xl font-bold text-red-800">{{ $inventory_alerts['low_stock'] }}</div>
                <a href="{{ route('inventory.index') }}" class="text-sm text-red-600 hover:text-red-800">View Details →</a>
            </div>
            @endif
            @if($inventory_alerts['out_of_stock'] > 0)
            <div class="bg-white p-4 rounded-lg border border-red-200">
                <div class="text-sm text-red-600 font-medium">Out of Stock</div>
                <div class="text-2xl font-bold text-red-800">{{ $inventory_alerts['out_of_stock'] }}</div>
                <a href="{{ route('inventory.index') }}" class="text-sm text-red-600 hover:text-red-800">View Details →</a>
            </div>
            @endif
            @if($inventory_alerts['expiring_soon'] > 0)
            <div class="bg-white p-4 rounded-lg border border-red-200">
                <div class="text-sm text-red-600 font-medium">Expiring Soon</div>
                <div class="text-2xl font-bold text-red-800">{{ $inventory_alerts['expiring_soon'] }}</div>
                <a href="{{ route('inventory.index') }}" class="text-sm text-red-600 hover:text-red-800">View Details →</a>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                        <a href="{{ route('orders.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</a>
                    </div>
                </div>
                <div class="p-6">
                    @if($recent_orders->count() > 0)
                        <div class="space-y-4">
                            @foreach($recent_orders as $order)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-blue-100 p-2 rounded-lg">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Order #{{ $order->id }}</p>
                                        <p class="text-sm text-gray-600">{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">₹{{ number_format($order->total_amount, 2) }}</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <p class="text-gray-500">No orders yet</p>
                            <a href="{{ route('orders.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Create your first order →</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('orders.create') }}" class="flex items-center w-full p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                        <div class="bg-blue-100 group-hover:bg-blue-200 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <span class="ml-3 font-medium text-gray-900">Create New Order</span>
                    </a>

                    <a href="{{ route('products.create') }}" class="flex items-center w-full p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                        <div class="bg-green-100 group-hover:bg-green-200 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <span class="ml-3 font-medium text-gray-900">Add New Product</span>
                    </a>

                    <a href="{{ route('vendors.create') }}" class="flex items-center w-full p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group">
                        <div class="bg-purple-100 group-hover:bg-purple-200 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <span class="ml-3 font-medium text-gray-900">Add New Vendor</span>
                    </a>

                    <a href="{{ route('purchase-orders.create') }}" class="flex items-center w-full p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors group">
                        <div class="bg-orange-100 group-hover:bg-orange-200 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span class="ml-3 font-medium text-gray-900">Create Purchase Order</span>
                    </a>

                    <a href="{{ route('inventory.addStockForm') }}" class="flex items-center w-full p-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors group">
                        <div class="bg-indigo-100 group-hover:bg-indigo-200 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <span class="ml-3 font-medium text-gray-900">Add Stock</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
                </div>
                <div class="p-6">
                    @if($recent_activities->count() > 0)
                        <div class="space-y-3">
                            @foreach($recent_activities as $activity)
                            <div class="flex items-center space-x-3">
                                <div class="bg-{{ $activity['color'] }}-100 p-2 rounded-lg">
                                    @if($activity['icon'] === 'shopping-cart')
                                        <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    @elseif($activity['icon'] === 'truck')
                                        <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity['time']->diffForHumans() }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">No recent activities</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Top Selling Products</h3>
                </div>
                <div class="p-6">
                    @if($top_products->count() > 0)
                        <div class="space-y-3">
                            @foreach($top_products->take(5) as $product)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-500 rounded-lg flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $product->category }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ $product->total_sold ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">sold</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">No sales data yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Branch Performance -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Branch Performance</h3>
                </div>
                <div class="p-6">
                    @if($branch_performance->count() > 0)
                        <div class="space-y-4">
                            @foreach($branch_performance as $branch)
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-900">{{ $branch->name }}</h4>
                                    <span class="text-xs text-gray-500">{{ $branch->orders_count }} orders</span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <p>Revenue: ₹{{ number_format($branch->orders_sum_total_amount ?? 0, 2) }}</p>
                                    <p>Products: {{ $branch->products_count }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">No branch data</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Products Section -->
    @if($low_stock_products->count() > 0)
    <div class="mt-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Low Stock Products</h3>
                    <a href="{{ route('inventory.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</a>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($low_stock_products as $product)
                    <div class="p-4 border border-red-200 bg-red-50 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900">{{ $product->name }}</h4>
                            <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full">Low Stock</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <p>Category: {{ $product->category }}</p>
                            @foreach($product->branches as $branch)
                            <p>{{ $branch->name }}: {{ $branch->pivot->current_stock ?? 0 }}{{ $product->weight_unit }}</p>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('inventory.addStockForm') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Add Stock →</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Include Chart.js for analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Analytics Chart
const salesData = @json($sales_analytics);
if (salesData.length > 0) {
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(item => item.date),
                datasets: [{
                    label: 'Revenue',
                    data: salesData.map(item => item.revenue),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}
</script>
@endsection