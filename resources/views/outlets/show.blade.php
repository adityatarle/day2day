@extends('layouts.app')

@section('title', 'Outlet Details - ' . $outlet->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('outlets.index') }}" 
                           class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $outlet->name }}</h1>
                            <p class="text-gray-600">{{ $outlet->code }} • {{ $outlet->city->name ?? 'No City' }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button onclick="openMaterialTransferModal()" 
                            class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-truck mr-2"></i>
                        Send Materials
                    </button>
                    <a href="{{ route('outlets.edit', $outlet) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Outlet
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Outlet Information -->
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Outlet Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Outlet Name</label>
                                <p class="text-gray-900">{{ $outlet->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Outlet Code</label>
                                <p class="text-gray-900">{{ $outlet->code }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <p class="text-gray-900">{{ $outlet->phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <p class="text-gray-900">{{ $outlet->email }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <p class="text-gray-900">{{ $outlet->address }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <p class="text-gray-900">{{ $outlet->city->name ?? 'No City' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Outlet Type</label>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full capitalize">
                                    {{ $outlet->outlet_type }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Staff Members -->
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Staff Members</h2>
                            <span class="text-sm text-gray-500">{{ $outlet->users->count() }} members</span>
                        </div>
                        @if($outlet->users->count() > 0)
                            <div class="space-y-3">
                                @foreach($outlet->users as $user)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                                            {{ $user->role->name ?? 'No Role' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-users text-3xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">No staff members assigned</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent POS Sessions -->
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent POS Sessions</h2>
                        @if($outlet->posSessions->count() > 0)
                            <div class="space-y-3">
                                @foreach($outlet->posSessions as $session)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">Session #{{ $session->session_number ?? 'N/A' }}</p>
                                            <p class="text-sm text-gray-500">{{ $session->created_at->format('M d, Y H:i') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-medium text-gray-900">₹{{ number_format($session->total_sales ?? 0, 2) }}</p>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                {{ ucfirst($session->status ?? 'active') }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-cash-register text-3xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">No recent POS sessions</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Operational Status</span>
                            @if($outlet->is_active)
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
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">POS System</span>
                            @if($outlet->pos_enabled)
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Enabled</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">Disabled</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button onclick="openMaterialTransferModal()" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center">
                            <i class="fas fa-truck mr-2"></i>
                            Send Materials
                        </button>
                        <a href="{{ route('outlets.staff', $outlet) }}" 
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users mr-2"></i>
                            Manage Staff
                        </a>
                        <a href="{{ route('outlets.edit', $outlet) }}" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Details
                        </a>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-phone w-4 mr-3"></i>
                            <span>{{ $outlet->phone }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-envelope w-4 mr-3"></i>
                            <span>{{ $outlet->email }}</span>
                        </div>
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="fas fa-map-marker-alt w-4 mr-3 mt-0.5"></i>
                            <span>{{ $outlet->address }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Material Transfer Modal -->
<div id="materialTransferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">Send Materials to {{ $outlet->name }}</h3>
                    <button onclick="closeMaterialTransferModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="materialTransferForm" action="#" method="POST">
                    @csrf
                    <input type="hidden" name="to_branch_id" value="{{ $outlet->id }}">
                    
                    <!-- Transfer Details -->
                    <div class="space-y-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expected Delivery Date</label>
                                <input type="date" 
                                       name="expected_delivery" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Transport Vendor</label>
                                <input type="text" 
                                       name="transport_vendor" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Enter transport vendor name">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Vehicle Number</label>
                                <input type="text" 
                                       name="vehicle_number" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Enter vehicle number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Transport Cost</label>
                                <input type="number" 
                                       name="transport_cost" 
                                       step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dispatch Notes</label>
                            <textarea name="dispatch_notes" 
                                      rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Enter any special instructions or notes"></textarea>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="border-t pt-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Items to Transfer</h4>
                        
                        <!-- Add Item Button -->
                        <button type="button" onclick="addTransferItem()" 
                                class="mb-4 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Add Item
                        </button>

                        <!-- Items Container -->
                        <div id="transferItemsContainer" class="space-y-4">
                            <!-- Items will be added here dynamically -->
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 mt-8 pt-6 border-t">
                        <button type="button" onclick="closeMaterialTransferModal()" 
                                class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-3 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-lg">
                            Create Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let itemCounter = 0;

function openMaterialTransferModal() {
    document.getElementById('materialTransferModal').classList.remove('hidden');
    // Add first item by default
    if (itemCounter === 0) {
        addTransferItem();
    }
}

function closeMaterialTransferModal() {
    document.getElementById('materialTransferModal').classList.add('hidden');
    // Reset form
    document.getElementById('materialTransferForm').reset();
    document.getElementById('transferItemsContainer').innerHTML = '';
    itemCounter = 0;
}

function addTransferItem() {
    const container = document.getElementById('transferItemsContainer');
    const itemHtml = `
        <div class="transfer-item bg-gray-50 p-4 rounded-lg" data-item="${itemCounter}">
            <div class="flex justify-between items-start mb-4">
                <h5 class="font-medium text-gray-900">Item ${itemCounter + 1}</h5>
                <button type="button" onclick="removeTransferItem(${itemCounter})" 
                        class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                    <select name="items[${itemCounter}][product_id]" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Select Product</option>
                        <!-- Products will be loaded via AJAX or server-side -->
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Batch</label>
                    <select name="items[${itemCounter}][batch_id]" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Batch</option>
                        <!-- Batches will be loaded based on product selection -->
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <input type="number" 
                           name="items[${itemCounter}][quantity_sent]" 
                           min="1"
                           step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0"
                           required>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <input type="text" 
                       name="items[${itemCounter}][notes]" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Optional notes for this item">
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    itemCounter++;
}

function removeTransferItem(itemId) {
    const item = document.querySelector(`[data-item="${itemId}"]`);
    if (item) {
        item.remove();
    }
    
    // If no items left, add one
    if (document.querySelectorAll('.transfer-item').length === 0) {
        addTransferItem();
    }
}

// Handle form submission
document.getElementById('materialTransferForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Creating Transfer...';
    submitBtn.disabled = true;
    
    // Here you would submit the form via AJAX to your stock transfer endpoint
    // For now, we'll just show a success message
    setTimeout(() => {
        alert('Material transfer request created successfully!');
        closeMaterialTransferModal();
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }, 2000);
});
</script>
@endsection