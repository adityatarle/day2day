@extends('layouts.super-admin')

@section('title', 'Security Settings')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Security Settings</h1>
            <p class="text-gray-600 text-sm sm:text-base">Manage user permissions, roles, and security policies</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('admin.settings') }}" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Settings
            </a>
        </div>
    </div>

    <!-- Security Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2">{{ $users->count() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-users text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Active Users</p>
                    <p class="text-2xl sm:text-3xl font-bold text-green-600 mt-1 sm:mt-2">{{ $users->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-user-check text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">User Roles</p>
                    <p class="text-2xl sm:text-3xl font-bold text-purple-600 mt-1 sm:mt-2">{{ $roles->count() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-user-shield text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Inactive Users</p>
                    <p class="text-2xl sm:text-3xl font-bold text-red-600 mt-1 sm:mt-2">{{ $users->where('is_active', false)->count() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-user-times text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- User Roles Management -->
    <div class="super-admin-card p-4 sm:p-6 rounded-xl">
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">User Roles</h2>
            <p class="text-gray-600 text-sm sm:text-base mt-1">Manage system roles and permissions</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user-shield text-white text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $role->users_count ?? $role->users->count() }} users
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $role->description ?? 'No description available' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-users mr-1"></i>
                                View Users
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent User Activity -->
    <div class="super-admin-card p-4 sm:p-6 rounded-xl">
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Recent User Activity</h2>
            <p class="text-gray-600 text-sm sm:text-base mt-1">Monitor recent user logins and activities</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users->take(10) as $user)
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
                            {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Never' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Security Policies -->
    <div class="super-admin-card p-4 sm:p-6 rounded-xl">
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Security Policies</h2>
            <p class="text-gray-600 text-sm sm:text-base mt-1">Configure security policies and access controls</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Password Policy</h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="require_strong_passwords" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                        <label for="require_strong_passwords" class="ml-3 text-sm font-medium text-gray-700">
                            Require strong passwords (8+ characters)
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="password_expiry" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="password_expiry" class="ml-3 text-sm font-medium text-gray-700">
                            Enable password expiry (90 days)
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="two_factor_auth" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="two_factor_auth" class="ml-3 text-sm font-medium text-gray-700">
                            Enable two-factor authentication
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Session Management</h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="auto_logout" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                        <label for="auto_logout" class="ml-3 text-sm font-medium text-gray-700">
                            Auto-logout after inactivity (30 minutes)
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="single_session" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="single_session" class="ml-3 text-sm font-medium text-gray-700">
                            Allow only one session per user
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="login_notifications" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                        <label for="login_notifications" class="ml-3 text-sm font-medium text-gray-700">
                            Send login notifications
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t">
            <button type="button" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-save mr-2"></i>
                Save Security Policies
            </button>
        </div>
    </div>
</div>
@endsection