@extends('layouts.super-admin')

@section('title', 'Branch Management')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Branch Management</h1>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage all business branches, their performance, and settings.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('admin.branches.performance') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors touch-target">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="hidden xs:inline">Analytics</span>
                    <span class="xs:hidden">Stats</span>
                </a>
                <a href="{{ route('admin.branches.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors touch-target">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="hidden xs:inline">Add New Branch</span>
                    <span class="xs:hidden">Add Branch</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Branch Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 sm:p-3 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Total Branches</p>
                    <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $branches->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="bg-green-100 p-2 sm:p-3 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Active Branches</p>
                    <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $branches->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="bg-purple-100 p-2 sm:p-3 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Total Staff</p>
                    <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $branches->sum('users_count') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="bg-orange-100 p-2 sm:p-3 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-900">₹{{ number_format($branches->sum('orders_sum_total_amount'), 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Branches Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($branches as $branch)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300">
            <div class="p-4 sm:p-6">
                <!-- Branch Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-sm sm:text-base">{{ strtoupper(substr($branch->name, 0, 2)) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.branches.show', $branch) }}" class="text-base sm:text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors block truncate">
                                {{ $branch->name }}
                            </a>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1
                                {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1 ml-4">
                        <a href="{{ route('admin.branches.edit', $branch) }}"
                           class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors touch-target">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Branch Details -->
                <div class="space-y-2 sm:space-y-3">
                    <div class="flex items-start text-xs sm:text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="truncate">{{ $branch->address }}</span>
                    </div>

                    @if($branch->phone)
                    <div class="flex items-start text-xs sm:text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="truncate">{{ $branch->phone }}</span>
                    </div>
                    @endif

                    @if($branch->manager_name)
                    <div class="flex items-start text-xs sm:text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="truncate">Manager: {{ $branch->manager_name }}</span>
                    </div>
                    @endif
                </div>

                <!-- Branch Metrics -->
                <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-3 gap-2 sm:gap-4 text-center">
                        <div>
                            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ $branch->users_count ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Staff</p>
                        </div>
                        <div>
                            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ $branch->orders_count ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Orders</p>
                        </div>
                        <div>
                            <p class="text-sm sm:text-lg font-bold text-gray-900">₹{{ number_format($branch->orders_sum_total_amount ?? 0, 0) }}</p>
                            <p class="text-xs text-gray-500">Revenue</p>
                        </div>
                    </div>
                </div>

                <!-- Branch Actions -->
                <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                        <div class="flex flex-wrap gap-2 sm:gap-4">
                            <a href="{{ route('admin.branches.show', $branch) }}" class="text-green-600 hover:text-green-800 text-xs sm:text-sm font-medium touch-target">
                                View Details
                            </a>
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="text-blue-600 hover:text-blue-800 text-xs sm:text-sm font-medium touch-target">
                                Edit Branch
                            </a>
                        </div>
                        @if($branch->users_count == 0 && $branch->orders_count == 0)
                        <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 hover:text-red-800 text-xs sm:text-sm font-medium touch-target"
                                    data-confirm="Are you sure you want to delete this branch?">
                                Delete Branch
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No branches found</h3>
                <p class="text-gray-500 mb-6 text-sm sm:text-base">Get started by creating your first branch.</p>
                <a href="{{ route('admin.branches.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors touch-target">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create First Branch
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($branches->hasPages())
    <div class="mt-6 sm:mt-8">
        {{ $branches->links() }}
    </div>
    @endif
</div>
@endsection