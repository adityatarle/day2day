@extends('layouts.app')

@section('title', 'Day2Day Admin Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Day2Day Admin Dashboard</h1>
                    <p class="mb-0 text-muted">Main Branch - Material Supply Management</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#supplyMaterialsModal">
                        <i class="fas fa-truck"></i> Supply Materials
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Active Branches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeBranches }}/{{ $totalBranches }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Transfers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingTransfers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Transit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inTransitTransfers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Overdue Transfers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueTransfers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Financial Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-success">₹{{ number_format($monthlySupplyValue, 2) }}</div>
                                <div class="text-xs text-uppercase text-success">Supply Value</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-info">₹{{ number_format($totalRevenue, 2) }}</div>
                                <div class="text-xs text-uppercase text-info">Total Revenue</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Branches</h6>
                </div>
                <div class="card-body">
                    @foreach($topPerformingBranches as $branch)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $branch['name'] }}</strong>
                            <small class="text-muted d-block">{{ $branch['city'] }}</small>
                        </div>
                        <div class="text-success font-weight-bold">
                            ₹{{ number_format($branch['revenue'], 2) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transfers and Low Stock -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Stock Transfers</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Transfer #</th>
                                    <th>To Branch</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Expected Delivery</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransfers as $transfer)
                                <tr>
                                    <td><strong>{{ $transfer['transfer_number'] }}</strong></td>
                                    <td>{{ $transfer['to_branch'] }}</td>
                                    <td>
                                        <span class="badge badge-{{ $transfer['status'] === 'pending' ? 'warning' : ($transfer['status'] === 'dispatched' ? 'info' : 'success') }}">
                                            {{ ucfirst($transfer['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $transfer['total_items'] }}</td>
                                    <td>{{ $transfer['expected_delivery'] ?? 'N/A' }}</td>
                                    <td>{{ $transfer['created_at'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Low Stock Alert</h6>
                </div>
                <div class="card-body">
                    @foreach($lowStockProducts as $product)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>{{ $product->product_name }}</strong>
                            <small class="text-muted d-block">{{ $product->branch_name }}</small>
                        </div>
                        <div>
                            <span class="badge badge-danger">{{ $product->current_stock }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Requests and Purchase Orders -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Branch Requests</h6>
                </div>
                <div class="card-body">
                    @foreach($recentBranchRequests as $request)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>{{ $request['branch_name'] }}</strong>
                            <small class="text-muted d-block">{{ $request['description'] }}</small>
                            <small class="text-muted">{{ $request['created_at'] }}</small>
                        </div>
                        <div>
                            <span class="badge badge-{{ $request['priority'] === 'high' ? 'danger' : ($request['priority'] === 'medium' ? 'warning' : 'info') }}">
                                {{ ucfirst($request['priority']) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Purchase Orders</h6>
                </div>
                <div class="card-body">
                    @foreach($recentPurchaseOrders as $po)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>{{ $po['order_number'] }}</strong>
                            <small class="text-muted d-block">{{ $po['branch_name'] }} - {{ $po['vendor_name'] }}</small>
                            <small class="text-muted">{{ $po['created_at'] }}</small>
                        </div>
                        <div>
                            <div class="text-success font-weight-bold">₹{{ number_format($po['total_amount'], 2) }}</div>
                            <span class="badge badge-{{ $po['status'] === 'pending' ? 'warning' : 'success' }} badge-sm">
                                {{ ucfirst($po['status']) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Supply Materials Modal -->
<div class="modal fade" id="supplyMaterialsModal" tabindex="-1" aria-labelledby="supplyMaterialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supplyMaterialsModalLabel">Supply Materials to Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="supplyMaterialsForm">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="toBranch" class="form-label">To Branch</label>
                            <select class="form-select" id="toBranch" name="to_branch_id" required>
                                <option value="">Select Branch</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="expectedDelivery" class="form-label">Expected Delivery Date</label>
                            <input type="date" class="form-control" id="expectedDelivery" name="expected_delivery" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Items to Supply</label>
                        <div id="itemsContainer">
                            <div class="row item-row mb-2">
                                <div class="col-md-6">
                                    <select class="form-select product-select" name="items[0][product_id]" required>
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" name="items[0][quantity]" placeholder="Quantity" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-item" style="display: none;">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" id="addItemBtn">Add Item</button>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Supply Materials</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 1;
    let branches = [];
    let products = [];

    // Load branches and products
    loadBranches();
    loadProducts();

    function loadBranches() {
        $.get('/api/day2day/admin/branches', function(data) {
            branches = data;
            const select = $('#toBranch');
            select.empty().append('<option value="">Select Branch</option>');
            data.forEach(function(branch) {
                select.append(`<option value="${branch.id}">${branch.name} (${branch.city.name})</option>`);
            });
        });
    }

    function loadProducts() {
        $.get('/api/day2day/admin/products', function(data) {
            products = data;
            updateProductSelects();
        });
    }

    function updateProductSelects() {
        $('.product-select').each(function() {
            const select = $(this);
            const currentValue = select.val();
            select.empty().append('<option value="">Select Product</option>');
            products.forEach(function(product) {
                select.append(`<option value="${product.id}">${product.name} (${product.code})</option>`);
            });
            select.val(currentValue);
        });
    }

    // Add item functionality
    $('#addItemBtn').click(function() {
        const newRow = `
            <div class="row item-row mb-2">
                <div class="col-md-6">
                    <select class="form-select product-select" name="items[${itemIndex}][product_id]" required>
                        <option value="">Select Product</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control" name="items[${itemIndex}][quantity]" placeholder="Quantity" min="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-item">Remove</button>
                </div>
            </div>
        `;
        $('#itemsContainer').append(newRow);
        itemIndex++;
        updateProductSelects();
        updateRemoveButtons();
    });

    // Remove item functionality
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        const rows = $('.item-row');
        rows.find('.remove-item').toggle(rows.length > 1);
    }

    // Form submission
    $('#supplyMaterialsForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.post('/api/day2day/admin/supply-materials', formData)
            .done(function(response) {
                if (response.success) {
                    alert('Materials supplied successfully!');
                    $('#supplyMaterialsModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                alert('Error: ' + (response.message || 'Something went wrong'));
            });
    });

    // Set minimum date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    $('#expectedDelivery').attr('min', tomorrow.toISOString().split('T')[0]);
});
</script>
@endsection