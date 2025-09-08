@extends('layouts.app')

@section('title', 'Edit Customer - ' . $customer->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('customers.show', $customer) }}" class="text-gray-600 hover:text-gray-800 transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Edit Customer</h1>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Customer Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Customer Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                           placeholder="Enter customer name" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="mb-6">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror"
                           placeholder="Enter phone number" required>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                           placeholder="Enter email address">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="mb-6">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Address
                    </label>
                    <textarea name="address" id="address" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror"
                              placeholder="Enter customer address">{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Customer Type -->
                <div class="mb-6">
                    <label for="customer_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Customer Type <span class="text-red-500">*</span>
                    </label>
                    <select name="customer_type" id="customer_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('customer_type') border-red-500 @enderror" required>
                        <option value="">Select customer type</option>
                        <option value="walk_in" {{ old('customer_type', $customer->customer_type) == 'walk_in' ? 'selected' : '' }}>Walk-in Customer</option>
                        <option value="regular" {{ old('customer_type', $customer->customer_type) == 'regular' ? 'selected' : '' }}>Regular Customer</option>
                        <option value="regular_wholesale" {{ old('customer_type', $customer->customer_type) == 'regular_wholesale' ? 'selected' : '' }}>Regular Wholesale</option>
                        <option value="premium_wholesale" {{ old('customer_type', $customer->customer_type) == 'premium_wholesale' ? 'selected' : '' }}>Premium Wholesale</option>
                        <option value="distributor" {{ old('customer_type', $customer->customer_type) == 'distributor' ? 'selected' : '' }}>Distributor</option>
                        <option value="retailer" {{ old('customer_type', $customer->customer_type) == 'retailer' ? 'selected' : '' }}>Retailer</option>
                    </select>
                    @error('customer_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Credit Limit -->
                <div class="mb-6">
                    <label for="credit_limit" class="block text-sm font-medium text-gray-700 mb-2">
                        Credit Limit
                    </label>
                    <input type="number" name="credit_limit" id="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" 
                           step="0.01" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('credit_limit') border-red-500 @enderror"
                           placeholder="0.00">
                    @error('credit_limit')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-600 mt-1">Leave as 0 for no credit limit</p>
                </div>

                <!-- Credit Days -->
                <div class="mb-6">
                    <label for="credit_days" class="block text-sm font-medium text-gray-700 mb-2">
                        Credit Days
                    </label>
                    <input type="number" name="credit_days" id="credit_days" value="{{ old('credit_days', $customer->credit_days) }}" 
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('credit_days') border-red-500 @enderror"
                           placeholder="0">
                    @error('credit_days')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-600 mt-1">Number of days allowed for credit payment</p>
                </div>

                <!-- Customer Status -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active Customer
                        </label>
                    </div>
                    @error('is_active')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('customers.show', $customer) }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                        Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection