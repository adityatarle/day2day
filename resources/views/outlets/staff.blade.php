@extends('layouts.app')

@section('title', 'Staff Management - ' . $outlet->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('outlets.show', $outlet) }}" 
                           class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Staff Management</h1>
                            <p class="text-gray-600">Manage staff for {{ $outlet->name }} ({{ $outlet->code }})</p>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button onclick="openAddStaffModal()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Add Staff Member
                    </button>
                    <a href="{{ route('outlets.show', $outlet) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg">
                        Back to Outlet
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Staff Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Staff</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $outlet->users->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cash-register text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Cashiers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $outlet->users->where('role.name', 'cashier')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Managers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $outlet->users->where('role.name', 'branch_manager')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active Staff</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $outlet->users->where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff List -->
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Staff Members</h2>
            </div>

            @if($outlet->users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($outlet->users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->phone ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->role)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                @if($user->role->name === 'cashier') bg-green-100 text-green-800 
                                                @elseif($user->role->name === 'branch_manager') bg-purple-100 text-purple-800 
                                                @elseif($user->role->name === 'admin') bg-blue-100 text-blue-800 
                                                @elseif($user->role->name === 'super_admin') bg-red-100 text-red-800 
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $user->role->name)) }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">No Role</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->is_active)
                                            <span class="flex items-center text-green-600">
                                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                                Active
                                            </span>
                                        @else
                                            <span class="flex items-center text-red-600">
                                                <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button onclick="editStaff({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->phone }}', '{{ $user->role_id }}', {{ $user->is_active ? 'true' : 'false' }})" 
                                                    class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="toggleStaffStatus({{ $user->id }}, {{ $user->is_active ? 'false' : 'true' }})" 
                                                    class="text-yellow-600 hover:text-yellow-800">
                                                <i class="fas fa-toggle-{{ $user->is_active ? 'on' : 'off' }}"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Staff Members</h3>
                    <p class="text-gray-600 mb-4">Get started by adding your first staff member</p>
                    <button onclick="openAddStaffModal()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg">
                        Add First Staff Member
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add/Edit Staff Modal -->
<div id="staffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-900">Add Staff Member</h3>
                    <button onclick="closeStaffModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="staffForm">
                    @csrf
                    <input type="hidden" id="staffId" name="staff_id">
                    <input type="hidden" name="branch_id" value="{{ $outlet->id }}">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" 
                                   id="staffName" 
                                   name="name" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" 
                                   id="staffEmail" 
                                   name="email" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" 
                                   id="staffPhone" 
                                   name="phone" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                            <select id="staffRole" 
                                    name="role_id" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       id="staffActive" 
                                       name="is_active" 
                                       value="1" 
                                       checked
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Staff member is active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-8">
                        <button type="button" onclick="closeStaffModal()" 
                                class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-3 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg">
                            <span id="submitButtonText">Add Staff Member</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let isEditMode = false;

function openAddStaffModal() {
    isEditMode = false;
    document.getElementById('modalTitle').textContent = 'Add Staff Member';
    document.getElementById('submitButtonText').textContent = 'Add Staff Member';
    document.getElementById('staffForm').reset();
    document.getElementById('staffId').value = '';
    document.getElementById('staffActive').checked = true;
    document.getElementById('staffModal').classList.remove('hidden');
}

function editStaff(id, name, email, phone, roleId, isActive) {
    isEditMode = true;
    document.getElementById('modalTitle').textContent = 'Edit Staff Member';
    document.getElementById('submitButtonText').textContent = 'Update Staff Member';
    
    document.getElementById('staffId').value = id;
    document.getElementById('staffName').value = name;
    document.getElementById('staffEmail').value = email;
    document.getElementById('staffPhone').value = phone || '';
    document.getElementById('staffRole').value = roleId;
    document.getElementById('staffActive').checked = isActive;
    
    document.getElementById('staffModal').classList.remove('hidden');
}

function closeStaffModal() {
    document.getElementById('staffModal').classList.add('hidden');
}

function toggleStaffStatus(userId, newStatus) {
    if (confirm('Are you sure you want to ' + (newStatus ? 'activate' : 'deactivate') + ' this staff member?')) {
        // Here you would make an AJAX call to update the staff status
        alert('Staff status would be updated to: ' + (newStatus ? 'Active' : 'Inactive'));
        // Reload page to show updated status
        location.reload();
    }
}

// Handle form submission
document.getElementById('staffForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.querySelector('span').textContent;
    submitBtn.querySelector('span').textContent = isEditMode ? 'Updating...' : 'Adding...';
    submitBtn.disabled = true;
    
    // Here you would submit the form via AJAX
    setTimeout(() => {
        alert(isEditMode ? 'Staff member updated successfully!' : 'Staff member added successfully!');
        closeStaffModal();
        submitBtn.querySelector('span').textContent = originalText;
        submitBtn.disabled = false;
        // Reload page to show updated data
        location.reload();
    }, 2000);
});
</script>
@endsection