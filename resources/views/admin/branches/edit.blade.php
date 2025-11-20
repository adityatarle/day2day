@extends('layouts.super-admin')

@section('title', 'Edit Branch')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Branch</h1>
                <p class="text-gray-600 mt-1">Update branch information and settings.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.branches.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Branches
                </a>
            </div>
        </div>
    </div>

    <!-- Branch Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-xl">{{ strtoupper(substr($branch->name, 0, 2)) }}</span>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">{{ $branch->name }}</h3>
                    <p class="text-gray-600">{{ $branch->address }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($branch->manager_name)
                            <span class="text-sm text-gray-500">Manager: {{ $branch->manager_name }}</span>
                        @endif
                        <span class="text-sm text-gray-500">Created {{ $branch->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Edit Branch Information</h3>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.branches.update', $branch) }}">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2">Basic Information</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Branch Name *</label>
                            <input type="text" name="name" value="{{ old('name', $branch->name) }}" 
                                   class="form-input @error('name') border-red-500 @enderror" 
                                   placeholder="Enter branch name" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Branch Code *</label>
                            <input type="text" name="code" value="{{ old('code', $branch->code) }}" 
                                   class="form-input @error('code') border-red-500 @enderror" 
                                   placeholder="Enter branch code" required>
                            @error('code')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address *</label>
                            <textarea name="address" rows="4" 
                                      class="form-input @error('address') border-red-500 @enderror" 
                                      placeholder="Enter complete branch address" required>{{ old('address', $branch->address) }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <select name="city_id" class="form-input @error('city_id') border-red-500 @enderror" required>
                                <option value="">Select City</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ old('city_id', $branch->city_id) == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('city_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Outlet Type *</label>
                            <select name="outlet_type" class="form-input @error('outlet_type') border-red-500 @enderror" required>
                                <option value="retail" {{ old('outlet_type', $branch->outlet_type) == 'retail' ? 'selected' : '' }}>Retail</option>
                                <option value="wholesale" {{ old('outlet_type', $branch->outlet_type) == 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                                <option value="hybrid" {{ old('outlet_type', $branch->outlet_type) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                            </select>
                            @error('outlet_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Branch Status</label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="is_active" value="1" 
                                           {{ old('is_active', $branch->is_active) == '1' ? 'checked' : '' }}
                                           class="form-radio text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="is_active" value="0" 
                                           {{ old('is_active', $branch->is_active) == '0' ? 'checked' : '' }}
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
                            <input type="tel" name="phone" value="{{ old('phone', $branch->phone) }}" 
                                   class="form-input @error('phone') border-red-500 @enderror" 
                                   placeholder="Enter branch phone number">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $branch->email) }}" 
                                   class="form-input @error('email') border-red-500 @enderror" 
                                   placeholder="Enter branch email">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Manager Name</label>
                            <input type="text" name="manager_name" value="{{ old('manager_name', $branch->manager_name) }}" 
                                   class="form-input @error('manager_name') border-red-500 @enderror" 
                                   placeholder="Enter branch manager name">
                            @error('manager_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Branch Statistics -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h5 class="font-medium text-gray-900 mb-3">Branch Statistics</h5>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $branch->users()->count() }}</div>
                            <div class="text-gray-600">Staff Members</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $branch->orders()->count() }}</div>
                            <div class="text-gray-600">Total Orders</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $branch->products()->count() }}</div>
                            <div class="text-gray-600">Products</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900">₹{{ number_format($branch->orders()->sum('total_amount'), 2) }}</div>
                            <div class="text-gray-600">Total Revenue</div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.branches.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection