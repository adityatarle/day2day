@extends('layouts.app')

@section('title', 'Day2Day Branch Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">{{ $branch->name }}</h1>
                    <p class="mb-0 text-muted">{{ $branch->city->name ?? 'Unknown City' }} Branch Dashboard</p>
                </div>
                <div>
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#purchaseEntryModal">
                        <i class="fas fa-plus"></i> Purchase Entry
                    </button>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#recordDamageModal">
                        <i class="fas fa-exclamation-triangle"></i> Record Damage
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
                                Today's Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($todaySales, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Monthly Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($monthlySales, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Inventory Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($inventoryValue, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
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
                                Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory and POS Status -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Inventory Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="h4 mb-0 font-weight-bold text-primary">{{ $totalProducts }}</div>
                            <div class="text-xs text-uppercase text-primary">Total Products</div>
                        </div>
                        <div class="col-md-4">
                            <div class="h4 mb-0 font-weight-bold text-warning">{{ $lowStockCount }}</div>
                            <div class="text-xs text-uppercase text-warning">Low Stock</div>
                        </div>
                        <div class="col-md-4">
                            <div class="h4 mb-0 font-weight-bold text-danger">{{ $outOfStockCount }}</div>
                            <div class="text-xs text-uppercase text-danger">Out of Stock</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">POS Status</h6>
                </div>
                <div class="card-body">
                    @if($activePosSession)
                    <div class="alert alert-success">
                        <strong>Active POS Session</strong><br>
                        Operator: {{ $activePosSession->user->name }}<br>
                        Started: {{ $activePosSession->created_at->format('M d, Y H:i A') }}<br>
                        Today's POS Revenue: ₹{{ number_format($todayPosRevenue, 2) }}
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <strong>No Active POS Session</strong><br>
                        <a href="{{ route('pos.start-session') }}" class="btn btn-primary btn-sm mt-2">Start POS Session</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Stock Transfers -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Stock Transfers</h6>
                    <span class="badge badge-warning">{{ $pendingReceipts }} Pending</span>
                </div>
                <div class="card-body">
                    @if($recentTransfers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Transfer #</th>
                                    <th>From</th>
                                    <th>Status</th>
                                    <th>Expected</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransfers as $transfer)
                                <tr>
                                    <td><strong>{{ $transfer['transfer_number'] }}</strong></td>
                                    <td>{{ $transfer['from_branch'] }}</td>
                                    <td>
                                        <span class="badge badge-{{ $transfer['status'] === 'pending' ? 'warning' : ($transfer['status'] === 'dispatched' ? 'info' : 'success') }}">
                                            {{ ucfirst($transfer['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $transfer['expected_delivery'] ?? 'N/A' }}</td>
                                    <td>{{ $transfer['created_at'] }}</td>
                                    <td>
                                        @if($transfer['status'] === 'dispatched')
                                        <button class="btn btn-success btn-sm confirm-receipt" data-transfer-id="{{ $transfer['id'] }}">
                                            Confirm Receipt
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">No recent stock transfers.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                </div>
                <div class="card-body">
                    @foreach($recentActivities as $activity)
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            @if($activity['type'] === 'sale')
                            <i class="fas fa-shopping-cart text-success"></i>
                            @elseif($activity['type'] === 'purchase')
                            <i class="fas fa-truck text-info"></i>
                            @else
                            <i class="fas fa-box text-warning"></i>
                            @endif
                        </div>
                        <div>
                            <div class="small">{{ $activity['description'] }}</div>
                            <div class="text-muted" style="font-size: 0.8rem;">{{ $activity['time_ago'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Purchases and Losses -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Purchases</h6>
                    <div class="text-success font-weight-bold">₹{{ number_format($monthlyPurchases, 2) }} This Month</div>
                </div>
                <div class="card-body">
                    @if($recentPurchases->count() > 0)
                    @foreach($recentPurchases as $purchase)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>{{ $purchase['order_number'] }}</strong>
                            <small class="text-muted d-block">{{ $purchase['vendor_name'] }}</small>
                            <small class="text-muted">{{ $purchase['created_at'] }}</small>
                        </div>
                        <div>
                            <div class="text-success font-weight-bold">₹{{ number_format($purchase['total_amount'], 2) }}</div>
                            <span class="badge badge-{{ $purchase['status'] === 'pending' ? 'warning' : 'success' }} badge-sm">
                                {{ ucfirst($purchase['status']) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted">No recent purchases.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Losses</h6>
                    <div class="text-danger font-weight-bold">₹{{ number_format($monthlyLosses, 2) }} This Month</div>
                </div>
                <div class="card-body">
                    @if($recentLosses->count() > 0)
                    @foreach($recentLosses as $loss)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>{{ $loss['product_name'] }}</strong>
                            <small class="text-muted d-block">{{ ucfirst($loss['loss_type']) }} - {{ $loss['quantity_lost'] }} units</small>
                            <small class="text-muted">{{ $loss['loss_date'] }}</small>
                        </div>
                        <div class="text-danger font-weight-bold">
                            ₹{{ number_format($loss['total_loss_value'], 2) }}
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted">No recent losses recorded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Purchase Entry Modal -->
<div class="modal fade" id="purchaseEntryModal" tabindex="-1" aria-labelledby="purchaseEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purchaseEntryModalLabel">Create Purchase Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="purchaseEntryForm">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="vendor" class="form-label">Vendor</label>
                            <select class="form-select" id="vendor" name="vendor_id" required>
                                <option value="">Select Vendor</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="stockTransfer" class="form-label">Related Stock Transfer (Optional)</label>
                            <select class="form-select" id="stockTransfer" name="stock_transfer_id">
                                <option value="">Select Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="invoiceNumber" class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="invoiceNumber" name="invoice_number">
                        </div>
                        <div class="col-md-6">
                            <label for="invoiceDate" class="form-label">Invoice Date</label>
                            <input type="date" class="form-control" id="invoiceDate" name="invoice_date">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Items</label>
                        <div id="purchaseItemsContainer">
                            <div class="row purchase-item-row mb-2">
                                <div class="col-md-3">
                                    <select class="form-select product-select" name="items[0][product_id]" required>
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="items[0][quantity_ordered]" placeholder="Ordered" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="items[0][quantity_received]" placeholder="Received" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="items[0][unit_price]" placeholder="Unit Price" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input damage-check" type="checkbox" name="items[0][is_damaged]" value="1">
                                        <label class="form-check-label">Damaged</label>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger remove-purchase-item" style="display: none;">×</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" id="addPurchaseItemBtn">Add Item</button>
                    </div>

                    <div class="mb-3">
                        <label for="purchaseNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="purchaseNotes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Purchase Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Damage Modal -->
<div class="modal fade" id="recordDamageModal" tabindex="-1" aria-labelledby="recordDamageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordDamageModalLabel">Record Damage/Wastage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="recordDamageForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="damageProduct" class="form-label">Product</label>
                        <select class="form-select" id="damageProduct" name="product_id" required>
                            <option value="">Select Product</option>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="damageQuantity" class="form-label">Quantity Lost</label>
                            <input type="number" class="form-control" id="damageQuantity" name="quantity" min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="damageUnitCost" class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" id="damageUnitCost" name="unit_cost" min="0" step="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="lossType" class="form-label">Loss Type</label>
                        <select class="form-select" id="lossType" name="loss_type" required>
                            <option value="">Select Type</option>
                            <option value="damage">Damage</option>
                            <option value="wastage">Wastage</option>
                            <option value="expiry">Expiry</option>
                            <option value="theft">Theft</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="damageReason" class="form-label">Reason</label>
                        <textarea class="form-control" id="damageReason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Record Damage</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let purchaseItemIndex = 1;
    let vendors = [];
    let products = [];
    let stockTransfers = [];

    // Load initial data
    loadVendors();
    loadProducts();
    loadStockTransfers();

    function loadVendors() {
        $.get('/api/day2day/branch/vendors', function(data) {
            vendors = data;
            const select = $('#vendor');
            select.empty().append('<option value="">Select Vendor</option>');
            data.forEach(function(vendor) {
                select.append(`<option value="${vendor.id}">${vendor.name}</option>`);
            });
        });
    }

    function loadProducts() {
        $.get('/api/day2day/branch/products', function(data) {
            products = data;
            updateProductSelects();
        });
    }

    function loadStockTransfers() {
        // Load pending stock transfers for this branch
        $.get('/api/day2day/branch/stock-transfers', function(data) {
            stockTransfers = data;
            const select = $('#stockTransfer');
            select.empty().append('<option value="">Select Transfer</option>');
            data.forEach(function(transfer) {
                select.append(`<option value="${transfer.id}">${transfer.transfer_number}</option>`);
            });
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

        // Also update damage product select
        const damageSelect = $('#damageProduct');
        const currentDamageValue = damageSelect.val();
        damageSelect.empty().append('<option value="">Select Product</option>');
        products.forEach(function(product) {
            damageSelect.append(`<option value="${product.id}">${product.name} (${product.code})</option>`);
        });
        damageSelect.val(currentDamageValue);
    }

    // Add purchase item functionality
    $('#addPurchaseItemBtn').click(function() {
        const newRow = `
            <div class="row purchase-item-row mb-2">
                <div class="col-md-3">
                    <select class="form-select product-select" name="items[${purchaseItemIndex}][product_id]" required>
                        <option value="">Select Product</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="items[${purchaseItemIndex}][quantity_ordered]" placeholder="Ordered" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="items[${purchaseItemIndex}][quantity_received]" placeholder="Received" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="items[${purchaseItemIndex}][unit_price]" placeholder="Unit Price" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <div class="form-check">
                        <input class="form-check-input damage-check" type="checkbox" name="items[${purchaseItemIndex}][is_damaged]" value="1">
                        <label class="form-check-label">Damaged</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-purchase-item">×</button>
                </div>
            </div>
        `;
        $('#purchaseItemsContainer').append(newRow);
        purchaseItemIndex++;
        updateProductSelects();
        updateRemovePurchaseButtons();
    });

    // Remove purchase item functionality
    $(document).on('click', '.remove-purchase-item', function() {
        $(this).closest('.purchase-item-row').remove();
        updateRemovePurchaseButtons();
    });

    function updateRemovePurchaseButtons() {
        const rows = $('.purchase-item-row');
        rows.find('.remove-purchase-item').toggle(rows.length > 1);
    }

    // Handle damage checkbox
    $(document).on('change', '.damage-check', function() {
        const row = $(this).closest('.purchase-item-row');
        const index = row.index();
        
        if ($(this).is(':checked')) {
            if (!row.find('.damage-quantity-input').length) {
                const damageInput = `
                    <div class="col-md-2 damage-quantity-input">
                        <input type="number" class="form-control" name="items[${index}][damage_quantity]" placeholder="Damage Qty" min="0" step="0.01">
                    </div>
                `;
                row.find('.remove-purchase-item').parent().before(damageInput);
            }
        } else {
            row.find('.damage-quantity-input').remove();
        }
    });

    // Purchase entry form submission
    $('#purchaseEntryForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.post('/api/day2day/branch/purchase-entry', formData)
            .done(function(response) {
                if (response.success) {
                    alert('Purchase entry created successfully!');
                    $('#purchaseEntryModal').modal('hide');
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

    // Record damage form submission
    $('#recordDamageForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.post('/api/day2day/branch/record-damage', formData)
            .done(function(response) {
                if (response.success) {
                    alert('Damage recorded successfully!');
                    $('#recordDamageModal').modal('hide');
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

    // Confirm stock transfer receipt
    $(document).on('click', '.confirm-receipt', function() {
        const transferId = $(this).data('transfer-id');
        
        if (confirm('Confirm receipt of this stock transfer?')) {
            $.post(`/api/day2day/branch/confirm-receipt/${transferId}`)
                .done(function(response) {
                    if (response.success) {
                        alert('Stock transfer confirmed!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                })
                .fail(function(xhr) {
                    const response = xhr.responseJSON;
                    alert('Error: ' + (response.message || 'Something went wrong'));
                });
        }
    });
});
</script>
@endsection