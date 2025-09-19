@extends('layouts.app')

@section('title', 'Create Local Purchase')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Local Purchase</h1>
        <p class="text-muted">Create a new local purchase for your branch</p>
    </div>

    <form action="{{ route('branch.local-purchases.store') }}" method="POST" enctype="multipart/form-data" id="localPurchaseForm">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Purchase Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                       value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                @error('purchase_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Link to Purchase Order (Optional)</label>
                                <select name="purchase_order_id" class="form-select" onchange="loadPurchaseOrderItems(this.value)">
                                    <option value="">-- Select Purchase Order --</option>
                                    @foreach($pendingOrders as $order)
                                    <option value="{{ $order->id }}" {{ old('purchase_order_id', $selectedOrder?->id) == $order->id ? 'selected' : '' }}>
                                        #{{ $order->po_number }} - {{ $order->items->count() }} items
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Link this purchase to fulfill a pending order</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Information -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Vendor Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="useExistingVendor" checked
                                           onchange="toggleVendorFields()">
                                    <label class="form-check-label" for="useExistingVendor">
                                        Use existing vendor
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12" id="existingVendorField">
                                <label class="form-label">Select Vendor</label>
                                <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }} - {{ $vendor->phone }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="newVendorNameField" style="display: none;">
                                <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror" 
                                       value="{{ old('vendor_name') }}" placeholder="Enter vendor name">
                                @error('vendor_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="newVendorPhoneField" style="display: none;">
                                <label class="form-label">Vendor Phone</label>
                                <input type="text" name="vendor_phone" class="form-control @error('vendor_phone') is-invalid @enderror" 
                                       value="{{ old('vendor_phone') }}" placeholder="Enter vendor phone">
                                @error('vendor_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Items -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Purchase Items</h5>
                        <button type="button" class="btn btn-sm btn-light" onclick="addItemRow()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 30%">Product</th>
                                        <th style="width: 15%">Quantity</th>
                                        <th style="width: 10%">Unit</th>
                                        <th style="width: 15%">Unit Price</th>
                                        <th style="width: 10%">Tax %</th>
                                        <th style="width: 10%">Discount %</th>
                                        <th style="width: 15%">Total</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                                        <td colspan="2">
                                            <span id="subtotalDisplay">₹0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Tax:</td>
                                        <td colspan="2">
                                            <span id="taxDisplay">₹0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Discount:</td>
                                        <td colspan="2">
                                            <span id="discountDisplay">₹0.00</span>
                                        </td>
                                    </tr>
                                    <tr class="table-active">
                                        <td colspan="6" class="text-end fw-bold fs-5">Total:</td>
                                        <td colspan="2">
                                            <span id="totalDisplay" class="fw-bold fs-5">₹0.00</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Payment Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>Credit</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Reference</label>
                            <input type="text" name="payment_reference" class="form-control @error('payment_reference') is-invalid @enderror" 
                                   value="{{ old('payment_reference') }}" placeholder="Transaction ID, Cheque No, etc.">
                            @error('payment_reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Receipt Upload -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Receipt/Invoice</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Upload Receipt</label>
                            <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror" 
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Accepted formats: JPG, PNG, PDF (Max 5MB)</small>
                            @error('receipt')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Additional Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Any additional information...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Purchase
                    </button>
                    <a href="{{ route('branch.local-purchases.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Products data
const products = @json($products);
let itemCount = 0;

// Initialize with one empty row
document.addEventListener('DOMContentLoaded', function() {
    addItemRow();
    
    // Load purchase order items if selected
    const purchaseOrderId = document.querySelector('[name="purchase_order_id"]').value;
    if (purchaseOrderId && '{{ $selectedOrder }}') {
        loadPurchaseOrderItems(purchaseOrderId);
    }
});

function toggleVendorFields() {
    const useExisting = document.getElementById('useExistingVendor').checked;
    document.getElementById('existingVendorField').style.display = useExisting ? 'block' : 'none';
    document.getElementById('newVendorNameField').style.display = useExisting ? 'none' : 'block';
    document.getElementById('newVendorPhoneField').style.display = useExisting ? 'none' : 'block';
    
    // Clear values
    if (useExisting) {
        document.querySelector('[name="vendor_name"]').value = '';
        document.querySelector('[name="vendor_phone"]').value = '';
    } else {
        document.querySelector('[name="vendor_id"]').value = '';
    }
}

function addItemRow(productId = null, quantity = 1, unit = 'kg', unitPrice = 0, taxRate = 0, discountRate = 0) {
    itemCount++;
    const tbody = document.getElementById('itemsTableBody');
    const row = document.createElement('tr');
    row.id = `item-row-${itemCount}`;
    
    let productOptions = '<option value="">-- Select Product --</option>';
    products.forEach(product => {
        const selected = productId == product.id ? 'selected' : '';
        productOptions += `<option value="${product.id}" ${selected}>${product.name}</option>`;
    });
    
    row.innerHTML = `
        <td>
            <select name="items[${itemCount}][product_id]" class="form-select form-select-sm" required onchange="updateItemTotal(${itemCount})">
                ${productOptions}
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemCount}][quantity]" class="form-control form-control-sm" 
                   value="${quantity}" min="0.001" step="0.001" required onchange="updateItemTotal(${itemCount})">
        </td>
        <td>
            <select name="items[${itemCount}][unit]" class="form-select form-select-sm" required>
                <option value="kg" ${unit === 'kg' ? 'selected' : ''}>kg</option>
                <option value="g" ${unit === 'g' ? 'selected' : ''}>g</option>
                <option value="l" ${unit === 'l' ? 'selected' : ''}>l</option>
                <option value="ml" ${unit === 'ml' ? 'selected' : ''}>ml</option>
                <option value="pcs" ${unit === 'pcs' ? 'selected' : ''}>pcs</option>
                <option value="dozen" ${unit === 'dozen' ? 'selected' : ''}>dozen</option>
                <option value="box" ${unit === 'box' ? 'selected' : ''}>box</option>
                <option value="pack" ${unit === 'pack' ? 'selected' : ''}>pack</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemCount}][unit_price]" class="form-control form-control-sm" 
                   value="${unitPrice}" min="0" step="0.01" required onchange="updateItemTotal(${itemCount})">
        </td>
        <td>
            <input type="number" name="items[${itemCount}][tax_rate]" class="form-control form-control-sm" 
                   value="${taxRate}" min="0" max="100" step="0.01" onchange="updateItemTotal(${itemCount})">
        </td>
        <td>
            <input type="number" name="items[${itemCount}][discount_rate]" class="form-control form-control-sm" 
                   value="${discountRate}" min="0" max="100" step="0.01" onchange="updateItemTotal(${itemCount})">
        </td>
        <td>
            <span id="item-total-${itemCount}" class="fw-bold">₹0.00</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeItemRow(${itemCount})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    updateItemTotal(itemCount);
}

function removeItemRow(rowId) {
    const row = document.getElementById(`item-row-${rowId}`);
    if (row) {
        row.remove();
        updateGrandTotal();
    }
}

function updateItemTotal(rowId) {
    const quantity = parseFloat(document.querySelector(`[name="items[${rowId}][quantity]"]`).value) || 0;
    const unitPrice = parseFloat(document.querySelector(`[name="items[${rowId}][unit_price]"]`).value) || 0;
    const taxRate = parseFloat(document.querySelector(`[name="items[${rowId}][tax_rate]"]`).value) || 0;
    const discountRate = parseFloat(document.querySelector(`[name="items[${rowId}][discount_rate]"]`).value) || 0;
    
    const subtotal = quantity * unitPrice;
    const taxAmount = subtotal * (taxRate / 100);
    const discountAmount = subtotal * (discountRate / 100);
    const total = subtotal + taxAmount - discountAmount;
    
    document.getElementById(`item-total-${rowId}`).textContent = `₹${total.toFixed(2)}`;
    
    updateGrandTotal();
}

function updateGrandTotal() {
    let subtotal = 0;
    let totalTax = 0;
    let totalDiscount = 0;
    
    // Calculate totals from all rows
    document.querySelectorAll('[id^="item-row-"]').forEach(row => {
        const rowId = row.id.replace('item-row-', '');
        const quantity = parseFloat(row.querySelector(`[name="items[${rowId}][quantity]"]`).value) || 0;
        const unitPrice = parseFloat(row.querySelector(`[name="items[${rowId}][unit_price]"]`).value) || 0;
        const taxRate = parseFloat(row.querySelector(`[name="items[${rowId}][tax_rate]"]`).value) || 0;
        const discountRate = parseFloat(row.querySelector(`[name="items[${rowId}][discount_rate]"]`).value) || 0;
        
        const itemSubtotal = quantity * unitPrice;
        subtotal += itemSubtotal;
        totalTax += itemSubtotal * (taxRate / 100);
        totalDiscount += itemSubtotal * (discountRate / 100);
    });
    
    const grandTotal = subtotal + totalTax - totalDiscount;
    
    document.getElementById('subtotalDisplay').textContent = `₹${subtotal.toFixed(2)}`;
    document.getElementById('taxDisplay').textContent = `₹${totalTax.toFixed(2)}`;
    document.getElementById('discountDisplay').textContent = `₹${totalDiscount.toFixed(2)}`;
    document.getElementById('totalDisplay').textContent = `₹${grandTotal.toFixed(2)}`;
}

function loadPurchaseOrderItems(purchaseOrderId) {
    if (!purchaseOrderId) {
        // Clear all items
        document.getElementById('itemsTableBody').innerHTML = '';
        addItemRow();
        return;
    }
    
    // This would typically make an AJAX call to get PO items
    // For now, we'll use the data if provided
    @if($selectedOrder)
        const poItems = @json($selectedOrder->items);
        document.getElementById('itemsTableBody').innerHTML = '';
        
        poItems.forEach(item => {
            addItemRow(
                item.product_id,
                item.quantity - (item.received_quantity || 0),
                item.unit || 'kg',
                item.unit_price || 0,
                0,
                0
            );
        });
    @endif
}

// Form validation
document.getElementById('localPurchaseForm').addEventListener('submit', function(e) {
    const itemRows = document.querySelectorAll('[id^="item-row-"]');
    if (itemRows.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the purchase');
        return false;
    }
    
    // Validate vendor selection
    const useExisting = document.getElementById('useExistingVendor').checked;
    if (useExisting) {
        const vendorId = document.querySelector('[name="vendor_id"]').value;
        if (!vendorId) {
            e.preventDefault();
            alert('Please select a vendor');
            return false;
        }
    } else {
        const vendorName = document.querySelector('[name="vendor_name"]').value;
        if (!vendorName) {
            e.preventDefault();
            alert('Please enter vendor name');
            return false;
        }
    }
});
</script>
@endsection