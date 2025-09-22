@extends('layouts.super-admin')

@section('title', 'User Management')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">User Management</h1>
            <p class="text-gray-600 text-sm sm:text-base">Manage all system users and their roles</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('admin.users.create') }}" class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 touch-target">
                <i class="fas fa-plus mr-2"></i>
                <span class="hidden xs:inline">Add New User</span>
                <span class="xs:hidden">Add User</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2">{{ $users->total() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-users text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Active Users</p>
                    <p class="text-2xl sm:text-3xl font-bold text-green-600 mt-1 sm:mt-2">{{ $users->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-user-check text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Branch Managers</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-amber-600 mt-1 sm:mt-2">{{ $users->filter(fn($u) => $u->role && $u->role->name === 'branch_manager')->count() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-user-tie text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="super-admin-card p-4 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">Cashiers</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-purple-600 mt-1 sm:mt-2">{{ $users->filter(fn($u) => $u->role && $u->role->name === 'cashier')->count() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 ml-4">
                    <i class="fas fa-cash-register text-white text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">All Users</h3>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">{{ $users->total() }} users total</p>
        </div>

        <!-- Mobile Card View -->
        <div class="block sm:hidden">
            @forelse($users as $user)
            <div class="p-4 border-b border-gray-200 last:border-b-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 text-sm truncate">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1">
                        <a href="{{ route('admin.users.show', $user) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg touch-target">
                            <i class="fas fa-eye text-sm"></i>
                        </a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg touch-target">
                            <i class="fas fa-edit text-sm"></i>
                        </a>
                        @if(!$user->isSuperAdmin() && $user->id !== auth()->id())
                        <button onclick="toggleUserStatus({{ $user->id }})" class="p-2 text-{{ $user->is_active ? 'orange' : 'green' }}-600 hover:bg-{{ $user->is_active ? 'orange' : 'green' }}-50 rounded-lg touch-target">
                            <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }} text-sm"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg touch-target">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="mt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Role:</span>
                        <span class="text-xs font-medium text-gray-900">{{ $user->role ? ($user->role->display_name ?? ucfirst(str_replace('_', ' ', $user->role->name))) : 'No role' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Branch:</span>
                        <span class="text-xs font-medium text-gray-900">{{ $user->branch->name ?? 'No branch' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Status:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <div class="w-1 h-1 rounded-full mr-1 {{ $user->is_active ? 'bg-green-400' : 'bg-red-400' }}"></div>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Created:</span>
                        <span class="text-xs font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <div class="text-gray-500">
                    <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                    <p class="text-lg font-medium">No users found</p>
                    <p class="text-sm">Get started by creating your first user.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-white font-semibold text-xs sm:text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                </div>
                                <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</div>
                                    <div class="text-xs sm:text-sm text-gray-500 truncate">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            @if($user->role)
                                <span class="inline-flex items-center px-2 sm:px-2.5 py-1 rounded-full text-xs font-medium
                                    @if($user->role->name === 'super_admin') bg-red-100 text-red-800
                                    @elseif($user->role->name === 'admin') bg-blue-100 text-blue-800
                                    @elseif($user->role->name === 'branch_manager') bg-green-100 text-green-800
                                    @elseif($user->role->name === 'cashier') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    <span class="hidden sm:inline">{{ $user->role->display_name ?? ucfirst(str_replace('_', ' ', $user->role->name)) }}</span>
                                    <span class="sm:hidden">{{ strtoupper(substr($user->role->name, 0, 1)) }}</span>
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">No role</span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-500 truncate">
                            {{ $user->branch->name ?? 'No branch' }}
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 bg-green-400 rounded-full mr-1 sm:mr-1.5"></div>
                                    <span class="hidden sm:inline">Active</span>
                                    <span class="sm:hidden">A</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 bg-red-400 rounded-full mr-1 sm:mr-1.5"></div>
                                    <span class="hidden sm:inline">Inactive</span>
                                    <span class="sm:hidden">I</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-1 sm:space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-900 p-1.5 sm:p-2 rounded-lg hover:bg-blue-50 transition-colors touch-target">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 p-1.5 sm:p-2 rounded-lg hover:bg-indigo-50 transition-colors touch-target">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                @if(!$user->isSuperAdmin() && $user->id !== auth()->id())
                                <button onclick="toggleUserStatus({{ $user->id }})" class="text-{{ $user->is_active ? 'orange' : 'green' }}-600 hover:text-{{ $user->is_active ? 'orange' : 'green' }}-900 p-1.5 sm:p-2 rounded-lg hover:bg-{{ $user->is_active ? 'orange' : 'green' }}-50 transition-colors touch-target">
                                    <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }} text-sm"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1.5 sm:p-2 rounded-lg hover:bg-red-50 transition-colors touch-target">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                                <p class="text-lg font-medium">No users found</p>
                                <p class="text-sm">Get started by creating your first user.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function toggleUserStatus(userId) {
    fetch(`/admin/users/${userId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update user status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update user status');
    });
}
</script>
@endsection