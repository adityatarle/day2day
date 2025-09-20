@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
                <p class="text-gray-600 mt-1">Update user information, role, and permissions.</p>
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

    <!-- User Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ $user->role->name === 'admin' ? 'bg-red-100 text-red-800' : 
                               ($user->role->name === 'branch_manager' ? 'bg-blue-100 text-blue-800' : 
                               ($user->role->name === 'cashier' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800')) }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role->name)) }}
                        </span>
                        @if($user->branch)
                            <span class="text-sm text-gray-500">{{ $user->branch->name }}</span>
                        @endif
                        <span class="text-sm text-gray-500">Joined {{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Edit User Information</h3>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div class="space-y-6">
                        <h4 class="text-md font-semibold text-gray-900 border-b border-gray-200 pb-2">Personal Information</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                   class="form-input @error('name') border-red-500 @enderror" 
                                   placeholder="Enter full name" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                   class="form-input @error('email') border-red-500 @enderror" 
                                   placeholder="Enter email address" required>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" 
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
                                      placeholder="Enter address">{{ old('address', $user->address) }}</textarea>
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
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
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
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" 
                                   class="form-input @error('password') border-red-500 @enderror" 
                                   placeholder="Leave empty to keep current password">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Leave empty if you don't want to change the password</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" 
                                   class="form-input" 
                                   placeholder="Confirm new password">
                        </div>
                    </div>
                </div>

                <!-- Account Status -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h5 class="font-medium text-gray-900 mb-3">Account Status</h5>
                    <div class="text-sm text-gray-600">
                        <p><strong>Created:</strong> {{ $user->created_at->format('M d, Y \a\t h:i A') }}</p>
                        <p><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y \a\t h:i A') }}</p>
                        <p><strong>Current Status:</strong> 
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-1">
                                Active
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection