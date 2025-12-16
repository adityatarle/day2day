@extends('layouts.branch-manager')

@section('title', 'Branch Manager Dashboard')

@section('content')
<div class="p-6 space-y-8 bg-gradient-to-br from-slate-50 via-green-50 to-emerald-50 min-h-screen">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-green-600 via-emerald-600 to-teal-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        </div>
        
        <div class="relative flex items-center justify-between">
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center float-animation">
                        <i class="fas fa-store text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-1 bg-gradient-to-r from-white to-green-100 bg-clip-text text-transparent">
                            {{ $branch->name }} Manager
                        </h1>
                        <p class="text-green-100 text-lg font-medium">Welcome back, {{ Auth::user()->name }}!</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6 mt-4">
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-map-marker-alt text-green-200"></i>
                        <span class="text-sm font-medium">{{ Str::limit($branch->address, 20) }}</span>
                    </div>
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-users text-blue-300"></i>
                        <span class="text-sm font-medium">{{ $stats['branch_staff'] }} Staff</span>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="text-right space-y-2">
                    <div class="text-3xl font-bold bg-gradient-to-r from-white to-green-100 bg-clip-text text-transparent">
                        {{ Carbon\Carbon::now()->format('M d, Y') }}
                    </div>
                    <div class="text-green-200 text-lg font-medium">{{ Carbon\Carbon::now()->format('l') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Branch Products -->
        <a href="{{ route('products.index') }}" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition-colors">Branch Products</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['branch_products']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-yellow-600 text-sm font-medium">{{ $inventory_alerts['low_stock'] }} Low Stock</span>
                <i class="fas fa-arrow-right text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all"></i>
            </div>
        </a>

        <!-- Branch Orders -->
        <a href="{{ route('branch.orders.index') }}" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 group-hover:text-green-600 transition-colors">Branch Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['branch_orders']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end">
                <i class="fas fa-arrow-right text-gray-400 group-hover:text-green-600 group-hover:translate-x-1 transition-all"></i>
            </div>
        </a>

        <!-- Monthly Revenue -->
        <a href="{{ route('reports.index') }}" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 group-hover:text-purple-600 transition-colors">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-gray-900">₹{{ number_format($stats['monthly_revenue'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-gray-600 text-sm">Total: ₹{{ number_format($stats['branch_revenue'], 2) }}</span>
                <i class="fas fa-arrow-right text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all"></i>
            </div>
        </a>

        <!-- Branch Customers -->
        <a href="{{ route('branch.customers.index') }}" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 group-hover:text-orange-600 transition-colors">Branch Customers</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['branch_customers']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <i class="fas fa-users text-orange-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end">
                <i class="fas fa-arrow-right text-gray-400 group-hover:text-orange-600 group-hover:translate-x-1 transition-all"></i>
            </div>
        </a>
    </div>

    <!-- Purchase Entries Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pending Purchase Entries -->
        <a href="{{ route('enhanced-purchase-entries.index', ['status' => 'pending']) }}" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 group-hover:text-yellow-600 transition-colors">Pending Receipts</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['pending_purchase_orders'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-yellow-600 text-sm font-medium">Awaiting delivery</span>
                <i class="fas fa-arrow-right text-gray-400 group-hover:text-yellow-600 group-hover:translate-x-1 transition-all"></i>
            </div>
        </a>

        <!-- Purchase Entries Ready for Receipt -->
        <a href="{{ route('enhanced-purchase-entries.index', ['status' => 'approved']) }}" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition-colors">Ready for Receipt</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['approved_orders'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <i class="fas fa-truck text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-blue-600 text-sm font-medium">Can record receipt</span>
                <i class="fas fa-arrow-right text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all"></i>
            </div>
        </a>

        <!-- Completed Receipts -->
        <a href="{{ route('enhanced-purchase-entries.index', ['status' => 'fulfilled']) }}" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 group-hover:text-green-600 transition-colors">Completed Receipts</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['fulfilled_orders'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-green-600 text-sm font-medium">Fully received</span>
                <i class="fas fa-arrow-right text-gray-400 group-hover:text-green-600 group-hover:translate-x-1 transition-all"></i>
            </div>
        </a>

        <!-- Partial Receipts -->
        <a href="{{ route('enhanced-purchase-entries.index', ['status' => 'partial']) }}" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 group-hover:text-orange-600 transition-colors">Partial Receipts</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['partial_receipts'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-orange-600 text-sm font-medium">Partially received</span>
                <i class="fas fa-arrow-right text-gray-400 group-hover:text-orange-600 group-hover:translate-x-1 transition-all"></i>
            </div>
        </a>
    </div>

    <!-- Staff Performance and Recent Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Staff Performance -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Staff Performance</h3>
                <a href="{{ route('branch.staff.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    Manage Staff
                </a>
            </div>
            <div class="space-y-4">
                @foreach($staff_performance as $staff)
                <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $staff['name'] }}</h4>
                            <p class="text-sm text-gray-600">{{ $staff['role'] }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">{{ $staff['monthly_orders'] }} orders</p>
                        <p class="text-sm text-gray-600">Last: {{ $staff['last_login'] }}</p>
                        <span class="px-2 py-1 text-xs rounded-full {{ $staff['status'] == 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $staff['status'] }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Recent Branch Orders</h3>
                <a href="{{ route('branch.orders.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    View All
                </a>
            </div>
            <div class="space-y-4">
                @foreach($recent_orders->take(8) as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <i class="fas fa-receipt text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 group-hover:text-blue-600">#{{ $order->id }}</p>
                            <p class="text-sm text-gray-600">{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                        </div>
                    </div>
                    <div class="text-right flex items-center space-x-3">
                        <div>
                            <p class="font-semibold text-gray-900">₹{{ number_format($order->total_amount, 2) }}</p>
                            <p class="text-sm text-gray-600">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600 transition-colors"></i>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Purchase Entries and Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Purchase Entries with Detailed Tracking -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Purchase Order Tracking</h3>
                <a href="{{ route('enhanced-purchase-entries.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    View All
                </a>
            </div>
            <div class="space-y-4">
                @forelse($recent_purchase_entries as $entry)
                <a href="{{ route('enhanced-purchase-entries.show', $entry) }}" class="block p-4 rounded-lg hover:bg-gray-50 transition-colors border border-gray-100 cursor-pointer group">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                                <i class="fas fa-truck text-indigo-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $entry->po_number }}</p>
                                <p class="text-sm text-gray-600">{{ $entry->vendor ? $entry->vendor->name : 'Admin' }}</p>
                            </div>
                        </div>
                        <div class="text-right flex items-center space-x-2">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $entry->completion_percentage >= 100 ? 'bg-green-100 text-green-800' : 
                                       ($entry->completion_percentage > 0 ? 'bg-orange-100 text-orange-800' : 
                                       'bg-gray-100 text-gray-800') }}">
                                    {{ $entry->completion_percentage >= 100 ? 'Complete' : 
                                       ($entry->completion_percentage > 0 ? 'Partial' : 'Pending') }}
                                </span>
                                <p class="text-sm text-gray-600 mt-1">{{ $entry->created_at->diffForHumans() }}</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-2">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>Progress</span>
                            <span>{{ number_format($entry->completion_percentage, 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ min(100, $entry->completion_percentage) }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Quantity Details -->
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div class="text-center">
                            <p class="text-gray-600">Expected</p>
                            <p class="font-semibold text-gray-900">{{ number_format($entry->total_expected, 0) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-600">Received</p>
                            <p class="font-semibold text-green-600">{{ number_format($entry->total_received, 0) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-600">Remaining</p>
                            <p class="font-semibold {{ $entry->total_remaining > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                {{ number_format($entry->total_remaining, 0) }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Receipt Count -->
                    <div class="mt-2 text-center">
                        <span class="text-xs text-gray-500">{{ $entry->receipt_count }} receipt{{ $entry->receipt_count !== 1 ? 's' : '' }}</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-4">
                    <p class="text-gray-500 text-sm">No purchase orders yet</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Top Branch Products -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Top Selling Products</h3>
                <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    View All
                </a>
            </div>
            <div class="space-y-4">
                @foreach($top_products->take(6) as $product)
                <a href="{{ route('products.show', $product) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer group">
                    <div>
                        <h4 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $product->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $product->sku }}</p>
                    </div>
                    <div class="text-right flex items-center space-x-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $product->total_sold }} sold</p>
                            <p class="text-sm text-gray-600">₹{{ number_format($product->selling_price, 2) }}</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Inventory Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">

        <!-- Branch Inventory Alerts -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Branch Inventory Alerts</h3>
                <a href="{{ route('branch.inventory.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    View All Inventory
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('branch.inventory.index', ['filter' => 'low_stock']) }}" class="flex items-center justify-between p-4 rounded-lg bg-yellow-50 border border-yellow-200 hover:bg-yellow-100 hover:border-yellow-300 transition-all duration-300 cursor-pointer group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Low Stock Items</h4>
                            <p class="text-sm text-gray-600">Items below threshold</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl font-bold text-yellow-600">{{ $inventory_alerts['low_stock'] }}</span>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-yellow-600 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </a>

                <a href="{{ route('branch.inventory.index', ['filter' => 'out_of_stock']) }}" class="flex items-center justify-between p-4 rounded-lg bg-red-50 border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-300 cursor-pointer group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition-colors">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Out of Stock</h4>
                            <p class="text-sm text-gray-600">Items completely out</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl font-bold text-red-600">{{ $inventory_alerts['out_of_stock'] }}</span>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-red-600 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </a>

                <a href="{{ route('branch.inventory.index', ['filter' => 'expiring_soon']) }}" class="flex items-center justify-between p-4 rounded-lg bg-orange-50 border border-orange-200 hover:bg-orange-100 hover:border-orange-300 transition-all duration-300 cursor-pointer group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                            <i class="fas fa-clock text-orange-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Expiring Soon</h4>
                            <p class="text-sm text-gray-600">Items expiring in 7 days</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl font-bold text-orange-600">{{ $inventory_alerts['expiring_soon'] }}</span>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-orange-600 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="bg-white rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Branch Financial Summary</h3>
            <a href="{{ route('reports.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                View Reports
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <a href="{{ route('reports.index', ['type' => 'sales']) }}" class="text-center p-4 rounded-lg bg-green-50 border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-300 cursor-pointer group">
                <div class="text-2xl font-bold text-green-600">₹{{ number_format($financial_summary['total_sales'], 2) }}</div>
                <div class="text-sm text-gray-600 mt-1 group-hover:text-green-700">Total Sales</div>
            </a>
            <a href="{{ route('reports.index', ['type' => 'sales', 'period' => 'monthly']) }}" class="text-center p-4 rounded-lg bg-blue-50 border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-all duration-300 cursor-pointer group">
                <div class="text-2xl font-bold text-blue-600">₹{{ number_format($financial_summary['monthly_sales'], 2) }}</div>
                <div class="text-sm text-gray-600 mt-1 group-hover:text-blue-700">Monthly Sales</div>
            </a>
            <a href="{{ route('enhanced-purchase-entries.index') }}" class="text-center p-4 rounded-lg bg-purple-50 border border-purple-200 hover:bg-purple-100 hover:border-purple-300 transition-all duration-300 cursor-pointer group">
                <div class="text-2xl font-bold text-purple-600">₹{{ number_format($financial_summary['total_purchases'], 2) }}</div>
                <div class="text-sm text-gray-600 mt-1 group-hover:text-purple-700">Total Purchases</div>
            </a>
            <a href="{{ route('reports.index', ['type' => 'expenses']) }}" class="text-center p-4 rounded-lg bg-orange-50 border border-orange-200 hover:bg-orange-100 hover:border-orange-300 transition-all duration-300 cursor-pointer group">
                <div class="text-2xl font-bold text-orange-600">₹{{ number_format($financial_summary['monthly_expenses'], 2) }}</div>
                <div class="text-sm text-gray-600 mt-1 group-hover:text-orange-700">Monthly Expenses</div>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('products.create') }}" class="bg-gradient-to-r from-green-500 to-green-600 rounded-2xl p-6 text-white hover:from-green-600 hover:to-green-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Add Product</h4>
                    <p class="text-green-100 text-sm">Create new product</p>
                </div>
            </div>
        </a>

        <a href="{{ route('pos.index') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-white hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">POS System</h4>
                    <p class="text-blue-100 text-sm">Point of sale</p>
                </div>
            </div>
        </a>

        <a href="{{ route('branch.inventory.index') }}" class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-6 text-white hover:from-purple-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-boxes text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Inventory</h4>
                    <p class="text-purple-100 text-sm">Manage stock</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.index') }}" class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl p-6 text-white hover:from-orange-600 hover:to-orange-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-bar text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Reports</h4>
                    <p class="text-orange-100 text-sm">Branch analytics</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Additional Quick Actions Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('enhanced-purchase-entries.index') }}" class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white hover:from-indigo-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-truck-loading text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Purchase Entries</h4>
                    <p class="text-indigo-100 text-sm">Track deliveries & quantities</p>
                </div>
            </div>
        </a>

        <a href="{{ route('branch.product-orders.index') }}" class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-2xl p-6 text-white hover:from-teal-600 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Product Orders</h4>
                    <p class="text-teal-100 text-sm">Order from admin</p>
                </div>
            </div>
        </a>

        <a href="{{ route('branch.staff.index') }}" class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-2xl p-6 text-white hover:from-pink-600 hover:to-pink-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Staff Management</h4>
                    <p class="text-pink-100 text-sm">Manage team</p>
                </div>
            </div>
        </a>

        <a href="{{ route('branch.purchase-entries.discrepancy-report') }}" class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-6 text-white hover:from-amber-600 hover:to-amber-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Discrepancy Report</h4>
                    <p class="text-amber-100 text-sm">Track losses</p>
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