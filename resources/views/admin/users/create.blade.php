@extends('layouts.super-admin')

@section('title', 'Add New User')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New User</h1>
                <p class="text-gray-600 mt-1">Create a new user account with appropriate role and permissions.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">User Information</h3>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2">Personal Information</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-input @error('name') border-red-500 @enderror" 
                                   placeholder="Enter full name" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="form-input @error('email') border-red-500 @enderror" 
                                   placeholder="Enter email address" required>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" 
                                   class="form-input @error('phone') border-red-500 @enderror" 
                                   placeholder="Enter phone number">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="3" 
                                      class="form-input @error('address') border-red-500 @enderror" 
                                      placeholder="Enter address">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2">Account Information</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Role *</label>
                            <select name="role_id" class="form-input @error('role_id') border-red-500 @enderror" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Branch Assignment</label>
                            <select name="branch_id" class="form-input @error('branch_id') border-red-500 @enderror">
                                <option value="">No specific branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Leave empty for admin users or users with access to all branches</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" 
                                   class="form-input @error('password') border-red-500 @enderror" 
                                   placeholder="Enter password" required>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="password_confirmation" 
                                   class="form-input" 
                                   placeholder="Confirm password" required>
                        </div>
                    </div>
                </div>

                <!-- Role Descriptions -->
                <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h5 class="font-medium text-blue-900 mb-3">Role Descriptions:</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-blue-800">Admin:</span>
                            <span class="text-blue-700">Full system access, user management, all modules</span>
                        </div>
                        <div>
                            <span class="font-medium text-blue-800">Branch Manager:</span>
                            <span class="text-blue-700">Branch-specific management, inventory, orders, reports</span>
                        </div>
                        <div>
                            <span class="font-medium text-blue-800">Cashier:</span>
                            <span class="text-blue-700">Order processing, billing, customer service</span>
                        </div>
                        <div>
                            <span class="font-medium text-blue-800">Delivery Boy:</span>
                            <span class="text-blue-700">Delivery management, returns, customer adjustments</span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection