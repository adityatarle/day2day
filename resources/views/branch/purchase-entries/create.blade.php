@extends('layouts.app')

@section('title', 'Create Purchase Entry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('branch.purchase-entries.index') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Purchase Entries
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Purchase Entry</h1>
                <p class="text-gray-600">Select a purchase order to record received materials</p>
            </div>
        </div>

        @if($availablePurchaseOrders->count() > 0)
            <div class="space-y-6">
                <div>
                    <label class="form-label">Select Purchase Order</label>
                    <p class="text-sm text-gray-500 mb-4">Choose from purchase orders that are approved/fulfilled but not yet received for your branch</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($availablePurchaseOrders as $purchaseOrder)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer purchase-order-card" 
                             data-po-id="{{ $purchaseOrder->id }}" 
                             data-po-number="{{ $purchaseOrder->po_number }}"
                             data-vendor="{{ $purchaseOrder->vendor->name }}"
                             data-status="{{ $purchaseOrder->status }}"
                             data-items-count="{{ $purchaseOrder->purchaseOrderItems->count() }}"
                             data-total-amount="{{ $purchaseOrder->total_amount }}"
                             data-created-at="{{ $purchaseOrder->created_at->format('M d, Y') }}">
                            
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $purchaseOrder->po_number }}</h3>
                                    <p class="text-sm text-gray-600">{{ $purchaseOrder->vendor->name }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $purchaseOrder->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $purchaseOrder->status === 'approved' ? 'Approved' : 'Fulfilled' }}
                                </span>
                            </div>

                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Items:</span>
                                    <span class="font-medium">{{ $purchaseOrder->purchaseOrderItems->count() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Total Amount:</span>
                                    <span class="font-medium text-green-600">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Order Date:</span>
                                    <span class="font-medium">{{ $purchaseOrder->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <div class="text-xs text-gray-500">
                                    <strong>Items:</strong>
                                    @foreach($purchaseOrder->purchaseOrderItems->take(3) as $item)
                                        {{ $item->product->name }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                    @if($purchaseOrder->purchaseOrderItems->count() > 3)
                                        <span class="text-blue-600">+{{ $purchaseOrder->purchaseOrderItems->count() - 3 }} more</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Selected Purchase Order Details -->
                <div id="selected-po-details" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Selected Purchase Order Details</h3>
                    <div id="po-details-content"></div>
                    <div class="mt-6">
                        <a href="#" id="proceed-to-receipt" class="btn btn-success">
                            <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Proceed to Record Receipt
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Purchase Orders Available</h3>
                <p class="mt-1 text-sm text-gray-500">
                    There are no approved or fulfilled purchase orders that haven't been received yet for your branch.
                </p>
                <div class="mt-6">
                    <a href="{{ route('branch.purchase-entries.index') }}" class="btn btn-primary">
                        View All Purchase Entries
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const purchaseOrderCards = document.querySelectorAll('.purchase-order-card');
    const selectedPODetails = document.getElementById('selected-po-details');
    const poDetailsContent = document.getElementById('po-details-content');
    const proceedButton = document.getElementById('proceed-to-receipt');
    let selectedPOId = null;

    purchaseOrderCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove previous selection
            purchaseOrderCards.forEach(c => c.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50'));
            
            // Add selection to clicked card
            this.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
            
            // Get selected PO data
            selectedPOId = this.dataset.poId;
            const poNumber = this.dataset.poNumber;
            const vendor = this.dataset.vendor;
            const status = this.dataset.status;
            const itemsCount = this.dataset.itemsCount;
            const totalAmount = this.dataset.totalAmount;
            const createdAt = this.dataset.createdAt;
            
            // Update details section
            poDetailsContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-600">Purchase Order:</span>
                        <p class="text-lg font-semibold text-gray-900">${poNumber}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Vendor:</span>
                        <p class="text-lg font-semibold text-gray-900">${vendor}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Status:</span>
                        <p class="text-lg font-semibold text-gray-900">${status === 'approved' ? 'Approved' : 'Fulfilled'}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Items:</span>
                        <p class="text-lg font-semibold text-gray-900">${itemsCount}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Total Amount:</span>
                        <p class="text-lg font-semibold text-green-600">₹${parseFloat(totalAmount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Order Date:</span>
                        <p class="text-lg font-semibold text-gray-900">${createdAt}</p>
                    </div>
                </div>
            `;
            
            // Update proceed button link
            proceedButton.href = `/branch/purchase-entries/${selectedPOId}/create-receipt`;
            
            // Show details section
            selectedPODetails.classList.remove('hidden');
        });
    });
});
</script>
@endsection