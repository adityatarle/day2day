@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Vendors</h1>
        <a href="{{ route('vendors.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add New Vendor
        </a>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('vendors.index') }}" class="flex gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Vendors</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Search by name, email, or phone">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Vendors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($vendors as $vendor)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-green-600 flex items-center justify-center">
                                <span class="text-white font-bold text-lg">{{ strtoupper(substr($vendor->name, 0, 1)) }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $vendor->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $vendor->email }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium">{{ $vendor->phone }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Address:</span>
                            <span class="font-medium">{{ Str::limit($vendor->address, 30) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Orders:</span>
                            <span class="font-medium text-green-600">{{ $vendor->purchase_orders_count }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Value:</span>
                            <span class="font-medium text-green-600">₹{{ number_format($vendor->purchase_orders_sum_total_amount ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Supplier since {{ $vendor->created_at->format('M Y') }}
                        </div>
                        <div class="space-x-2">
                            <a href="{{ route('vendors.show', $vendor) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                            <a href="{{ route('vendors.edit', $vendor) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No vendors found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a new vendor.</p>
                    <div class="mt-6">
                        <a href="{{ route('vendors.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Add Vendor
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($vendors->hasPages())
        <div class="mt-8">
            {{ $vendors->links() }}
        </div>
    @endif
</div>
@endsection