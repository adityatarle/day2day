@extends('layouts.app')

@section('title', 'Customer Details - ' . $customer->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('customers.index') }}" class="text-gray-600 hover:text-gray-800 transition-colors">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Customer Details</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Customer Information Card -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Customer Information</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('customers.edit', $customer) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this customer?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-blue-600 flex items-center justify-center">
                            <span class="text-white font-bold text-2xl">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h3>
                            <p class="text-gray-500">Customer ID: #{{ $customer->id }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email Address</label>
                            <p class="text-gray-900">{{ $customer->email ?: 'Not provided' }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone Number</label>
                            <p class="text-gray-900">{{ $customer->phone }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Address</label>
                            <p class="text-gray-900">{{ $customer->address ?: 'Not provided' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Customer Type</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ ucwords(str_replace('_', ' ', $customer->customer_type)) }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Credit Limit</label>
                        <p class="text-gray-900">₹{{ number_format($customer->credit_limit, 2) }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Credit Days</label>
                        <p class="text-gray-900">{{ $customer->credit_days }} days</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Member Since</label>
                        <p class="text-gray-900">{{ $customer->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Orders</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $orderCount }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Spent</span>
                        <span class="text-2xl font-bold text-green-600">₹{{ number_format($totalSpent, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Average Order</span>
                        <span class="text-lg font-semibold text-gray-900">₹{{ $orderCount > 0 ? number_format($totalSpent / $orderCount, 2) : '0.00' }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i>Create Order
                    </a>
                    <a href="{{ route('customers.purchaseHistory', $customer) }}" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                        <i class="fas fa-history mr-2"></i>Purchase History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    @if($customer->orders->count() > 0)
    <div class="mt-8 bg-white rounded-lg shadow-sm border p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Recent Orders</h2>
            @if($customer->orders->count() >= 10)
            <a href="{{ route('customers.purchaseHistory', $customer) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                View All Orders
            </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customer->orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $order->order_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->orderItems->count() }} items
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($order->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="mt-8 bg-white rounded-lg shadow-sm border p-12 text-center">
        <div class="text-gray-400 mb-4">
            <i class="fas fa-shopping-cart text-6xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Orders Yet</h3>
        <p class="text-gray-500 mb-6">This customer hasn't placed any orders yet.</p>
        <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            Create First Order
        </a>
    </div>
    @endif
</div>
@endsection