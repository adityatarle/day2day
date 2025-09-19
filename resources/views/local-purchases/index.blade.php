@extends('layouts.app')

@section('title', 'Local Purchases')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Local Purchases</h1>
        @if(auth()->user()->isBranchManager())
        <a href="{{ route('branch.local-purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Create Local Purchase
        </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ request()->url() }}" method="GET" class="row g-3">
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="mb-3">
        <a href="{{ request()->fullUrlWithQuery(['format' => 'csv']) }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-file-csv me-1"></i>Export CSV
        </a>
        <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-file-pdf me-1"></i>Export PDF
        </a>
    </div>

    <!-- Local Purchases Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Purchase #</th>
                            <th>Date</th>
                            <th>Branch</th>
                            <th>Manager</th>
                            <th>Vendor</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($localPurchases as $purchase)
                        <tr>
                            <td>
                                <a href="{{ route(auth()->user()->isBranchManager() ? 'branch.local-purchases.show' : 'admin.local-purchases.show', $purchase) }}" 
                                   class="text-decoration-none">
                                    {{ $purchase->purchase_number }}
                                </a>
                            </td>
                            <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                            <td>{{ $purchase->branch->name }}</td>
                            <td>{{ $purchase->manager->name }}</td>
                            <td>{{ $purchase->vendor_display_name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $purchase->items->count() }} items</span>
                            </td>
                            <td class="fw-bold">₹{{ number_format($purchase->total_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-info text-dark">{{ ucfirst($purchase->payment_method) }}</span>
                            </td>
                            <td>
                                @switch($purchase->status)
                                    @case('draft')
                                        <span class="badge bg-secondary">Draft</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-success">Approved</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-primary">Completed</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route(auth()->user()->isBranchManager() ? 'branch.local-purchases.show' : 'admin.local-purchases.show', $purchase) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($purchase->isPending() && $purchase->manager_id === auth()->id())
                                    <a href="{{ route('branch.local-purchases.edit', $purchase) }}" 
                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif

                                    @if($purchase->isPending() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            onclick="approvePurchase({{ $purchase->id }})" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="showRejectModal({{ $purchase->id }})" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">No local purchases found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $localPurchases->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
@if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Local Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required
                                  placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approvePurchase(purchaseId) {
    if (confirm('Are you sure you want to approve this local purchase?')) {
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
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    document.getElementById('rejectForm').action = `/admin/local-purchases/${purchaseId}/reject`;
    modal.show();
}
</script>
@endif
@endsection