@extends('layouts.app')

@section('title', 'Edit Local Purchase')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Local Purchase</h1>
        <p class="text-muted">Update local purchase details - {{ $localPurchase->purchase_number }}</p>
    </div>

    <form action="{{ route('branch.local-purchases.update', $localPurchase) }}" method="POST" enctype="multipart/form-data" id="localPurchaseForm">
        @csrf
        @method('PUT')
        
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
                                       value="{{ old('purchase_date', $localPurchase->purchase_date->format('Y-m-d')) }}" required>
                                @error('purchase_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Purchase Number</label>
                                <input type="text" class="form-control" value="{{ $localPurchase->purchase_number }}" readonly>
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
                                    <input type="checkbox" class="form-check-input" id="useExistingVendor" 
                                           {{ $localPurchase->vendor_id ? 'checked' : '' }}
                                           onchange="toggleVendorFields()">
                                    <label class="form-check-label" for="useExistingVendor">
                                        Use existing vendor
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12" id="existingVendorField" style="{{ !$localPurchase->vendor_id ? 'display: none;' : '' }}">
                                <label class="form-label">Select Vendor</label>
                                <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id', $localPurchase->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }} - {{ $vendor->phone }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="newVendorNameField" style="{{ $localPurchase->vendor_id ? 'display: none;' : '' }}">
                                <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror" 
                                       value="{{ old('vendor_name', $localPurchase->vendor_name) }}" placeholder="Enter vendor name">
                                @error('vendor_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="newVendorPhoneField" style="{{ $localPurchase->vendor_id ? 'display: none;' : '' }}">
                                <label class="form-label">Vendor Phone</label>
                                <input type="text" name="vendor_phone" class="form-control @error('vendor_phone') is-invalid @enderror" 
                                       value="{{ old('vendor_phone', $localPurchase->vendor_phone) }}" placeholder="Enter vendor phone">
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
                                    @foreach($localPurchase->items as $index => $item)
                                    <tr id="item-row-{{ $index + 1 }}">
                                        <td>
                                            <input type="hidden" name="items[{{ $index + 1 }}][id]" value="{{ $item->id }}">
                                            <select name="items[{{ $index + 1 }}][product_id]" class="form-select form-select-sm" required onchange="updateItemTotal({{ $index + 1 }})">
                                                <option value="">-- Select Product --</option>
                                                @foreach($products as $product)
                                                <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index + 1 }}][quantity]" class="form-control form-control-sm" 
                                                   value="{{ $item->quantity }}" min="0.001" step="0.001" required onchange="updateItemTotal({{ $index + 1 }})">
                                        </td>
                                        <td>
                                            <select name="items[{{ $index + 1 }}][unit]" class="form-select form-select-sm" required>
                                                <option value="kg" {{ $item->unit === 'kg' ? 'selected' : '' }}>kg</option>
                                                <option value="g" {{ $item->unit === 'g' ? 'selected' : '' }}>g</option>
                                                <option value="l" {{ $item->unit === 'l' ? 'selected' : '' }}>l</option>
                                                <option value="ml" {{ $item->unit === 'ml' ? 'selected' : '' }}>ml</option>
                                                <option value="pcs" {{ $item->unit === 'pcs' ? 'selected' : '' }}>pcs</option>
                                                <option value="dozen" {{ $item->unit === 'dozen' ? 'selected' : '' }}>dozen</option>
                                                <option value="box" {{ $item->unit === 'box' ? 'selected' : '' }}>box</option>
                                                <option value="pack" {{ $item->unit === 'pack' ? 'selected' : '' }}>pack</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index + 1 }}][unit_price]" class="form-control form-control-sm" 
                                                   value="{{ $item->unit_price }}" min="0" step="0.01" required onchange="updateItemTotal({{ $index + 1 }})">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index + 1 }}][tax_rate]" class="form-control form-control-sm" 
                                                   value="{{ $item->tax_rate }}" min="0" max="100" step="0.01" onchange="updateItemTotal({{ $index + 1 }})">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index + 1 }}][discount_rate]" class="form-control form-control-sm" 
                                                   value="{{ $item->discount_rate }}" min="0" max="100" step="0.01" onchange="updateItemTotal({{ $index + 1 }})">
                                        </td>
                                        <td>
                                            <span id="item-total-{{ $index + 1 }}" class="fw-bold">₹{{ number_format($item->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeItemRow({{ $index + 1 }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                                        <td colspan="2">
                                            <span id="subtotalDisplay">₹{{ number_format($localPurchase->subtotal, 2) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Tax:</td>
                                        <td colspan="2">
                                            <span id="taxDisplay">₹{{ number_format($localPurchase->tax_amount, 2) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Discount:</td>
                                        <td colspan="2">
                                            <span id="discountDisplay">₹{{ number_format($localPurchase->discount_amount, 2) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="table-active">
                                        <td colspan="6" class="text-end fw-bold fs-5">Total:</td>
                                        <td colspan="2">
                                            <span id="totalDisplay" class="fw-bold fs-5">₹{{ number_format($localPurchase->total_amount, 2) }}</span>
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
                                <option value="cash" {{ old('payment_method', $localPurchase->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="upi" {{ old('payment_method', $localPurchase->payment_method) == 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="credit" {{ old('payment_method', $localPurchase->payment_method) == 'credit' ? 'selected' : '' }}>Credit</option>
                                <option value="bank_transfer" {{ old('payment_method', $localPurchase->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="card" {{ old('payment_method', $localPurchase->payment_method) == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="other" {{ old('payment_method', $localPurchase->payment_method) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Reference</label>
                            <input type="text" name="payment_reference" class="form-control @error('payment_reference') is-invalid @enderror" 
                                   value="{{ old('payment_reference', $localPurchase->payment_reference) }}" placeholder="Transaction ID, Cheque No, etc.">
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
                        @if($localPurchase->receipt_path)
                        <div class="mb-3">
                            <p class="text-muted mb-2">Current receipt:</p>
                            <a href="{{ Storage::url($localPurchase->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-invoice me-1"></i>View Current Receipt
                            </a>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label">Upload New Receipt</label>
                            <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror" 
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Leave empty to keep current receipt. Accepted formats: JPG, PNG, PDF (Max 5MB)</small>
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
                                  placeholder="Any additional information...">{{ old('notes', $localPurchase->notes) }}</textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Purchase
                    </button>
                    <a href="{{ route('branch.local-purchases.show', $localPurchase) }}" class="btn btn-secondary">
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
let itemCount = {{ $localPurchase->items->count() }};

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

// Initialize totals on page load
document.addEventListener('DOMContentLoaded', function() {
    updateGrandTotal();
});

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