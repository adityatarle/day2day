@extends('layouts.app')

@section('title', 'Add New Customer')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6 lg:py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Add New Customer</h1>
            <a href="{{ route('customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg touch-target text-center text-sm sm:text-base mobile-full-width sm:w-auto">
                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
                <span class="hidden sm:inline">Back to Customers</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>

        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf

                <!-- Customer Name -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        Customer Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" 
                           class="form-input touch-target @error('name') border-red-500 @enderror"
                           placeholder="Enter customer name" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone" class="form-label">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" 
                           class="form-input touch-target @error('phone') border-red-500 @enderror"
                           placeholder="Enter phone number" required>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        Email Address
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                           class="form-input touch-target @error('email') border-red-500 @enderror"
                           placeholder="Enter email address">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address" class="form-label">
                        Address
                    </label>
                    <textarea name="address" id="address" rows="3" 
                              class="form-input touch-target @error('address') border-red-500 @enderror"
                              placeholder="Enter customer address">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Customer Type -->
                <div class="form-group">
                    <label for="customer_type" class="form-label">
                        Customer Type <span class="text-red-500">*</span>
                    </label>
                    <select name="customer_type" id="customer_type" 
                            class="form-input touch-target @error('customer_type') border-red-500 @enderror" required>
                        <option value="">Select customer type</option>
                        <option value="walk_in" {{ old('customer_type') == 'walk_in' ? 'selected' : '' }}>Walk-in Customer</option>
                        <option value="regular" {{ old('customer_type') == 'regular' ? 'selected' : '' }}>Regular Customer</option>
                        <option value="regular_wholesale" {{ old('customer_type') == 'regular_wholesale' ? 'selected' : '' }}>Regular Wholesale</option>
                        <option value="premium_wholesale" {{ old('customer_type') == 'premium_wholesale' ? 'selected' : '' }}>Premium Wholesale</option>
                        <option value="distributor" {{ old('customer_type') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                        <option value="retailer" {{ old('customer_type') == 'retailer' ? 'selected' : '' }}>Retailer</option>
                    </select>
                    @error('customer_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Credit Limit -->
                <div class="form-group">
                    <label for="credit_limit" class="form-label">
                        Credit Limit
                    </label>
                    <input type="number" name="credit_limit" id="credit_limit" value="{{ old('credit_limit', 0) }}" 
                           step="0.01" min="0"
                           class="form-input touch-target @error('credit_limit') border-red-500 @enderror"
                           placeholder="0.00">
                    @error('credit_limit')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Leave as 0 for no credit limit</p>
                </div>

                <!-- Credit Days -->
                <div class="form-group">
                    <label for="credit_days" class="form-label">
                        Credit Days
                    </label>
                    <input type="number" name="credit_days" id="credit_days" value="{{ old('credit_days', 0) }}" 
                           min="0"
                           class="form-input touch-target @error('credit_days') border-red-500 @enderror"
                           placeholder="0">
                    @error('credit_days')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Number of days allowed for credit payment</p>
                </div>

                <!-- Customer Status -->
                <div class="form-group">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded touch-target">
                        <label for="is_active" class="ml-3 block text-sm sm:text-base text-gray-900 font-medium">
                            Active Customer
                        </label>
                    </div>
                    @error('is_active')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('customers.index') }}" 
                       class="btn-secondary mobile-full-width sm:w-auto text-center touch-target order-2 sm:order-1">
                        <i class="fas fa-times mr-1 sm:mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" 
                            class="btn-primary mobile-full-width sm:w-auto touch-target order-1 sm:order-2">
                        <i class="fas fa-plus mr-1 sm:mr-2"></i>
                        Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection