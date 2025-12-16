@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="p-6 space-y-6 bg-gray-50 min-h-screen">
    <!-- Settings Header -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Settings</h1>
                <p class="text-gray-600 mt-1">Manage your account settings and preferences</p>
            </div>
            <a href="{{ route('profile') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Profile
            </a>
        </div>
    </div>

    <!-- Account Settings -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-user-cog mr-2 text-blue-600"></i>
            Account Settings
        </h2>
        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <div class="flex items-center justify-between">
                    <p class="text-gray-900">{{ Auth::user()->email }}</p>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Cannot be changed</span>
                </div>
            </div>
            
            <div class="border-b border-gray-200 pb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2 {{ Auth::user()->is_active ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        <span class="text-gray-900">{{ Auth::user()->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <span class="text-xs text-gray-500">Contact administrator to change</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Preferences -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-bell mr-2 text-blue-600"></i>
            Notification Preferences
        </h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email Notifications</label>
                    <p class="text-sm text-gray-500 mt-1">Receive email notifications for important updates</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-gray-700">SMS Notifications</label>
                    <p class="text-sm text-gray-500 mt-1">Receive SMS notifications for urgent updates</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- Delivery Boy Specific Settings -->
    @if(Auth::user()->isDeliveryBoy())
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-motorcycle mr-2 text-blue-600"></i>
            Delivery Settings
        </h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Location Sharing</label>
                    <p class="text-sm text-gray-500 mt-1">Share your location for real-time delivery tracking</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Auto-Update Status</label>
                    <p class="text-sm text-gray-500 mt-1">Automatically update delivery status when near destination</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>
    </div>
    @endif

    <!-- Security -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-shield-alt mr-2 text-blue-600"></i>
            Security
        </h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Change Password</label>
                <p class="text-sm text-gray-500 mb-3">To change your password, please contact your administrator.</p>
                <button disabled class="bg-gray-100 text-gray-400 px-4 py-2 rounded-md text-sm font-medium cursor-not-allowed">
                    <i class="fas fa-lock mr-2"></i>
                    Change Password
                </button>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Two-Factor Authentication</label>
                <p class="text-sm text-gray-500 mb-3">Add an extra layer of security to your account.</p>
                <button disabled class="bg-gray-100 text-gray-400 px-4 py-2 rounded-md text-sm font-medium cursor-not-allowed">
                    <i class="fas fa-mobile-alt mr-2"></i>
                    Enable 2FA
                </button>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            @if(Auth::user()->isDeliveryBoy())
            <a href="{{ route('delivery.dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                <i class="fas fa-motorcycle mr-2"></i>
                Back to Dashboard
            </a>
            @else
            <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
            @endif
            <a href="{{ route('profile') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300">
                <i class="fas fa-user mr-2"></i>
                View Profile
            </a>
        </div>
    </div>
</div>
@endsection
