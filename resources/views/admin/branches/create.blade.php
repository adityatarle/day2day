@extends('layouts.app')

@section('title', 'Add New Branch')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New Branch</h1>
                <p class="text-gray-600 mt-1">Create a new business branch with all necessary details.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.branches') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Branches
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Branch Information</h3>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.branches.store') }}">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2">Basic Information</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Branch Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-input @error('name') border-red-500 @enderror" 
                                   placeholder="Enter branch name" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address *</label>
                            <textarea name="address" rows="4" 
                                      class="form-input @error('address') border-red-500 @enderror" 
                                      placeholder="Enter complete branch address" required>{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Branch Status</label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="is_active" value="1" 
                                           {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                           class="form-radio text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="is_active" value="0" 
                                           {{ old('is_active') == '0' ? 'checked' : '' }}
                                           class="form-radio text-red-600">
                                    <span class="ml-2 text-sm text-gray-700">Inactive</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2">Contact Information</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" 
                                   class="form-input @error('phone') border-red-500 @enderror" 
                                   placeholder="Enter branch phone number">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="form-input @error('email') border-red-500 @enderror" 
                                   placeholder="Enter branch email">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Manager Name</label>
                            <input type="text" name="manager_name" value="{{ old('manager_name') }}" 
                                   class="form-input @error('manager_name') border-red-500 @enderror" 
                                   placeholder="Enter branch manager name">
                            @error('manager_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Branch Features Info -->
                <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h5 class="font-medium text-blue-900 mb-3">Branch Features:</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Independent inventory management
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Branch-specific pricing
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Staff assignment and management
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Performance analytics and reporting
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.branches') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection