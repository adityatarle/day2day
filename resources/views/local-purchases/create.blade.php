@extends('layouts.app')

@section('title', 'Create Local Purchase')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex items-center text-sm text-gray-600">
            <a href="{{ route('branch.local-purchases.index') }}" class="hover:text-gray-900">Local Purchases</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">Create New</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">Create Local Purchase</h1>
        <p class="text-gray-600">Create a new local purchase for your branch</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('branch.local-purchases.store') }}" method="POST" enctype="multipart/form-data" id="localPurchaseForm">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-4 rounded-t-lg">
                        <h2 class="text-lg font-semibold">Purchase Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Purchase Date <span class="text-red-500">*</span></label>
                                <input type="date" name="purchase_date" class="form-input @error('purchase_date') border-red-500 @enderror" 
                                       value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                @error('purchase_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label">Link to Purchase Order (Optional)</label>
                                <select name="purchase_order_id" class="form-input" onchange="loadPurchaseOrderItems(this.value)">
                                    <option value="">-- Select Purchase Order --</option>
                                    @foreach($pendingOrders as $order)
                                    <option value="{{ $order->id }}" {{ old('purchase_order_id', $selectedOrder?->id) == $order->id ? 'selected' : '' }}>
                                        #{{ $order->po_number }} - {{ $order->items->count() }} items
                                    </option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-gray-500 mt-1">Link this purchase to fulfill a pending order</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-4 rounded-t-lg">
                        <h2 class="text-lg font-semibold">Vendor Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                           id="useExistingVendor" checked onchange="toggleVendorFields()">
                                    <span class="ml-2 text-sm text-gray-700">Use existing vendor</span>
                                </label>
                            </div>

                            <div id="existingVendorField">
                                <label class="form-label">Select Vendor</label>
                                <select name="vendor_id" class="form-input @error('vendor_id') border-red-500 @enderror">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }} - {{ $vendor->phone }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="newVendorFields" style="display: none;">
                                <div>
                                    <label class="form-label">Vendor Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="vendor_name" class="form-input @error('vendor_name') border-red-500 @enderror" 
                                           value="{{ old('vendor_name') }}" placeholder="Enter vendor name">
                                    @error('vendor_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="form-label">Vendor Phone</label>
                                    <input type="text" name="vendor_phone" class="form-input @error('vendor_phone') border-red-500 @enderror" 
                                           value="{{ old('vendor_phone') }}" placeholder="Enter vendor phone">
                                    @error('vendor_phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Items -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                        <h2 class="text-lg font-semibold">Purchase Items</h2>
                        <button type="button" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-3 py-1 rounded transition-colors" onclick="addItemRow()">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-2">Product</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-2">Quantity</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-2">Unit</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-2">Unit Price</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-2">Tax %</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-2">Discount %</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-2">Total</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-2"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="divide-y divide-gray-200">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                                <tfoot class="border-t-2 border-gray-300">
                                    <tr>
                                        <td colspan="6" class="text-right font-medium text-gray-700 py-2">Subtotal:</td>
                                        <td colspan="2" class="py-2">
                                            <span id="subtotalDisplay" class="font-medium">₹0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right font-medium text-gray-700 py-2">Tax:</td>
                                        <td colspan="2" class="py-2">
                                            <span id="taxDisplay" class="font-medium">₹0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right font-medium text-gray-700 py-2">Discount:</td>
                                        <td colspan="2" class="py-2">
                                            <span id="discountDisplay" class="font-medium">₹0.00</span>
                                        </td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td colspan="6" class="text-right font-bold text-gray-900 text-lg py-3">Total:</td>
                                        <td colspan="2" class="py-3">
                                            <span id="totalDisplay" class="font-bold text-lg text-gray-900">₹0.00</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <!-- Payment Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-6 py-4 rounded-t-lg">
                        <h2 class="text-lg font-semibold">Payment Information</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="form-label">Payment Method <span class="text-red-500">*</span></label>
                            <select name="payment_method" class="form-input @error('payment_method') border-red-500 @enderror" required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>Credit</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('payment_method')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Payment Reference</label>
                            <input type="text" name="payment_reference" class="form-input @error('payment_reference') border-red-500 @enderror" 
                                   value="{{ old('payment_reference') }}" placeholder="Transaction ID, Cheque No, etc.">
                            @error('payment_reference')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Receipt Upload -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Receipt/Invoice</h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label class="form-label">Upload Receipt</label>
                            <input type="file" name="receipt" class="form-input @error('receipt') border-red-500 @enderror" 
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <p class="text-sm text-gray-500 mt-1">Accepted formats: JPG, PNG, PDF (Max 5MB)</p>
                            @error('receipt')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Additional Notes</h2>
                    </div>
                    <div class="p-6">
                        <textarea name="notes" class="form-input" rows="3" 
                                  placeholder="Any additional information...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i>Create Purchase
                    </button>
                    <a href="{{ route('branch.local-purchases.index') }}" class="block w-full text-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancel
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
    document.getElementById('newVendorFields').style.display = useExisting ? 'none' : 'grid';
    
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
    row.className = 'hover:bg-gray-50';
    
    let productOptions = '<option value="">-- Select Product --</option>';
    products.forEach(product => {
        const selected = productId == product.id ? 'selected' : '';
        productOptions += `<option value="${product.id}" ${selected}>${product.name}</option>`;
    });
    
    row.innerHTML = `
        <td class="py-3">
            <select name="items[${itemCount}][product_id]" class="form-input text-sm" required onchange="updateItemTotal(${itemCount})">
                ${productOptions}
            </select>
        </td>
        <td class="py-3">
            <input type="number" name="items[${itemCount}][quantity]" class="form-input text-sm" 
                   value="${quantity}" min="0.001" step="0.001" required onchange="updateItemTotal(${itemCount})">
        </td>
        <td class="py-3">
            <select name="items[${itemCount}][unit]" class="form-input text-sm" required>
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
        <td class="py-3">
            <input type="number" name="items[${itemCount}][unit_price]" class="form-input text-sm" 
                   value="${unitPrice}" min="0" step="0.01" required onchange="updateItemTotal(${itemCount})">
        </td>
        <td class="py-3">
            <input type="number" name="items[${itemCount}][tax_rate]" class="form-input text-sm" 
                   value="${taxRate}" min="0" max="100" step="0.01" onchange="updateItemTotal(${itemCount})">
        </td>
        <td class="py-3">
            <input type="number" name="items[${itemCount}][discount_rate]" class="form-input text-sm" 
                   value="${discountRate}" min="0" max="100" step="0.01" onchange="updateItemTotal(${itemCount})">
        </td>
        <td class="py-3">
            <span id="item-total-${itemCount}" class="font-semibold text-gray-900">₹0.00</span>
        </td>
        <td class="py-3">
            <button type="button" class="text-red-600 hover:text-red-800 transition-colors" onclick="removeItemRow(${itemCount})">
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
    console.log('Form submission started');
    
    // Check if there are any items
    const itemRows = document.querySelectorAll('[id^="item-row-"]');
    console.log('Item rows found:', itemRows.length);
    
    if (itemRows.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the purchase');
        return false;
    }
    
    // Validate each item row
    let hasValidItems = false;
    itemRows.forEach((row, index) => {
        const rowId = row.id.replace('item-row-', '');
        const productId = row.querySelector(`[name="items[${rowId}][product_id]"]`).value;
        const quantity = parseFloat(row.querySelector(`[name="items[${rowId}][quantity]"]`).value) || 0;
        const unitPrice = parseFloat(row.querySelector(`[name="items[${rowId}][unit_price]"]`).value) || 0;
        
        if (productId && quantity > 0 && unitPrice > 0) {
            hasValidItems = true;
        }
    });
    
    if (!hasValidItems) {
        e.preventDefault();
        alert('Please add at least one valid item with product, quantity, and unit price');
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
    
    console.log('Form validation passed, submitting...');
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating Purchase...';
    submitBtn.disabled = true;
    
    // Re-enable button after 5 seconds as fallback
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 5000);
});
</script>
@endsection