@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="p-3 sm:p-4 lg:p-6 space-y-4 sm:space-y-6 lg:space-y-8 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8 text-white shadow-2xl">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        </div>
        
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-3">
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center float-animation">
                        <i class="fas fa-chart-line text-lg sm:text-xl lg:text-2xl text-white"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl sm:text-2xl lg:text-4xl font-bold mb-1 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                            Welcome back, {{ Auth::user()->name }}! 
                        </h1>
                        <p class="text-blue-100 text-sm sm:text-base lg:text-lg font-medium">Manage your business with powerful insights</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 lg:gap-6 mt-4">
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-3 py-1.5 sm:px-4 sm:py-2">
                        <i class="fas fa-calendar-day text-blue-200 text-sm"></i>
                        <span class="text-xs sm:text-sm font-medium">Today's Focus</span>
                    </div>
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-3 py-1.5 sm:px-4 sm:py-2">
                        <i class="fas fa-trending-up text-green-300 text-sm"></i>
                        <span class="text-xs sm:text-sm font-medium">Performance Up</span>
                    </div>
                </div>
            </div>
            <div class="hidden lg:block mt-4 lg:mt-0">
                <div class="text-right space-y-2">
                    <div class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                        {{ Carbon\Carbon::now()->format('M d, Y') }}
                    </div>
                    <div class="text-blue-200 text-base lg:text-lg font-medium">{{ Carbon\Carbon::now()->format('l') }}</div>
                    <div class="w-20 lg:w-24 h-1 bg-gradient-to-r from-blue-300 to-purple-300 rounded-full mt-3 ml-auto"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 xl:gap-8">
        <!-- Total Revenue -->
        <div class="metric-card rounded-xl sm:rounded-2xl p-3 sm:p-4 lg:p-6 group cursor-pointer slide-in-up delay-100 interactive-card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-2">
                <div class="space-y-1.5 sm:space-y-2 min-w-0 flex-1 order-2 sm:order-1">
                    <div class="flex items-center space-x-2">
                        <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Revenue</p>
                        <div class="w-2 h-2 bg-blue-500 rounded-full pulse-glow"></div>
                    </div>
                    <p class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                        ₹{{ number_format($stats['total_revenue'] ?? 0, 2) }}
                    </p>
                    <div class="space-y-1 mobile-hide sm:block">
                        <p class="text-xs sm:text-sm text-gray-500">This Month: ₹{{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</p>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="progress-bar rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
                <div class="w-12 h-12 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl sm:rounded-2xl flex items-center justify-center icon-bounce group-hover:glow-blue flex-shrink-0 order-1 sm:order-2 self-start sm:self-auto">
                    <i class="fas fa-rupee-sign text-white text-base sm:text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="metric-card rounded-xl sm:rounded-2xl p-3 sm:p-4 lg:p-6 group cursor-pointer slide-in-up delay-200 interactive-card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-2">
                <div class="space-y-1.5 sm:space-y-2 min-w-0 flex-1 order-2 sm:order-1">
                    <div class="flex items-center space-x-2">
                        <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Orders</p>
                        <div class="w-2 h-2 bg-green-500 rounded-full pulse-glow"></div>
                    </div>
                    <p class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-gray-900 group-hover:text-green-600 transition-colors">
                        {{ number_format($stats['total_orders'] ?? 0) }}
                    </p>
                    <div class="space-y-1 mobile-hide sm:block">
                        <p class="text-xs sm:text-sm text-gray-500">All time orders</p>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-green-400 to-emerald-500 rounded-full h-2" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
                <div class="w-12 h-12 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl sm:rounded-2xl flex items-center justify-center icon-bounce group-hover:glow-green flex-shrink-0 order-1 sm:order-2 self-start sm:self-auto">
                    <i class="fas fa-shopping-cart text-white text-base sm:text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="metric-card rounded-xl sm:rounded-2xl p-3 sm:p-4 lg:p-6 group cursor-pointer slide-in-up delay-300 interactive-card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-2">
                <div class="space-y-1.5 sm:space-y-2 min-w-0 flex-1 order-2 sm:order-1">
                    <div class="flex items-center space-x-2">
                        <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Products</p>
                        <div class="w-2 h-2 bg-purple-500 rounded-full pulse-glow"></div>
                    </div>
                    <p class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors">
                        {{ number_format($stats['total_products'] ?? 0) }}
                    </p>
                    <div class="space-y-1 mobile-hide sm:block">
                        <p class="text-xs sm:text-sm text-gray-500">Active products</p>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-purple-400 to-violet-500 rounded-full h-2" style="width: 92%"></div>
                        </div>
                    </div>
                </div>
                <div class="w-12 h-12 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl sm:rounded-2xl flex items-center justify-center icon-bounce group-hover:glow-purple flex-shrink-0 order-1 sm:order-2 self-start sm:self-auto">
                    <i class="fas fa-box text-white text-base sm:text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="metric-card rounded-xl sm:rounded-2xl p-3 sm:p-4 lg:p-6 group cursor-pointer slide-in-up delay-400 interactive-card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-2">
                <div class="space-y-1.5 sm:space-y-2 min-w-0 flex-1 order-2 sm:order-1">
                    <div class="flex items-center space-x-2">
                        <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Customers</p>
                        <div class="w-2 h-2 bg-orange-500 rounded-full pulse-glow"></div>
                    </div>
                    <p class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-gray-900 group-hover:text-orange-600 transition-colors">
                        {{ number_format($stats['total_customers'] ?? 0) }}
                    </p>
                    <div class="space-y-1 mobile-hide sm:block">
                        <p class="text-xs sm:text-sm text-gray-500">Registered customers</p>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-orange-400 to-red-500 rounded-full h-2" style="width: 68%"></div>
                        </div>
                    </div>
                </div>
                <div class="w-12 h-12 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl sm:rounded-2xl flex items-center justify-center icon-bounce group-hover:glow-orange flex-shrink-0 order-1 sm:order-2 self-start sm:self-auto">
                    <i class="fas fa-users text-white text-base sm:text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
        <!-- Branches -->
        <div class="glass-card rounded-xl sm:rounded-2xl p-4 sm:p-6 group hover:shadow-modern-lg transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="space-y-2 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Branches</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                        {{ $stats['total_branches'] ?? 0 }}
                    </p>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-indigo-500 rounded-full"></div>
                        <span class="text-xs text-gray-500 font-medium">Active Locations</span>
                    </div>
                </div>
                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center icon-bounce flex-shrink-0">
                    <i class="fas fa-code-branch text-white text-base sm:text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Vendors -->
        <div class="glass-card rounded-xl sm:rounded-2xl p-4 sm:p-6 group hover:shadow-modern-lg transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="space-y-2 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Vendors</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 group-hover:text-teal-600 transition-colors">
                        {{ $stats['total_vendors'] ?? 0 }}
                    </p>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-teal-500 rounded-full"></div>
                        <span class="text-xs text-gray-500 font-medium">Business Partners</span>
                    </div>
                </div>
                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center icon-bounce flex-shrink-0">
                    <i class="fas fa-user-tie text-white text-base sm:text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Pending Purchase Orders -->
        <div class="glass-card rounded-xl sm:rounded-2xl p-4 sm:p-6 group hover:shadow-modern-lg transition-all duration-300 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div class="space-y-2 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Pending POs</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 group-hover:text-amber-600 transition-colors">
                        {{ $stats['pending_purchase_orders'] ?? 0 }}
                    </p>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-amber-500 rounded-full animate-pulse"></div>
                        <span class="text-xs text-gray-500 font-medium">Awaiting Action</span>
                    </div>
                </div>
                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center icon-bounce flex-shrink-0">
                    <i class="fas fa-clock text-white text-base sm:text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50 to-blue-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shopping-cart text-white text-sm sm:text-base"></i>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Recent Orders</h3>
                    </div>
                    <a href="{{ route('orders.index') }}" class="btn-modern bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-3 py-2 sm:px-4 rounded-lg sm:rounded-xl text-xs sm:text-sm font-semibold hover:shadow-lg transition-all duration-300 self-start sm:self-auto">
                        <span class="sm:hidden">View all</span>
                        <span class="hidden sm:inline">View all <i class="fas fa-arrow-right ml-2"></i></span>
                    </a>
                </div>
            </div>
            <div class="p-6">
                @if(isset($recent_orders) && $recent_orders->count() > 0)
                    <div class="space-y-4">
                        @foreach($recent_orders->take(5) as $order)
                        <div class="group flex items-center justify-between p-5 bg-gradient-to-r from-gray-50 to-blue-50/30 rounded-xl hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-blue-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="fas fa-shopping-cart text-white"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">Order #{{ $order->id }}</p>
                                    <p class="text-sm text-gray-600 font-medium">{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <i class="fas fa-calendar-alt text-gray-400 text-xs"></i>
                                        <span class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right space-y-2">
                                <p class="font-bold text-gray-900 text-lg">₹{{ number_format($order->total_amount, 2) }}</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                    @if($order->status === 'completed') bg-gradient-to-r from-green-400 to-emerald-500 text-white
                                    @elseif($order->status === 'pending') bg-gradient-to-r from-yellow-400 to-orange-500 text-white
                                    @elseif($order->status === 'processing') bg-gradient-to-r from-blue-400 to-indigo-500 text-white
                                    @else bg-gradient-to-r from-gray-400 to-gray-500 text-white @endif
                                    shadow-lg">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4 float-animation">
                            <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-600 font-semibold text-lg">No orders yet</p>
                        <p class="text-sm text-gray-500 mt-2">Orders will appear here once created</p>
                        <button class="mt-4 btn-modern bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-6 py-2 rounded-xl font-semibold">
                            Create First Order
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50 to-purple-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-activity text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Recent Activities</h3>
                </div>
            </div>
            <div class="p-6 custom-scrollbar max-h-96 overflow-y-auto">
                @if(isset($recent_activities) && $recent_activities->count() > 0)
                    <div class="space-y-4">
                        @foreach($recent_activities->take(6) as $activity)
                        <div class="flex items-start space-x-4 p-4 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-purple-50/30 transition-all duration-300 group">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform
                                @if($activity['color'] === 'blue') bg-gradient-to-br from-blue-400 to-blue-600 text-white
                                @elseif($activity['color'] === 'green') bg-gradient-to-br from-green-400 to-emerald-600 text-white
                                @elseif($activity['color'] === 'purple') bg-gradient-to-br from-purple-400 to-violet-600 text-white
                                @else bg-gradient-to-br from-gray-400 to-gray-600 text-white @endif">
                                <i class="fas fa-{{ $activity['icon'] }} text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0 space-y-1">
                                <p class="text-sm font-medium text-gray-900 leading-relaxed">{{ $activity['message'] }}</p>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-clock text-gray-400 text-xs"></i>
                                    <p class="text-xs text-gray-500 font-medium">{{ $activity['time']->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4 float-animation">
                            <i class="fas fa-activity text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-600 font-semibold text-lg">No recent activities</p>
                        <p class="text-sm text-gray-500 mt-2">Activity feed will show here</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Additional Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Low Stock Alerts -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-red-50 to-orange-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Low Stock Alerts</h3>
                    </div>
                    <a href="{{ route('inventory.lowStockAlerts') }}" class="btn-modern bg-gradient-to-r from-red-500 to-orange-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:shadow-lg transition-all duration-300">
                        View all <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
            <div class="p-6">
                @if(isset($low_stock_products) && $low_stock_products->count() > 0)
                    <div class="space-y-4">
                        @foreach($low_stock_products->take(4) as $product)
                        <div class="group flex items-center justify-between p-4 bg-gradient-to-r from-red-50 to-orange-50/50 rounded-xl border border-red-100 hover:shadow-lg transition-all duration-300 hover:border-red-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="fas fa-exclamation-triangle text-white"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $product->name }}</p>
                                    <p class="text-sm text-red-600 font-semibold">⚠️ Low stock alert</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                        <span class="text-xs text-gray-500">Requires attention</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('products.show', $product) }}" class="btn-modern bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold">
                                View
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gradient-to-br from-green-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4 float-animation">
                            <i class="fas fa-check text-green-600 text-2xl"></i>
                        </div>
                        <p class="text-gray-600 font-semibold text-lg">All products are well stocked</p>
                        <p class="text-sm text-gray-500 mt-2">No stock alerts at this time</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-yellow-50 to-orange-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-star text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Top Selling Products</h3>
                </div>
            </div>
            <div class="p-6">
                @if(isset($top_products) && $top_products->count() > 0)
                    <div class="space-y-4">
                        @foreach($top_products->take(4) as $index => $product)
                        <div class="group flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-orange-50/30 rounded-xl hover:shadow-lg transition-all duration-300 border border-yellow-100 hover:border-orange-200">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-star text-white"></i>
                                    </div>
                                    <div class="absolute -top-2 -right-2 w-6 h-6 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">{{ $index + 1 }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $product->name }}</p>
                                    <div class="flex items-center space-x-3 mt-1">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-chart-line text-green-500 text-xs"></i>
                                            <span class="text-sm text-green-600 font-semibold">{{ $product->total_sold ?? 0 }} sold</span>
                                        </div>
                                        <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                                        <span class="text-xs text-gray-500">Top performer</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('products.show', $product) }}" class="btn-modern bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold">
                                View
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4 float-animation">
                            <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-600 font-semibold text-lg">No sales data available</p>
                        <p class="text-sm text-gray-500 mt-2">Sales analytics will appear here</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-8 overflow-hidden">
        <div class="flex items-center space-x-3 mb-8">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-bolt text-white"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Quick Actions</h3>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
            <a href="{{ route('products.create') }}" class="group flex flex-col items-center p-3 sm:p-4 lg:p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl sm:rounded-2xl hover:shadow-xl transition-all duration-300 border border-blue-100 hover:border-blue-300 card-3d touch-target min-h-[120px] sm:min-h-[140px]">
                <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl sm:rounded-2xl flex items-center justify-center mb-2 sm:mb-3 lg:mb-4 group-hover:scale-110 transition-transform duration-300 glow-blue">
                    <i class="fas fa-plus text-white text-lg sm:text-xl lg:text-2xl"></i>
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors text-center">Add Product</span>
                <span class="text-xs text-gray-500 mt-1 text-center mobile-hide sm:block">Create new inventory item</span>
            </a>
            
            <a href="{{ route('orders.create') }}" class="group flex flex-col items-center p-3 sm:p-4 lg:p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl sm:rounded-2xl hover:shadow-xl transition-all duration-300 border border-green-100 hover:border-green-300 card-3d touch-target min-h-[120px] sm:min-h-[140px]">
                <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl sm:rounded-2xl flex items-center justify-center mb-2 sm:mb-3 lg:mb-4 group-hover:scale-110 transition-transform duration-300 glow-green">
                    <i class="fas fa-shopping-cart text-white text-lg sm:text-xl lg:text-2xl"></i>
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-green-600 transition-colors text-center">New Order</span>
                <span class="text-xs text-gray-500 mt-1 text-center mobile-hide sm:block">Process customer sale</span>
            </a>
            
            <a href="{{ route('customers.create') }}" class="group flex flex-col items-center p-3 sm:p-4 lg:p-6 bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl sm:rounded-2xl hover:shadow-xl transition-all duration-300 border border-purple-100 hover:border-purple-300 card-3d touch-target min-h-[120px] sm:min-h-[140px]">
                <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl sm:rounded-2xl flex items-center justify-center mb-2 sm:mb-3 lg:mb-4 group-hover:scale-110 transition-transform duration-300 glow-purple">
                    <i class="fas fa-user-plus text-white text-lg sm:text-xl lg:text-2xl"></i>
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-purple-600 transition-colors text-center">Add Customer</span>
                <span class="text-xs text-gray-500 mt-1 text-center mobile-hide sm:block">Register new client</span>
            </a>
            
            <a href="{{ route('vendors.create') }}" class="group flex flex-col items-center p-3 sm:p-4 lg:p-6 bg-gradient-to-br from-orange-50 to-red-50 rounded-xl sm:rounded-2xl hover:shadow-xl transition-all duration-300 border border-orange-100 hover:border-orange-300 card-3d touch-target min-h-[120px] sm:min-h-[140px]">
                <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl sm:rounded-2xl flex items-center justify-center mb-2 sm:mb-3 lg:mb-4 group-hover:scale-110 transition-transform duration-300 glow-orange">
                    <i class="fas fa-building text-white text-lg sm:text-xl lg:text-2xl"></i>
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-orange-600 transition-colors text-center">Add Vendor</span>
                <span class="text-xs text-gray-500 mt-1 text-center mobile-hide sm:block">Onboard supplier</span>
            </a>
        </div>
        
        <!-- Additional Quick Actions Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6 mt-4 sm:mt-6 lg:mt-8">
            <a href="{{ route('reports.index') }}" class="group flex items-center p-4 bg-gradient-to-r from-cyan-50 to-blue-50 rounded-xl hover:shadow-lg transition-all duration-300 border border-cyan-100 hover:border-cyan-300">
                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-bar text-white"></i>
                </div>
                <div>
                    <span class="text-sm font-bold text-gray-900 group-hover:text-cyan-600 transition-colors">View Reports</span>
                    <p class="text-xs text-gray-500">Analytics & insights</p>
                </div>
            </a>
            
            <a href="{{ route('billing.quickSale') }}" class="group flex items-center p-4 bg-gradient-to-r from-pink-50 to-rose-50 rounded-xl hover:shadow-lg transition-all duration-300 border border-pink-100 hover:border-pink-300">
                <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-cash-register text-white"></i>
                </div>
                <div>
                    <span class="text-sm font-bold text-gray-900 group-hover:text-pink-600 transition-colors">Quick Sale</span>
                    <p class="text-xs text-gray-500">Fast checkout</p>
                </div>
            </a>
            
            <a href="{{ route('inventory.index') }}" class="group flex items-center p-4 bg-gradient-to-r from-teal-50 to-cyan-50 rounded-xl hover:shadow-lg transition-all duration-300 border border-teal-100 hover:border-teal-300">
                <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-warehouse text-white"></i>
                </div>
                <div>
                    <span class="text-sm font-bold text-gray-900 group-hover:text-teal-600 transition-colors">Inventory</span>
                    <p class="text-xs text-gray-500">Stock management</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection