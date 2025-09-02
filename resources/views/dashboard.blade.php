@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="p-6 space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h1>
                <p class="text-blue-100 text-lg">Here's what's happening with your business today.</p>
            </div>
            <div class="hidden md:block">
                <div class="text-right">
                    <div class="text-2xl font-bold">{{ Carbon\Carbon::now()->format('M d, Y') }}</div>
                    <div class="text-blue-100">{{ Carbon\Carbon::now()->format('l') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                    <p class="text-sm text-gray-500 mt-1">This Month: ₹{{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_orders'] ?? 0) }}</p>
                    <p class="text-sm text-gray-500 mt-1">All time orders</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Products</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_products'] ?? 0) }}</p>
                    <p class="text-sm text-gray-500 mt-1">Active products</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Customers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_customers'] ?? 0) }}</p>
                    <p class="text-sm text-gray-500 mt-1">Registered customers</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Branches -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-code-branch text-indigo-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Branches</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_branches'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Vendors -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-tie text-teal-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Vendors</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_vendors'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Purchase Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending POs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_purchase_orders'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                    <a href="{{ route('orders.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View all</a>
                </div>
            </div>
            <div class="p-6">
                @if(isset($recent_orders) && $recent_orders->count() > 0)
                    <div class="space-y-4">
                        @foreach($recent_orders->take(5) as $order)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-shopping-cart text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Order #{{ $order->id }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">₹{{ number_format($order->total_amount, 2) }}</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shopping-cart text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500">No orders yet</p>
                        <p class="text-sm text-gray-400 mt-1">Orders will appear here once created</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
            </div>
            <div class="p-6">
                @if(isset($recent_activities) && $recent_activities->count() > 0)
                    <div class="space-y-4">
                        @foreach($recent_activities->take(6) as $activity)
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                @if($activity['color'] === 'blue') bg-blue-100 text-blue-600
                                @elseif($activity['color'] === 'green') bg-green-100 text-green-600
                                @elseif($activity['color'] === 'purple') bg-purple-100 text-purple-600
                                @else bg-gray-100 text-gray-600 @endif">
                                <i class="fas fa-{{ $activity['icon'] }} text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">{{ $activity['message'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-activity text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500">No recent activities</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Additional Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Low Stock Alerts</h3>
                    <a href="{{ route('inventory.lowStockAlerts') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View all</a>
                </div>
            </div>
            <div class="p-6">
                @if(isset($low_stock_products) && $low_stock_products->count() > 0)
                    <div class="space-y-3">
                        @foreach($low_stock_products->take(4) as $product)
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-sm text-red-600">Low stock</p>
                                </div>
                            </div>
                            <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-700 text-sm">View</a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <p class="text-gray-500">All products are well stocked</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Selling Products</h3>
            </div>
            <div class="p-6">
                @if(isset($top_products) && $top_products->count() > 0)
                    <div class="space-y-3">
                        @foreach($top_products->take(4) as $product)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-star text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $product->total_sold ?? 0 }} sold</p>
                                </div>
                            </div>
                            <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-700 text-sm">View</a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-chart-line text-gray-400"></i>
                        </div>
                        <p class="text-gray-500">No sales data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('products.create') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                    <i class="fas fa-plus text-blue-600 text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-900">Add Product</span>
            </a>
            
            <a href="{{ route('orders.create') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-900">New Order</span>
            </a>
            
            <a href="{{ route('customers.create') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors duration-200">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                    <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-900">Add Customer</span>
            </a>
            
            <a href="{{ route('vendors.create') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors duration-200">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-3">
                    <i class="fas fa-building text-orange-600 text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-900">Add Vendor</span>
            </a>
        </div>
    </div>
</div>
@endsection