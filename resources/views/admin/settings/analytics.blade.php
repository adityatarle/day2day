@extends('layouts.super-admin')

@section('title', 'System Analytics')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">System Analytics</h1>
            <p class="text-gray-600 text-sm sm:text-base">Monitor system performance and business metrics</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('admin.settings') }}" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Settings
            </a>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-6">
        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Sales</p>
                    <p class="text-lg sm:text-xl font-bold text-green-600 mt-1 sm:mt-2">${{ number_format($stats['total_sales'], 2) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-dollar-sign text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Orders</p>
                    <p class="text-lg sm:text-xl font-bold text-blue-600 mt-1 sm:mt-2">{{ number_format($stats['total_orders']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-shopping-cart text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Customers</p>
                    <p class="text-lg sm:text-xl font-bold text-purple-600 mt-1 sm:mt-2">{{ number_format($stats['total_customers']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-users text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Products</p>
                    <p class="text-lg sm:text-xl font-bold text-orange-600 mt-1 sm:mt-2">{{ number_format($stats['total_products']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-box text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Vendors</p>
                    <p class="text-lg sm:text-xl font-bold text-indigo-600 mt-1 sm:mt-2">{{ number_format($stats['total_vendors']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-truck text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Branches</p>
                    <p class="text-lg sm:text-xl font-bold text-teal-600 mt-1 sm:mt-2">{{ number_format($stats['total_branches']) }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-building text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="super-admin-card p-4 sm:p-6 rounded-xl">
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Recent Orders</h2>
            <p class="text-gray-600 text-sm sm:text-base mt-1">Latest orders across all branches</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $order->customer->name ?? 'Guest' }}</div>
                            <div class="text-sm text-gray-500">{{ $order->customer->email ?? 'No email' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $order->branch->name ?? 'No Branch' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${{ number_format($order->total_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            No recent orders found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="super-admin-card p-4 sm:p-6 rounded-xl">
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Recent Users</h2>
            <p class="text-gray-600 text-sm sm:text-base mt-1">Latest user registrations and activities</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentUsers as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $user->role->name ?? 'No Role' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->branch->name ?? 'No Branch' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            No recent users found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance Metrics -->
        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Performance Metrics</h2>
                <p class="text-gray-600 text-sm sm:text-base mt-1">System performance indicators</p>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Database Response Time</span>
                    <span class="text-sm font-semibold text-green-600">~2.5ms</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: 85%"></div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Server Load</span>
                    <span class="text-sm font-semibold text-yellow-600">45%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-600 h-2 rounded-full" style="width: 45%"></div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Memory Usage</span>
                    <span class="text-sm font-semibold text-blue-600">62%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 62%"></div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Disk Usage</span>
                    <span class="text-sm font-semibold text-purple-600">38%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: 38%"></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Quick Actions</h2>
                <p class="text-gray-600 text-sm sm:text-base mt-1">Common administrative tasks</p>
            </div>

            <div class="space-y-4">
                <a href="{{ route('admin.users.index') }}" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-semibold text-gray-900">Manage Users</h3>
                        <p class="text-xs text-gray-600">Add, edit, or remove system users</p>
                    </div>
                </a>

                <a href="{{ route('admin.branches.index') }}" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-building text-white"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-semibold text-gray-900">Manage Branches</h3>
                        <p class="text-xs text-gray-600">Configure branch settings and staff</p>
                    </div>
                </a>

                <a href="{{ route('reports.index') }}" class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-chart-bar text-white"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-semibold text-gray-900">View Reports</h3>
                        <p class="text-xs text-gray-600">Generate business reports and analytics</p>
                    </div>
                </a>

                <a href="{{ route('admin.settings') }}" class="flex items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                    <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-cog text-white"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-semibold text-gray-900">System Settings</h3>
                        <p class="text-xs text-gray-600">Configure system preferences</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection