@extends('layouts.app')

@section('title', 'Search Customers')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6 lg:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Search Customers</h1>
        <a href="{{ route('customers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm sm:text-base touch-target">
            <i class="fas fa-plus mr-1 sm:mr-2"></i>
            <span class="hidden sm:inline">Add New Customer</span>
            <span class="sm:hidden">Add Customer</span>
        </a>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('cashier.customers.search') }}" class="flex flex-col sm:flex-row gap-3 sm:gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Customers</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 touch-target"
                       placeholder="Search by name, email, or phone">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg w-full sm:w-auto touch-target">
                    <i class="fas fa-search mr-2"></i>
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    @if(request('search'))
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Search Results for "{{ request('search') }}"
                <span class="text-sm font-normal text-gray-500">({{ $customers->total() }} found)</span>
            </h2>

            @if($customers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($customers as $customer)
                        <div class="bg-gray-50 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow border">
                            <div class="p-4 sm:p-6">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-blue-600 flex items-center justify-center">
                                            <span class="text-white font-bold text-sm sm:text-lg">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">{{ $customer->name }}</h3>
                                        <p class="text-xs sm:text-sm text-gray-500 truncate">{{ $customer->email ?: 'No email' }}</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-phone w-4 mr-2"></i>
                                        <span>{{ $customer->phone }}</span>
                                    </div>
                                    @if($customer->address)
                                        <div class="flex items-start text-sm text-gray-600">
                                            <i class="fas fa-map-marker-alt w-4 mr-2 mt-0.5"></i>
                                            <span class="line-clamp-2">{{ $customer->address }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-tag w-4 mr-2"></i>
                                        <span class="capitalize">{{ str_replace('_', ' ', $customer->customer_type) }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-2">
                                    <a href="{{ route('customers.show', $customer) }}" 
                                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center font-medium py-2 px-3 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        View Details
                                    </a>
                                    <a href="{{ route('cashier.orders.create', ['customer_id' => $customer->id]) }}" 
                                       class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center font-medium py-2 px-3 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-shopping-cart mr-1"></i>
                                        New Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($customers->hasPages())
                    <div class="mt-6">
                        {{ $customers->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <i class="fas fa-search text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No customers found</h3>
                    <p class="text-gray-500">Try adjusting your search terms or create a new customer.</p>
                </div>
            @endif
        </div>
    @else
        <!-- Initial state - show instructions -->
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Search for Customers</h3>
            <p class="text-gray-500 mb-6">Enter a customer's name, email, or phone number to find them quickly.</p>
            <div class="text-sm text-gray-400">
                <p>You can search by:</p>
                <ul class="mt-2 space-y-1">
                    <li>• Customer name</li>
                    <li>• Email address</li>
                    <li>• Phone number</li>
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection