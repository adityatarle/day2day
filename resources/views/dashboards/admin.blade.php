@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="p-6 space-y-8 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        </div>
        
        <div class="relative flex items-center justify-between">
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center float-animation">
                        <i class="fas fa-chart-line text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-1 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                            Welcome back, {{ Auth::user()->name }}!
                        </h1>
                        <p class="text-blue-100 text-lg font-medium">Manage your business operations</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6 mt-4">
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-calendar-day text-blue-200"></i>
                        <span class="text-sm font-medium">Today's Focus</span>
                    </div>
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-trending-up text-green-300"></i>
                        <span class="text-sm font-medium">Performance Up</span>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="text-right space-y-2">
                    <div class="text-3xl font-bold bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                        {{ Carbon\Carbon::now()->format('M d, Y') }}
                    </div>
                    <div class="text-blue-200 text-lg font-medium">{{ Carbon\Carbon::now()->format('l') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Products -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_products']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-green-600 text-sm font-medium">{{ $inventory_alerts['low_stock'] }} Low Stock</span>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-gray-900">₹{{ number_format($stats['monthly_revenue'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-gray-600 text-sm">Total: ₹{{ number_format($stats['total_revenue'], 2) }}</span>
            </div>
        </div>

        <!-- Total Branches -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Branches</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_branches']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-orange-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-gray-600 text-sm">{{ $stats['total_staff'] }} Staff Members</span>
            </div>
        </div>
    </div>

    <!-- Branch Performance and Recent Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Branch Performance -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Branch Performance</h3>
            <div class="space-y-4">
                @foreach($branch_performance as $branch)
                <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ $branch['name'] }}</h4>
                        <p class="text-sm text-gray-600">Manager: {{ $branch['manager'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">₹{{ number_format($branch['total_revenue']) }}</p>
                        <p class="text-sm text-gray-600">{{ $branch['total_orders'] }} orders</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Recent Orders</h3>
            <div class="space-y-4">
                @foreach($recent_orders->take(8) as $order)
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-receipt text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">#{{ $order->id }}</p>
                            <p class="text-sm text-gray-600">{{ $order->customer->name ?? 'Walk-in' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">₹{{ number_format($order->total_amount, 2) }}</p>
                        <p class="text-sm text-gray-600">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Products and Inventory Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Products -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Top Selling Products</h3>
            <div class="space-y-4">
                @foreach($top_products->take(6) as $product)
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $product->sku }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">{{ $product->total_sold }} sold</p>
                        <p class="text-sm text-gray-600">₹{{ number_format($product->selling_price, 2) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Inventory Alerts -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Inventory Alerts</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 rounded-lg bg-yellow-50 border border-yellow-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Low Stock Items</h4>
                            <p class="text-sm text-gray-600">Items below threshold</p>
                        </div>
                    </div>
                    <span class="text-2xl font-bold text-yellow-600">{{ $inventory_alerts['low_stock'] }}</span>
                </div>

                <div class="flex items-center justify-between p-4 rounded-lg bg-red-50 border border-red-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Out of Stock</h4>
                            <p class="text-sm text-gray-600">Items completely out</p>
                        </div>
                    </div>
                    <span class="text-2xl font-bold text-red-600">{{ $inventory_alerts['out_of_stock'] }}</span>
                </div>

                <div class="flex items-center justify-between p-4 rounded-lg bg-orange-50 border border-orange-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-orange-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Expiring Soon</h4>
                            <p class="text-sm text-gray-600">Items expiring in 7 days</p>
                        </div>
                    </div>
                    <span class="text-2xl font-bold text-orange-600">{{ $inventory_alerts['expiring_soon'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('products.create') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-white hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Add Product</h4>
                    <p class="text-blue-100 text-sm">Create new product</p>
                </div>
            </div>
        </a>

        <a href="{{ route('orders.create') }}" class="bg-gradient-to-r from-green-500 to-green-600 rounded-2xl p-6 text-white hover:from-green-600 hover:to-green-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">New Order</h4>
                    <p class="text-green-100 text-sm">Process new order</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.index') }}" class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-6 text-white hover:from-purple-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-bar text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Reports</h4>
                    <p class="text-purple-100 text-sm">View analytics</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.branches.index') }}" class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl p-6 text-white hover:from-orange-600 hover:to-orange-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Branches</h4>
                    <p class="text-orange-100 text-sm">Manage branches</p>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.float-animation {
    animation: float 3s ease-in-out infinite;
}
</style>
@endsection