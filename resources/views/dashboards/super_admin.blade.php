@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="p-6 space-y-8 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-red-600 via-purple-600 to-indigo-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        </div>
        
        <div class="relative flex items-center justify-between">
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center float-animation">
                        <i class="fas fa-crown text-2xl text-yellow-300"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-1 bg-gradient-to-r from-white to-yellow-100 bg-clip-text text-transparent">
                            Super Admin Control Center
                        </h1>
                        <p class="text-blue-100 text-lg font-medium">Complete system oversight and management</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6 mt-4">
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-shield-alt text-green-300"></i>
                        <span class="text-sm font-medium">System Secure</span>
                    </div>
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-users-cog text-blue-300"></i>
                        <span class="text-sm font-medium">{{ $stats['total_users'] }} Users</span>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="text-right space-y-2">
                    <div class="text-3xl font-bold bg-gradient-to-r from-white to-yellow-100 bg-clip-text text-transparent">
                        {{ Carbon\Carbon::now()->format('M d, Y') }}
                    </div>
                    <div class="text-blue-200 text-lg font-medium">{{ Carbon\Carbon::now()->format('l') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">{{ $system_health['active_users'] }} Active</span>
                <span class="text-gray-500 ml-2">{{ $system_health['inactive_users'] }} Inactive</span>
            </div>
        </div>

        <!-- Total Branches -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Branches</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_branches']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">{{ $system_health['active_branches'] }} Active</span>
            </div>
        </div>

        <!-- System Revenue -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-3xl font-bold text-gray-900">₹{{ number_format($stats['total_revenue'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">₹{{ number_format($stats['monthly_revenue'], 2) }}</span>
                <span class="text-gray-500 ml-2">This Month</span>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- User Role Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Role Distribution Chart -->
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">User Role Distribution</h3>
            <div class="space-y-4">
                @foreach($role_distribution as $role)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <span class="text-sm font-medium text-gray-700">{{ $role['name'] }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-gray-900">{{ $role['count'] }}</span>
                        <span class="text-xs text-gray-500 ml-1">({{ $role['percentage'] }}%)</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Branch Performance -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Branch Performance Overview</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Branch</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Manager</th>
                            <th class="text-right py-3 px-2 font-semibold text-gray-700">Orders</th>
                            <th class="text-right py-3 px-2 font-semibold text-gray-700">Revenue</th>
                            <th class="text-right py-3 px-2 font-semibold text-gray-700">Staff</th>
                            <th class="text-center py-3 px-2 font-semibold text-gray-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branch_performance as $branch)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-2">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $branch['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($branch['location'], 30) }}</div>
                                </div>
                            </td>
                            <td class="py-3 px-2 text-gray-700">{{ $branch['manager'] }}</td>
                            <td class="py-3 px-2 text-right font-semibold">{{ number_format($branch['total_orders']) }}</td>
                            <td class="py-3 px-2 text-right font-semibold">₹{{ number_format($branch['total_revenue']) }}</td>
                            <td class="py-3 px-2 text-right">{{ $branch['total_staff'] }}</td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2 py-1 text-xs rounded-full {{ $branch['status'] == 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $branch['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent System Activities -->
    <div class="bg-white rounded-2xl p-6 shadow-lg">
        <h3 class="text-xl font-bold text-gray-900 mb-6">Recent System Activities</h3>
        <div class="space-y-4">
            @foreach($recent_activities->take(10) as $activity)
            <div class="flex items-center space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 rounded-full bg-{{ $activity['color'] }}-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }}-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</p>
                    <p class="text-xs text-gray-500">{{ $activity['time']->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.users.index') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-white hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users-cog text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Manage Users</h4>
                    <p class="text-blue-100 text-sm">Add, edit, or remove users</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.branches.index') }}" class="bg-gradient-to-r from-green-500 to-green-600 rounded-2xl p-6 text-white hover:from-green-600 hover:to-green-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Manage Branches</h4>
                    <p class="text-green-100 text-sm">Oversee all branch operations</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.settings') }}" class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-6 text-white hover:from-purple-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cogs text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">System Settings</h4>
                    <p class="text-purple-100 text-sm">Configure system parameters</p>
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