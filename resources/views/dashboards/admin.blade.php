@extends('layouts.super-admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8 text-white shadow-2xl">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        </div>

        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center float-animation flex-shrink-0">
                        <i class="fas fa-chart-line text-xl sm:text-2xl text-white"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-1 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                            Welcome back, {{ Auth::user()->name }}!
                        </h1>
                        <p class="text-blue-100 text-base sm:text-lg font-medium">Manage your business operations</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-4 mt-4">
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-3 sm:px-4 py-1.5 sm:py-2">
                        <i class="fas fa-calendar-day text-blue-200 text-sm"></i>
                        <span class="text-xs sm:text-sm font-medium">Today's Focus</span>
                    </div>
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-3 sm:px-4 py-1.5 sm:py-2">
                        <i class="fas fa-trending-up text-green-300 text-sm"></i>
                        <span class="text-xs sm:text-sm font-medium">Performance Up</span>
                    </div>
                </div>
            </div>
            <div class="lg:hidden text-center">
                <div class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                    {{ Carbon\Carbon::now()->format('M d, Y') }}
                </div>
                <div class="text-blue-200 text-sm sm:text-base font-medium">{{ Carbon\Carbon::now()->format('l') }}</div>
            </div>
            <div class="hidden lg:block">
                <div class="text-right space-y-2">
                    <div class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                        {{ Carbon\Carbon::now()->format('M d, Y') }}
                    </div>
                    <div class="text-blue-200 text-base lg:text-lg font-medium">{{ Carbon\Carbon::now()->format('l') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Products -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_products']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-box text-blue-600 text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <span class="text-green-600 text-xs sm:text-sm font-medium">{{ $inventory_alerts['low_stock'] }} Low Stock</span>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-shopping-cart text-green-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Monthly Revenue</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">₹{{ number_format($stats['monthly_revenue'], 2) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-chart-line text-purple-600 text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <span class="text-gray-600 text-xs sm:text-sm">Total: ₹{{ number_format($stats['total_revenue'], 2) }}</span>
            </div>
        </div>

        <!-- Total Branches -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Branches</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_branches']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-building text-orange-600 text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <span class="text-gray-600 text-xs sm:text-sm">{{ $stats['total_staff'] }} Staff Members</span>
            </div>
        </div>
    </div>

    <!-- Branch Performance and Recent Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
        <!-- Branch Performance -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Branch Performance</h3>
            <div class="space-y-3 sm:space-y-4">
                @foreach($branch_performance as $branch)
                <div class="flex items-center justify-between p-3 sm:p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.branches.show', $branch['id']) }}" class="font-semibold text-gray-900 hover:text-blue-600 transition-colors block truncate">
                            {{ $branch['name'] }}
                        </a>
                        <p class="text-xs sm:text-sm text-gray-600 truncate">Manager: {{ $branch['manager'] }}</p>
                    </div>
                    <div class="text-right ml-4 flex-shrink-0">
                        <p class="font-semibold text-gray-900 text-sm sm:text-base">₹{{ number_format($branch['total_revenue']) }}</p>
                        <p class="text-xs sm:text-sm text-gray-600">{{ $branch['total_orders'] }} orders</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Recent Orders</h3>
            <div class="space-y-3 sm:space-y-4">
                @foreach($recent_orders->take(8) as $order)
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-receipt text-blue-600 text-xs sm:text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 text-sm sm:text-base truncate">#{{ $order->id }}</p>
                            <p class="text-xs sm:text-sm text-gray-600 truncate">{{ $order->customer->name ?? 'Walk-in' }}</p>
                        </div>
                    </div>
                    <div class="text-right ml-4 flex-shrink-0">
                        <p class="font-semibold text-gray-900 text-sm sm:text-base">₹{{ number_format($order->total_amount, 2) }}</p>
                        <p class="text-xs sm:text-sm text-gray-600">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Products and Inventory Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
        <!-- Top Products -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Top Selling Products</h3>
            <div class="space-y-3 sm:space-y-4">
                @foreach($top_products->take(6) as $product)
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="min-w-0 flex-1">
                        <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $product->name }}</h4>
                        <p class="text-xs sm:text-sm text-gray-600 truncate">{{ $product->sku }}</p>
                    </div>
                    <div class="text-right ml-4 flex-shrink-0">
                        <p class="font-semibold text-gray-900 text-sm sm:text-base">{{ $product->total_sold }} sold</p>
                        <p class="text-xs sm:text-sm text-gray-600">₹{{ number_format($product->selling_price, 2) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Inventory Alerts -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Inventory Alerts</h3>
            <div class="space-y-3 sm:space-y-4">
                <div class="flex items-center justify-between p-3 sm:p-4 rounded-lg bg-yellow-50 border border-yellow-200">
                    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Low Stock Items</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Items below threshold</p>
                        </div>
                    </div>
                    <span class="text-xl sm:text-2xl font-bold text-yellow-600 flex-shrink-0">{{ $inventory_alerts['low_stock'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 sm:p-4 rounded-lg bg-red-50 border border-red-200">
                    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-times-circle text-red-600 text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Out of Stock</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Items completely out</p>
                        </div>
                    </div>
                    <span class="text-xl sm:text-2xl font-bold text-red-600 flex-shrink-0">{{ $inventory_alerts['out_of_stock'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 sm:p-4 rounded-lg bg-orange-50 border border-orange-200">
                    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-orange-600 text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Expiring Soon</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Items expiring in 7 days</p>
                        </div>
                    </div>
                    <span class="text-xl sm:text-2xl font-bold text-orange-600 flex-shrink-0">{{ $inventory_alerts['expiring_soon'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <a href="{{ route('products.create') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl sm:rounded-2xl p-4 sm:p-6 text-white hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl touch-target">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-plus text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-base sm:text-lg font-bold">Add Product</h4>
                    <p class="text-blue-100 text-xs sm:text-sm">Create new product</p>
                </div>
            </div>
        </a>

        <a href="{{ route('orders.create') }}" class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl sm:rounded-2xl p-4 sm:p-6 text-white hover:from-green-600 hover:to-green-700 transition-all duration-300 shadow-lg hover:shadow-xl touch-target">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shopping-cart text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-base sm:text-lg font-bold">New Order</h4>
                    <p class="text-green-100 text-xs sm:text-sm">Process new order</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.index') }}" class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl sm:rounded-2xl p-4 sm:p-6 text-white hover:from-purple-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl touch-target">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-chart-bar text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-base sm:text-lg font-bold">Reports</h4>
                    <p class="text-purple-100 text-xs sm:text-sm">View analytics</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.branches.index') }}" class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl sm:rounded-2xl p-4 sm:p-6 text-white hover:from-orange-600 hover:to-orange-700 transition-all duration-300 shadow-lg hover:shadow-xl touch-target sm:col-span-2 lg:col-span-1">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-building text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-base sm:text-lg font-bold">Branches</h4>
                    <p class="text-orange-100 text-xs sm:text-sm">Manage branches</p>
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