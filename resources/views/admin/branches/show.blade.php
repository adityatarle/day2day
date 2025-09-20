@extends('layouts.super-admin')

@section('title', 'Branch Details - ' . $branch->name)

@section('content')
<div class="p-6 space-y-8 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Page Header -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        </div>
        
        <div class="relative flex items-center justify-between">
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-1 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                            {{ $branch->name }}
                        </h1>
                        <p class="text-blue-100 text-lg font-medium">{{ $branch->code }} - {{ $branch->city->name ?? 'No City' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6 mt-4">
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-map-marker-alt text-blue-200"></i>
                        <span class="text-sm font-medium">{{ $branch->address }}</span>
                    </div>
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-{{ $branch->is_active ? 'check-circle text-green-300' : 'times-circle text-red-300' }}"></i>
                        <span class="text-sm font-medium">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="text-right space-y-2">
                    <a href="{{ route('admin.branches.edit', $branch) }}" class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl inline-flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit Branch</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Staff -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Staff</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $branch->users->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-gray-600 text-sm">Manager: {{ $branch->manager->name ?? 'Not Assigned' }}</span>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-green-600 text-sm font-medium">Today: ₹{{ number_format($stats['today_sales'], 2) }}</span>
            </div>
        </div>

        <!-- Monthly Sales -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Monthly Sales</p>
                    <p class="text-3xl font-bold text-gray-900">₹{{ number_format($stats['monthly_sales'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-gray-600 text-sm">Total: ₹{{ number_format($stats['total_sales'], 2) }}</span>
            </div>
        </div>

        <!-- POS Sessions -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active POS</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['active_sessions'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cash-register text-orange-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-gray-600 text-sm">{{ $stats['total_products'] }} Products</span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Branch Staff Management -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Staff Members -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Staff Management</h3>
                    <button onclick="openAddStaffModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Staff
                    </button>
                </div>
                
                <!-- Staff List -->
                <div class="space-y-4">
                    @forelse($branch->users as $user)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $user->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                <p class="text-sm text-gray-500">{{ $user->role->display_name ?? 'No Role' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <div class="flex space-x-1">
                                <button onclick="editStaff({{ $user->id }})" class="text-blue-600 hover:text-blue-800 p-1 rounded">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="resetPassword({{ $user->id }})" class="text-yellow-600 hover:text-yellow-800 p-1 rounded">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button onclick="toggleStaffStatus({{ $user->id }})" class="text-{{ $user->is_active ? 'red' : 'green' }}-600 hover:text-{{ $user->is_active ? 'red' : 'green' }}-800 p-1 rounded">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No staff members assigned to this branch</p>
                        <button onclick="openAddStaffModal()" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add First Staff Member
                        </button>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Recent Orders</h3>
                <div class="space-y-4">
                    @forelse($branch->orders->take(10) as $order)
                    <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-receipt text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">#{{ $order->id }}</p>
                                <p class="text-sm text-gray-600">{{ $order->customer->name ?? 'Walk-in' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">₹{{ number_format($order->total_amount, 2) }}</p>
                            <p class="text-sm text-gray-600">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <i class="fas fa-shopping-cart text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No recent orders</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Branch Information & POS Details -->
        <div class="space-y-6">
            <!-- Branch Details -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Branch Details</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Branch Code</label>
                        <p class="text-gray-900 font-medium">{{ $branch->code }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Phone</label>
                        <p class="text-gray-900 font-medium">{{ $branch->phone ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Email</label>
                        <p class="text-gray-900 font-medium">{{ $branch->email ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Outlet Type</label>
                        <p class="text-gray-900 font-medium capitalize">{{ $branch->outlet_type ?? 'Not specified' }}</p>
                    </div>
                    @if($branch->operating_hours)
                    <div>
                        <label class="text-sm font-medium text-gray-600">Operating Hours</label>
                        <div class="mt-2 space-y-1">
                            @foreach($branch->operating_hours as $day => $hours)
                            <div class="flex justify-between text-sm">
                                <span class="capitalize text-gray-700">{{ $day }}</span>
                                <span class="text-gray-900">{{ $hours['open'] ?? 'Closed' }} - {{ $hours['close'] ?? 'Closed' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- POS Configuration -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">POS Configuration</h3>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $branch->pos_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $branch->pos_enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                
                <div class="space-y-4">
                    @if($branch->pos_enabled)
                    <div>
                        <label class="text-sm font-medium text-gray-600">Terminal ID</label>
                        <p class="text-gray-900 font-medium">{{ $branch->pos_terminal_id ?? 'Not configured' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Active Sessions</label>
                        <p class="text-gray-900 font-medium">{{ $stats['active_sessions'] }}</p>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <button onclick="viewPosDetails()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-cash-register mr-2"></i>View POS Details
                        </button>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-times-circle text-gray-400 text-2xl mb-2"></i>
                        <p class="text-gray-500 text-sm">POS system is not enabled for this branch</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Quick Actions</h3>
                <div class="space-y-3">
                    <button onclick="openAddStaffModal()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-left">
                        <i class="fas fa-user-plus mr-2"></i>Add Staff Member
                    </button>
                    <button onclick="assignManager()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-left">
                        <i class="fas fa-user-tie mr-2"></i>Assign Manager
                    </button>
                    <button onclick="viewInventory()" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-left">
                        <i class="fas fa-boxes mr-2"></i>View Inventory
                    </button>
                    <button onclick="viewReports()" class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-left">
                        <i class="fas fa-chart-bar mr-2"></i>View Reports
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div id="addStaffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Staff Member</h3>
            <form id="addStaffForm" method="POST" action="{{ route('admin.branches.add-staff', $branch) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select name="role_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Role</option>
                            <option value="3">Branch Manager</option>
                            <option value="4">Cashier</option>
                            <option value="5">Delivery Boy</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAddStaffModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- POS Details Modal -->
<div id="posDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">POS Details - {{ $branch->name }}</h3>
                <button onclick="closePosDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="posDetailsContent">
                <!-- POS details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function openAddStaffModal() {
    document.getElementById('addStaffModal').classList.remove('hidden');
}

function closeAddStaffModal() {
    document.getElementById('addStaffModal').classList.add('hidden');
}

function editStaff(userId) {
    // Implement edit staff functionality
    alert('Edit staff functionality - User ID: ' + userId);
}

function resetPassword(userId) {
    if (confirm('Are you sure you want to reset the password for this staff member?')) {
        fetch(`/admin/users/${userId}/reset-password`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password reset successfully. New password: ' + data.password);
            } else {
                alert('Error resetting password: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error resetting password');
        });
    }
}

function toggleStaffStatus(userId) {
    fetch(`/admin/users/${userId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}

function assignManager() {
    // Implement assign manager functionality
    alert('Assign manager functionality');
}

function viewInventory() {
    window.location.href = `/admin/branches/{{ $branch->id }}/inventory`;
}

function viewReports() {
    window.location.href = `/admin/branches/{{ $branch->id }}/reports`;
}

function viewPosDetails() {
    document.getElementById('posDetailsModal').classList.remove('hidden');
    
    // Load POS details via AJAX
    fetch(`/admin/branches/{{ $branch->id }}/pos-details`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('posDetailsContent').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('posDetailsContent').innerHTML = '<p class="text-red-500">Error loading POS details</p>';
        });
}

function closePosDetailsModal() {
    document.getElementById('posDetailsModal').classList.add('hidden');
}
</script>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.float-animation {
    animation: float 3s ease-in-out infinite;
}
</style>
@endsection