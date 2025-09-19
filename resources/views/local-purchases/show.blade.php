@extends('layouts.app')

@section('title', 'Local Purchase Details')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Local Purchase Details</h1>
            <p class="text-muted mb-0">{{ $localPurchase->purchase_number }}</p>
        </div>
        <div>
            @if($localPurchase->isPending() && $localPurchase->manager_id === auth()->id())
            <a href="{{ route('branch.local-purchases.edit', $localPurchase) }}" class="btn btn-secondary">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            @endif
            
            @if($localPurchase->isPending() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
            <button type="button" class="btn btn-success" onclick="approvePurchase()">
                <i class="fas fa-check me-2"></i>Approve
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="fas fa-times me-2"></i>Reject
            </button>
            @endif
            
            <a href="{{ auth()->user()->isBranchManager() ? route('branch.local-purchases.index') : route('admin.local-purchases.index') }}" 
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    @if($localPurchase->isRejected())
    <div class="alert alert-danger mb-4">
        <h6 class="alert-heading">Purchase Rejected</h6>
        <p class="mb-0">
            <strong>Rejected by:</strong> {{ $localPurchase->approvedBy->name }}<br>
            <strong>Date:</strong> {{ $localPurchase->approved_at->format('d M Y, h:i A') }}<br>
            <strong>Reason:</strong> {{ $localPurchase->rejection_reason ?: 'No reason provided' }}
        </p>
    </div>
    @elseif($localPurchase->isApproved() || $localPurchase->isCompleted())
    <div class="alert alert-success mb-4">
        <h6 class="alert-heading">Purchase Approved</h6>
        <p class="mb-0">
            <strong>Approved by:</strong> {{ $localPurchase->approvedBy->name }}<br>
            <strong>Date:</strong> {{ $localPurchase->approved_at->format('d M Y, h:i A') }}
        </p>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Purchase Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Purchase Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Purchase Number:</dt>
                                <dd class="col-sm-7">{{ $localPurchase->purchase_number }}</dd>
                                
                                <dt class="col-sm-5">Purchase Date:</dt>
                                <dd class="col-sm-7">{{ $localPurchase->purchase_date->format('d M Y') }}</dd>
                                
                                <dt class="col-sm-5">Branch:</dt>
                                <dd class="col-sm-7">{{ $localPurchase->branch->name }}</dd>
                                
                                <dt class="col-sm-5">Created by:</dt>
                                <dd class="col-sm-7">{{ $localPurchase->manager->name }}</dd>
                                
                                <dt class="col-sm-5">Status:</dt>
                                <dd class="col-sm-7">
                                    @switch($localPurchase->status)
                                        @case('draft')
                                            <span class="badge bg-secondary">Draft</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">Pending Approval</span>
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
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Vendor:</dt>
                                <dd class="col-sm-7">{{ $localPurchase->vendor_display_name }}</dd>
                                
                                @if($localPurchase->vendor_phone)
                                <dt class="col-sm-5">Vendor Phone:</dt>
                                <dd class="col-sm-7">{{ $localPurchase->vendor_phone }}</dd>
                                @endif
                                
                                <dt class="col-sm-5">Payment Method:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-info text-dark">{{ ucfirst($localPurchase->payment_method) }}</span>
                                </dd>
                                
                                @if($localPurchase->payment_reference)
                                <dt class="col-sm-5">Payment Ref:</dt>
                                <dd class="col-sm-7">{{ $localPurchase->payment_reference }}</dd>
                                @endif
                                
                                @if($localPurchase->purchaseOrder)
                                <dt class="col-sm-5">Linked PO:</dt>
                                <dd class="col-sm-7">
                                    <a href="{{ route('branch.product-orders.show', $localPurchase->purchaseOrder) }}">
                                        {{ $localPurchase->purchaseOrder->po_number }}
                                    </a>
                                </dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Items -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Purchase Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                    <th>Tax</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($localPurchase->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->notes)
                                        <br><small class="text-muted">{{ $item->notes }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td>₹{{ number_format($item->subtotal, 2) }}</td>
                                    <td>
                                        @if($item->tax_rate > 0)
                                        {{ $item->tax_rate }}%<br>
                                        <small>₹{{ number_format($item->tax_amount, 2) }}</small>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->discount_rate > 0)
                                        {{ $item->discount_rate }}%<br>
                                        <small>₹{{ number_format($item->discount_amount, 2) }}</small>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class="fw-bold">₹{{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end fw-bold">Subtotal:</td>
                                    <td class="fw-bold">₹{{ number_format($localPurchase->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end fw-bold">Total Tax:</td>
                                    <td class="fw-bold">₹{{ number_format($localPurchase->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end fw-bold">Total Discount:</td>
                                    <td class="fw-bold">₹{{ number_format($localPurchase->discount_amount, 2) }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td colspan="7" class="text-end fw-bold fs-5">Grand Total:</td>
                                    <td class="fw-bold fs-5 text-primary">₹{{ number_format($localPurchase->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($localPurchase->notes)
            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $localPurchase->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Financial Summary -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Items Count:</td>
                            <td class="text-end">{{ $localPurchase->items->count() }}</td>
                        </tr>
                        <tr>
                            <td>Total Quantity:</td>
                            <td class="text-end">{{ $localPurchase->items->sum('quantity') }}</td>
                        </tr>
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-end">₹{{ number_format($localPurchase->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Tax:</td>
                            <td class="text-end">₹{{ number_format($localPurchase->tax_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Discount:</td>
                            <td class="text-end">₹{{ number_format($localPurchase->discount_amount, 2) }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Total Amount:</td>
                            <td class="text-end text-primary">₹{{ number_format($localPurchase->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Receipt/Invoice -->
            @if($localPurchase->receipt_path)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Receipt/Invoice</h5>
                </div>
                <div class="card-body">
                    <a href="{{ Storage::url($localPurchase->receipt_path) }}" target="_blank" class="btn btn-outline-primary w-100">
                        <i class="fas fa-file-invoice me-2"></i>View Receipt
                    </a>
                </div>
            </div>
            @endif

            <!-- Linked Expense -->
            @if($localPurchase->expense)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Expense Record</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Expense ID:</dt>
                        <dd class="col-sm-6">#{{ $localPurchase->expense->id }}</dd>
                        
                        <dt class="col-sm-6">Category:</dt>
                        <dd class="col-sm-6">{{ $localPurchase->expense->expenseCategory->name }}</dd>
                        
                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-{{ $localPurchase->expense->isApproved() ? 'success' : 'warning' }}">
                                {{ ucfirst($localPurchase->expense->status) }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <li>
                            <div class="timeline-badge bg-primary">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title">Created</h6>
                                    <p class="text-muted">
                                        <small>{{ $localPurchase->created_at->format('d M Y, h:i A') }}</small>
                                    </p>
                                </div>
                                <div class="timeline-body">
                                    <p>By {{ $localPurchase->manager->name }}</p>
                                </div>
                            </div>
                        </li>
                        
                        @if($localPurchase->approved_at)
                        <li>
                            <div class="timeline-badge bg-{{ $localPurchase->isApproved() ? 'success' : 'danger' }}">
                                <i class="fas fa-{{ $localPurchase->isApproved() ? 'check' : 'times' }}"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title">{{ $localPurchase->isApproved() ? 'Approved' : 'Rejected' }}</h6>
                                    <p class="text-muted">
                                        <small>{{ $localPurchase->approved_at->format('d M Y, h:i A') }}</small>
                                    </p>
                                </div>
                                <div class="timeline-body">
                                    <p>By {{ $localPurchase->approvedBy->name }}</p>
                                    @if($localPurchase->rejection_reason)
                                    <p class="text-danger">Reason: {{ $localPurchase->rejection_reason }}</p>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endif
                        
                        @if($localPurchase->isCompleted())
                        <li>
                            <div class="timeline-badge bg-info">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title">Completed</h6>
                                    <p class="text-muted">
                                        <small>{{ $localPurchase->updated_at->format('d M Y, h:i A') }}</small>
                                    </p>
                                </div>
                                <div class="timeline-body">
                                    <p>Stock updated and expense recorded</p>
                                </div>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
@if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.local-purchases.reject', $localPurchase) }}" method="POST">
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
function approvePurchase() {
    if (confirm('Are you sure you want to approve this local purchase? This will update the stock and create an expense record.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.local-purchases.approve', $localPurchase) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endif

<style>
.timeline {
    list-style: none;
    padding: 20px 0 20px;
    position: relative;
}

.timeline:before {
    top: 0;
    bottom: 0;
    position: absolute;
    content: " ";
    width: 3px;
    background-color: #eee;
    left: 50%;
    margin-left: -1.5px;
}

.timeline > li {
    margin-bottom: 20px;
    position: relative;
}

.timeline > li:before,
.timeline > li:after {
    content: " ";
    display: table;
}

.timeline > li:after {
    clear: both;
}

.timeline > li > .timeline-panel {
    width: 46%;
    float: left;
    border: 1px solid #d4d4d4;
    border-radius: 2px;
    padding: 20px;
    position: relative;
    -webkit-box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
}

.timeline > li > .timeline-badge {
    color: #fff;
    width: 50px;
    height: 50px;
    line-height: 50px;
    font-size: 1.4em;
    text-align: center;
    position: absolute;
    top: 16px;
    left: 50%;
    margin-left: -25px;
    background-color: #999999;
    z-index: 100;
    border-top-right-radius: 50%;
    border-top-left-radius: 50%;
    border-bottom-right-radius: 50%;
    border-bottom-left-radius: 50%;
}

.timeline-title {
    margin-top: 0;
    color: inherit;
}

.timeline-body > p,
.timeline-body > ul {
    margin-bottom: 0;
}

.timeline-body > p + p {
    margin-top: 5px;
}
</style>
@endsection