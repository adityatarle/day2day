@extends('layouts.super-admin')

@section('title', 'Admin Dashboard')

@section('head')
<!-- Chart.js for interactive charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

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
                    <button onclick="openTodaysFocusModal()" class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-3 sm:px-4 py-1.5 sm:py-2 hover:bg-white/20 transition-all duration-300 cursor-pointer">
                        <i class="fas fa-calendar-day text-blue-200 text-sm"></i>
                        <span class="text-xs sm:text-sm font-medium">Today's Focus</span>
                    </button>
                    <button onclick="openPerformanceModal()" class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-3 sm:px-4 py-1.5 sm:py-2 hover:bg-white/20 transition-all duration-300 cursor-pointer">
                        <i class="fas fa-trending-up text-green-300 text-sm"></i>
                        <span class="text-xs sm:text-sm font-medium">Performance Up</span>
                    </button>
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
        <div onclick="openProductsModal()" class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer hover:scale-105">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Total Products</p>
                    <p id="total-products" class="text-2xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_products']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-box text-blue-600 text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <span id="low-stock-count" class="text-green-600 text-xs sm:text-sm font-medium">{{ $inventory_alerts['low_stock'] }} Low Stock</span>
            </div>
        </div>

        <!-- Total Orders -->
        <div onclick="openOrdersModal()" class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer hover:scale-105">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Total Orders</p>
                    <p id="total-orders" class="text-2xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-shopping-cart text-green-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div onclick="openRevenueModal()" class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer hover:scale-105">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Monthly Revenue</p>
                    <p id="monthly-revenue" class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">₹{{ number_format($stats['monthly_revenue'], 2) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-chart-line text-purple-600 text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <span id="total-revenue" class="text-gray-600 text-xs sm:text-sm">Total: ₹{{ number_format($stats['total_revenue'], 2) }}</span>
            </div>
        </div>

        <!-- Total Branches -->
        <div onclick="openBranchesModal()" class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer hover:scale-105">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Branches</p>
                    <p id="total-branches" class="text-2xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_branches']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-building text-orange-600 text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <span id="total-staff" class="text-gray-600 text-xs sm:text-sm">{{ $stats['total_staff'] }} Staff Members</span>
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

    <!-- Interactive Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
        <!-- Sales Trend Chart -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Sales Trend (Last 7 Days)</h3>
            <div class="relative h-64">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Revenue vs Orders Chart -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Revenue vs Orders</h3>
            <div class="relative h-64">
                <canvas id="revenueOrdersChart"></canvas>
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

<!-- Modals -->
<!-- Today's Focus Modal -->
<div id="todaysFocusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Today's Focus</h3>
                <button onclick="closeModal('todaysFocusModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div id="todaysFocusContent" class="space-y-6">
                <!-- Loading state -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
                    <p class="mt-2 text-gray-600">Loading today's focus...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Modal -->
<div id="performanceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Performance Analytics</h3>
                <button onclick="closeModal('performanceModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div id="performanceContent" class="space-y-6">
                <!-- Loading state -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
                    <p class="mt-2 text-gray-600">Loading performance data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Products Modal -->
<div id="productsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Product Management</h3>
                <button onclick="closeModal('productsModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-4">Quick Actions</h4>
                    <div class="space-y-3">
                        <a href="{{ route('products.create') }}" class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New Product
                        </a>
                        <a href="{{ route('products.index') }}" class="block w-full bg-blue-100 text-blue-700 text-center py-3 rounded-lg hover:bg-blue-200 transition-colors">
                            <i class="fas fa-list mr-2"></i>View All Products
                        </a>
                        <a href="{{ route('inventory.lowStockAlerts') }}" class="block w-full bg-orange-100 text-orange-700 text-center py-3 rounded-lg hover:bg-orange-200 transition-colors">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Low Stock Alerts
                        </a>
                    </div>
                </div>
                <div class="bg-green-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-green-900 mb-4">Product Statistics</h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Products:</span>
                            <span class="font-semibold text-lg" id="modal-total-products">{{ number_format($stats['total_products']) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Low Stock Items:</span>
                            <span class="font-semibold text-lg text-orange-600" id="modal-low-stock">{{ $inventory_alerts['low_stock'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Out of Stock:</span>
                            <span class="font-semibold text-lg text-red-600">{{ $inventory_alerts['out_of_stock'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Modal -->
<div id="ordersModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Order Management</h3>
                <button onclick="closeModal('ordersModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-green-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-green-900 mb-4">Quick Actions</h4>
                    <div class="space-y-3">
                        <a href="{{ route('orders.create') }}" class="block w-full bg-green-600 text-white text-center py-3 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Create New Order
                        </a>
                        <a href="{{ route('orders.index') }}" class="block w-full bg-green-100 text-green-700 text-center py-3 rounded-lg hover:bg-green-200 transition-colors">
                            <i class="fas fa-list mr-2"></i>View All Orders
                        </a>
                        <a href="{{ route('billing.quickSale') }}" class="block w-full bg-blue-100 text-blue-700 text-center py-3 rounded-lg hover:bg-blue-200 transition-colors">
                            <i class="fas fa-bolt mr-2"></i>Quick Sale
                        </a>
                    </div>
                </div>
                <div class="bg-blue-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-4">Order Statistics</h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Orders:</span>
                            <span class="font-semibold text-lg" id="modal-total-orders">{{ number_format($stats['total_orders']) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Today's Orders:</span>
                            <span class="font-semibold text-lg text-green-600">{{ $recent_orders->where('created_at', '>=', \Carbon\Carbon::today())->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Pending Orders:</span>
                            <span class="font-semibold text-lg text-orange-600">{{ $recent_orders->where('status', 'pending')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Modal -->
<div id="revenueModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Financial Analytics</h3>
                <button onclick="closeModal('revenueModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-purple-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-purple-900 mb-4">Revenue Overview</h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Monthly Revenue:</span>
                            <span class="font-semibold text-lg" id="modal-monthly-revenue">₹{{ number_format($stats['monthly_revenue'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Revenue:</span>
                            <span class="font-semibold text-lg" id="modal-total-revenue">₹{{ number_format($stats['total_revenue'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Average Order Value:</span>
                            <span class="font-semibold text-lg text-green-600">₹{{ $stats['total_orders'] > 0 ? number_format($stats['total_revenue'] / $stats['total_orders'], 2) : '0.00' }}</span>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-4">Quick Actions</h4>
                    <div class="space-y-3">
                        <a href="{{ route('reports.sales') }}" class="block w-full bg-purple-600 text-white text-center py-3 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-chart-line mr-2"></i>Sales Reports
                        </a>
                        <a href="{{ route('reports.profitLoss') }}" class="block w-full bg-blue-100 text-blue-700 text-center py-3 rounded-lg hover:bg-blue-200 transition-colors">
                            <i class="fas fa-calculator mr-2"></i>Profit & Loss
                        </a>
                        <a href="{{ route('reports.analytics') }}" class="block w-full bg-green-100 text-green-700 text-center py-3 rounded-lg hover:bg-green-200 transition-colors">
                            <i class="fas fa-chart-bar mr-2"></i>Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Branches Modal -->
<div id="branchesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Branch Management</h3>
                <button onclick="closeModal('branchesModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-orange-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-orange-900 mb-4">Quick Actions</h4>
                    <div class="space-y-3">
                        <a href="{{ route('admin.branches.index') }}" class="block w-full bg-orange-600 text-white text-center py-3 rounded-lg hover:bg-orange-700 transition-colors">
                            <i class="fas fa-building mr-2"></i>Manage Branches
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="block w-full bg-blue-100 text-blue-700 text-center py-3 rounded-lg hover:bg-blue-200 transition-colors">
                            <i class="fas fa-users mr-2"></i>Manage Staff
                        </a>
                        <a href="{{ route('admin.branches.performance') }}" class="block w-full bg-green-100 text-green-700 text-center py-3 rounded-lg hover:bg-green-200 transition-colors">
                            <i class="fas fa-chart-line mr-2"></i>Performance Analytics
                        </a>
                    </div>
                </div>
                <div class="bg-blue-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-4">Branch Statistics</h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Branches:</span>
                            <span class="font-semibold text-lg" id="modal-total-branches">{{ number_format($stats['total_branches']) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Staff:</span>
                            <span class="font-semibold text-lg" id="modal-total-staff">{{ $stats['total_staff'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Active Branches:</span>
                            <span class="font-semibold text-lg text-green-600">{{ $branch_performance->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

/* Modal animations */
.modal-enter {
    animation: modalEnter 0.3s ease-out;
}

@keyframes modalEnter {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Hover effects for cards */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .modal-enter {
        animation: modalEnterMobile 0.3s ease-out;
    }
    
    @keyframes modalEnterMobile {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Touch-friendly buttons */
    .touch-target {
        min-height: 44px;
        min-width: 44px;
    }
    
    /* Better spacing on mobile */
    .mobile-padding {
        padding: 1rem;
    }
    
    /* Stack modals better on mobile */
    .mobile-modal {
        margin: 0.5rem;
        max-height: calc(100vh - 1rem);
    }
}

/* Tablet optimizations */
@media (min-width: 641px) and (max-width: 1024px) {
    .tablet-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .dark-mode-card {
        background-color: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .dark-mode-text {
        color: #d1d5db;
    }
}

/* Accessibility improvements */
.focus\:ring-2:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Loading animations */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Smooth transitions for all interactive elements */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>

<script>
// Modal functions
function openTodaysFocusModal() {
    const modal = document.getElementById('todaysFocusModal');
    modal.classList.remove('hidden');
    modal.classList.add('modal-enter');
    
    // Load today's focus data
    fetch('{{ route("admin.dashboard.todays-focus") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('todaysFocusContent').innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-blue-900 mb-4">Today's Summary</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Orders Today:</span>
                                <span class="font-semibold text-lg text-blue-600">${data.todays_orders}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Revenue Today:</span>
                                <span class="font-semibold text-lg text-green-600">₹${data.todays_revenue.toLocaleString()}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Low Stock Items:</span>
                                <span class="font-semibold text-lg text-orange-600">${data.low_stock_items}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pending POs:</span>
                                <span class="font-semibold text-lg text-red-600">${data.pending_purchase_orders}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-green-900 mb-4">Priority Tasks</h4>
                        <div class="space-y-3">
                            ${data.priority_tasks.map(task => `
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-${task.icon} text-${task.color}-600"></i>
                                        <div>
                                            <p class="font-medium text-gray-900">${task.title}</p>
                                            <p class="text-sm text-gray-600">${task.description}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-${task.color}-100 text-${task.color}-800">
                                        ${task.priority}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Today's Activities</h4>
                    <div class="space-y-3">
                        ${data.activities.map(activity => `
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-${activity.icon} text-${activity.color}-600"></i>
                                    <div>
                                        <p class="font-medium text-gray-900">${activity.message}</p>
                                        <p class="text-sm text-gray-600">${activity.time}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">₹${activity.amount.toLocaleString()}</p>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-${activity.color}-100 text-${activity.color}-800">
                                        ${activity.status}
                                    </span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading today\'s focus:', error);
            document.getElementById('todaysFocusContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                    <p class="mt-2 text-gray-600">Error loading data. Please try again.</p>
                </div>
            `;
        });
}

function openPerformanceModal() {
    const modal = document.getElementById('performanceModal');
    modal.classList.remove('hidden');
    modal.classList.add('modal-enter');
    
    // Load performance data
    fetch('{{ route("admin.dashboard.performance") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('performanceContent').innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-blue-900 mb-4">Monthly Performance</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Orders:</span>
                                <div class="text-right">
                                    <span class="font-semibold text-lg">${data.current_month.orders}</span>
                                    <span class="ml-2 text-sm ${data.current_month.order_growth >= 0 ? 'text-green-600' : 'text-red-600'}">
                                        ${data.current_month.order_growth >= 0 ? '+' : ''}${data.current_month.order_growth}%
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Revenue:</span>
                                <div class="text-right">
                                    <span class="font-semibold text-lg">₹${data.current_month.revenue.toLocaleString()}</span>
                                    <span class="ml-2 text-sm ${data.current_month.revenue_growth >= 0 ? 'text-green-600' : 'text-red-600'}">
                                        ${data.current_month.revenue_growth >= 0 ? '+' : ''}${data.current_month.revenue_growth}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-green-900 mb-4">Top Performing Products</h4>
                        <div class="space-y-3">
                            ${data.top_products.map(product => `
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">${product.name}</p>
                                        <p class="text-sm text-gray-600">${product.sku || 'N/A'}</p>
                                    </div>
                                    <span class="font-semibold text-green-600">${product.total_sold} sold</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Branch Performance</h4>
                    <div class="space-y-3">
                        ${data.branch_performance.map(branch => `
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">${branch.name}</p>
                                    <p class="text-sm text-gray-600">Manager: ${branch.manager}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">${branch.orders} orders</p>
                                    <p class="text-sm text-gray-600">₹${branch.revenue.toLocaleString()}</p>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        Score: ${branch.performance_score}
                                    </span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading performance data:', error);
            document.getElementById('performanceContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                    <p class="mt-2 text-gray-600">Error loading data. Please try again.</p>
                </div>
            `;
        });
}

function openProductsModal() {
    const modal = document.getElementById('productsModal');
    modal.classList.remove('hidden');
    modal.classList.add('modal-enter');
}

function openOrdersModal() {
    const modal = document.getElementById('ordersModal');
    modal.classList.remove('hidden');
    modal.classList.add('modal-enter');
}

function openRevenueModal() {
    const modal = document.getElementById('revenueModal');
    modal.classList.remove('hidden');
    modal.classList.add('modal-enter');
}

function openBranchesModal() {
    const modal = document.getElementById('branchesModal');
    modal.classList.remove('hidden');
    modal.classList.add('modal-enter');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    modal.classList.remove('modal-enter');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
        event.target.classList.add('hidden');
        event.target.classList.remove('modal-enter');
    }
});

// Auto-refresh dashboard data every 30 seconds
setInterval(function() {
    updateDashboardData();
}, 30000);

function updateDashboardData() {
    // Update products data
    fetch('{{ route("admin.dashboard.widget-data") }}?widget=products')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-products').textContent = data.total.toLocaleString();
            document.getElementById('low-stock-count').textContent = `${data.low_stock} Low Stock`;
            document.getElementById('modal-total-products').textContent = data.total.toLocaleString();
            document.getElementById('modal-low-stock').textContent = data.low_stock;
        })
        .catch(error => console.error('Error updating products data:', error));

    // Update orders data
    fetch('{{ route("admin.dashboard.widget-data") }}?widget=orders')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-orders').textContent = data.total.toLocaleString();
            document.getElementById('modal-total-orders').textContent = data.total.toLocaleString();
        })
        .catch(error => console.error('Error updating orders data:', error));

    // Update revenue data
    fetch('{{ route("admin.dashboard.widget-data") }}?widget=revenue')
        .then(response => response.json())
        .then(data => {
            document.getElementById('monthly-revenue').textContent = `₹${data.monthly.toLocaleString()}`;
            document.getElementById('total-revenue').textContent = `Total: ₹${data.total.toLocaleString()}`;
            document.getElementById('modal-monthly-revenue').textContent = `₹${data.monthly.toLocaleString()}`;
            document.getElementById('modal-total-revenue').textContent = `₹${data.total.toLocaleString()}`;
        })
        .catch(error => console.error('Error updating revenue data:', error));

    // Update branches data
    fetch('{{ route("admin.dashboard.widget-data") }}?widget=branches')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-branches').textContent = data.total.toLocaleString();
            document.getElementById('total-staff').textContent = `${data.staff} Staff Members`;
            document.getElementById('modal-total-branches').textContent = data.total.toLocaleString();
            document.getElementById('modal-total-staff').textContent = data.staff;
        })
        .catch(error => console.error('Error updating branches data:', error));
}

// Initialize dashboard on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add loading states and smooth transitions
    const cards = document.querySelectorAll('.bg-white.rounded-xl');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Initialize charts
    initializeCharts();
});

// Chart initialization
function initializeCharts() {
    // Sales Trend Chart
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    const salesTrendChart = new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($sales_analytics->take(7) as $day)
                '{{ \Carbon\Carbon::parse($day->date)->format('M d') }}',
                @endforeach
            ],
            datasets: [{
                label: 'Orders',
                data: [
                    @foreach($sales_analytics->take(7) as $day)
                    {{ $day->orders }},
                    @endforeach
                ],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Revenue (₹)',
                data: [
                    @foreach($sales_analytics->take(7) as $day)
                    {{ $day->revenue }},
                    @endforeach
                ],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Orders'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue (₹)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 1) {
                                return 'Revenue: ₹' + context.parsed.y.toLocaleString();
                            }
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });

    // Revenue vs Orders Chart
    const revenueOrdersCtx = document.getElementById('revenueOrdersChart').getContext('2d');
    const revenueOrdersChart = new Chart(revenueOrdersCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed Orders', 'Pending Orders', 'Cancelled Orders'],
            datasets: [{
                data: [
                    {{ $recent_orders->where('status', 'completed')->count() }},
                    {{ $recent_orders->where('status', 'pending')->count() }},
                    {{ $recent_orders->where('status', 'cancelled')->count() }}
                ],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(251, 191, 36)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Store chart instances for potential updates
    window.salesTrendChart = salesTrendChart;
    window.revenueOrdersChart = revenueOrdersChart;
}

// Function to update charts with new data
function updateCharts() {
    // This function can be called to refresh chart data
    // For now, we'll just reinitialize them
    if (window.salesTrendChart) {
        window.salesTrendChart.destroy();
    }
    if (window.revenueOrdersChart) {
        window.revenueOrdersChart.destroy();
    }
    initializeCharts();
}
</script>
@endsection