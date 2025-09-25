@extends('layouts.super-admin')

@section('title', 'System Settings')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">System Settings</h1>
            <p class="text-gray-600 text-sm sm:text-base">Configure system-wide settings and preferences</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2">{{ $stats['total_users'] }}</p>
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
                    <p class="text-2xl sm:text-3xl font-bold text-green-600 mt-1 sm:mt-2">{{ $stats['active_users'] }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-user-check text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Branches</p>
                    <p class="text-2xl sm:text-3xl font-bold text-purple-600 mt-1 sm:mt-2">{{ $stats['total_branches'] }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-building text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Products</p>
                    <p class="text-2xl sm:text-3xl font-bold text-orange-600 mt-1 sm:mt-2">{{ $stats['total_products'] }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-box text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="super-admin-card p-4 sm:p-6 rounded-xl">
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">General Settings</h2>
            <p class="text-gray-600 text-sm sm:text-base mt-1">Configure basic system settings</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium">Please correct the following errors:</h3>
                        <ul class="mt-2 text-sm list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Company Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Company Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="company_name" 
                           name="company_name" 
                           value="{{ old('company_name', $existingSettings['company_name'] ?? 'Day2Day Business') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           required>
                </div>

                <div>
                    <label for="company_email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Company Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="company_email" 
                           name="company_email" 
                           value="{{ old('company_email', $existingSettings['company_email'] ?? 'admin@day2day.com') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           required>
                </div>

                <div>
                    <label for="company_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                        Company Phone
                    </label>
                    <input type="text" 
                           id="company_phone" 
                           name="company_phone" 
                           value="{{ old('company_phone', $existingSettings['company_phone'] ?? '+1-234-567-8900') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <div>
                    <label for="default_currency" class="block text-sm font-semibold text-gray-700 mb-2">
                        Default Currency <span class="text-red-500">*</span>
                    </label>
                    <select id="default_currency" 
                            name="default_currency" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            required>
                        <option value="USD" {{ old('default_currency', $existingSettings['default_currency'] ?? 'USD') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="EUR" {{ old('default_currency', $existingSettings['default_currency'] ?? 'USD') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="GBP" {{ old('default_currency', $existingSettings['default_currency'] ?? 'USD') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                        <option value="INR" {{ old('default_currency', $existingSettings['default_currency'] ?? 'USD') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="company_address" class="block text-sm font-semibold text-gray-700 mb-2">
                    Company Address
                </label>
                <textarea id="company_address" 
                          name="company_address" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                          placeholder="Enter company address">{{ old('company_address', $existingSettings['company_address'] ?? '123 Business Street, City, State 12345') }}</textarea>
            </div>

            <!-- Business Settings -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Settings</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="tax_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                            Default Tax Rate (%)
                        </label>
                        <input type="number" 
                               id="tax_rate" 
                               name="tax_rate" 
                               value="{{ old('tax_rate', $existingSettings['tax_rate'] ?? '8.5') }}"
                               min="0" 
                               max="100" 
                               step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div>
                        <label for="low_stock_threshold" class="block text-sm font-semibold text-gray-700 mb-2">
                            Low Stock Threshold
                        </label>
                        <input type="number" 
                               id="low_stock_threshold" 
                               name="low_stock_threshold" 
                               value="{{ old('low_stock_threshold', $existingSettings['low_stock_threshold'] ?? '10') }}"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Settings</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="auto_approve_orders" 
                               name="auto_approve_orders" 
                               value="1"
                               {{ old('auto_approve_orders', $existingSettings['auto_approve_orders'] ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="auto_approve_orders" class="ml-3 text-sm font-medium text-gray-700">
                            Auto-approve orders below threshold
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="email_notifications" 
                               name="email_notifications" 
                               value="1"
                               {{ old('email_notifications', $existingSettings['email_notifications'] ?? true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="email_notifications" class="ml-3 text-sm font-medium text-gray-700">
                            Enable email notifications
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="sms_notifications" 
                               name="sms_notifications" 
                               value="1"
                               {{ old('sms_notifications', $existingSettings['sms_notifications'] ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="sms_notifications" class="ml-3 text-sm font-medium text-gray-700">
                            Enable SMS notifications
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t">
                <button type="submit" 
                        class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Save Settings
                </button>
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 text-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <a href="{{ route('admin.security') }}" 
           class="super-admin-card p-6 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Security Settings</h3>
                    <p class="text-gray-600 text-sm">Manage user permissions and security</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.analytics') }}" 
           class="super-admin-card p-6 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-chart-line text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Analytics</h3>
                    <p class="text-gray-600 text-sm">View system analytics and reports</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.users.index') }}" 
           class="super-admin-card p-6 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">User Management</h3>
                    <p class="text-gray-600 text-sm">Manage system users and roles</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection