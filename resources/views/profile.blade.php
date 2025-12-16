@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="p-6 space-y-6 bg-gray-50 min-h-screen">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ Auth::user()->name }}</h1>
                    <p class="text-gray-600">{{ Auth::user()->role->display_name ?? 'User' }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ Auth::user()->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    <div class="w-2 h-2 rounded-full mr-2 {{ Auth::user()->is_active ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    {{ Auth::user()->is_active ? 'Active' : 'Inactive' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                Personal Information
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Full Name</label>
                    <p class="text-gray-900 mt-1">{{ Auth::user()->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Email Address</label>
                    <p class="text-gray-900 mt-1">{{ Auth::user()->email }}</p>
                </div>
                @if(Auth::user()->phone)
                <div>
                    <label class="text-sm font-medium text-gray-500">Phone Number</label>
                    <p class="text-gray-900 mt-1">{{ Auth::user()->phone }}</p>
                </div>
                @endif
                @if(Auth::user()->address)
                <div>
                    <label class="text-sm font-medium text-gray-500">Address</label>
                    <p class="text-gray-900 mt-1">{{ Auth::user()->address }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                Account Information
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Role</label>
                    <p class="text-gray-900 mt-1">{{ Auth::user()->role->display_name ?? 'N/A' }}</p>
                </div>
                @if(Auth::user()->branch)
                <div>
                    <label class="text-sm font-medium text-gray-500">Branch</label>
                    <p class="text-gray-900 mt-1">{{ Auth::user()->branch->name }}</p>
                    <p class="text-sm text-gray-500 mt-1">Code: {{ Auth::user()->branch->code }}</p>
                </div>
                @endif
                @if(Auth::user()->last_login_at)
                <div>
                    <label class="text-sm font-medium text-gray-500">Last Login</label>
                    <p class="text-gray-900 mt-1">{{ Auth::user()->last_login_at->format('M d, Y H:i') }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->last_login_at->diffForHumans() }}</p>
                </div>
                @endif
                <div>
                    <label class="text-sm font-medium text-gray-500">Account Created</label>
                    <p class="text-gray-900 mt-1">{{ Auth::user()->created_at->format('M d, Y') }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Boy Specific Stats (if applicable) -->
    @if(Auth::user()->isDeliveryBoy())
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-motorcycle mr-2 text-blue-600"></i>
            Delivery Statistics
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $totalDeliveries = \App\Models\Delivery::where('delivery_boy_id', Auth::id())->count();
                $completedDeliveries = \App\Models\Delivery::where('delivery_boy_id', Auth::id())
                    ->where('status', 'delivered')
                    ->count();
                $pendingDeliveries = \App\Models\Delivery::where('delivery_boy_id', Auth::id())
                    ->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])
                    ->count();
            @endphp
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">Total Deliveries</p>
                        <p class="text-2xl font-bold text-blue-900 mt-1">{{ $totalDeliveries }}</p>
                    </div>
                    <i class="fas fa-box text-blue-400 text-2xl"></i>
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">Completed</p>
                        <p class="text-2xl font-bold text-green-900 mt-1">{{ $completedDeliveries }}</p>
                    </div>
                    <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-orange-600">Pending</p>
                        <p class="text-2xl font-bold text-orange-900 mt-1">{{ $pendingDeliveries }}</p>
                    </div>
                    <i class="fas fa-clock text-orange-400 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

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
            <a href="{{ route('settings') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300">
                <i class="fas fa-cog mr-2"></i>
                Settings
            </a>
        </div>
    </div>
</div>
@endsection
