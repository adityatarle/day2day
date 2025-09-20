@extends('layouts.app')

@section('title', 'Local Purchase Approvals')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Local Purchase Approvals</h1>
            <p class="text-gray-600 mt-1">Review and approve local purchases from branch managers</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.local-purchases.export') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-file-export mr-2"></i>Export Data
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Approval</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Approved Today</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['approved_today'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Rejected Today</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['rejected_today'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-rupee-sign text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Value</p>
                    <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($stats['total_value'] ?? 0, 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="{{ request()->url() }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-input">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div>
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-input">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
            </div>

            <div>
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
            </div>

            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    @if(request('status') == 'pending' || !request('status'))
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Pending Approvals</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    You have {{ $stats['pending'] ?? 0 }} local purchases waiting for your approval. 
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="font-medium underline hover:text-yellow-900">
                        View all pending requests
                    </a>
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Local Purchases Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($localPurchases as $purchase)
                    <tr class="hover:bg-gray-50 {{ $purchase->isPending() ? 'bg-yellow-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.local-purchases.show', $purchase) }}" 
                               class="text-blue-600 hover:text-blue-900 font-medium">
                                {{ $purchase->purchase_number }}
                            </a>
                            @if($purchase->isPending())
                            <div class="flex items-center mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>Needs Approval
                                </span>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $purchase->purchase_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $purchase->branch->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $purchase->manager->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $purchase->vendor_display_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ $purchase->items->count() }} items
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            ₹{{ number_format($purchase->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($purchase->status)
                                @case('pending')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                    @break
                                @case('approved')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Approved
                                    </span>
                                    @break
                                @case('rejected')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Rejected
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-check-circle mr-1"></i>Completed
                                    </span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.local-purchases.show', $purchase) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($purchase->isPending())
                                <button type="button" class="text-green-600 hover:text-green-900" 
                                        onclick="approvePurchase({{ $purchase->id }})" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="text-red-600 hover:text-red-900" 
                                        onclick="showRejectModal({{ $purchase->id }})" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No local purchases found</h3>
                                <p class="mt-1 text-sm text-gray-500">No local purchases match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($localPurchases->hasPages())
        <div class="mt-8">
            {{ $localPurchases->links() }}
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Reject Local Purchase
                            </h3>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">
                                    Rejection Reason <span class="text-red-500">*</span>
                                </label>
                                <textarea name="rejection_reason" rows="3" class="mt-1 form-input" required
                                          placeholder="Please provide a reason for rejection..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Reject Purchase
                    </button>
                    <button type="button" onclick="closeRejectModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approvePurchase(purchaseId) {
    if (confirm('Are you sure you want to approve this local purchase? This will update the stock and create an expense record.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/local-purchases/${purchaseId}/approve`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function showRejectModal(purchaseId) {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectForm').action = `/admin/local-purchases/${purchaseId}/reject`;
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeRejectModal();
    }
});
</script>
@endsection