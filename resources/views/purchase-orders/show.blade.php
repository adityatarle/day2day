@extends('layouts.app')

@section('title', 'Purchase Order - ' . $purchaseOrder->po_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('purchase-orders.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $purchaseOrder->po_number }}</h1>
                        <p class="text-gray-600">Purchase Order Details</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}" target="_blank" 
                           class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download PDF
                        </a>
                        @if($purchaseOrder->isDraft())
                            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" 
                               class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                        @endif
                        @if($purchaseOrder->isConfirmed())
                            <a href="{{ route('purchase-orders.receive-form', $purchaseOrder) }}" 
                               class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                Receive Order
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status & Actions Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium status-{{ $purchaseOrder->status }}">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
                <div class="text-sm text-gray-600">
                    Created {{ $purchaseOrder->created_at->format('M d, Y') }} by {{ $purchaseOrder->user->name }}
                </div>
            </div>
            <div class="flex gap-2">
                @if($purchaseOrder->isDraft())
                    <form method="POST" action="{{ route('purchase-orders.send', $purchaseOrder) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Send to Vendor
                        </button>
                    </form>
                @elseif($purchaseOrder->isSent())
                    <form method="POST" action="{{ route('purchase-orders.confirm', $purchaseOrder) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Confirm Order
                        </button>
                    </form>
                @endif
                
                @if(!$purchaseOrder->isReceived() && !$purchaseOrder->isCancelled())
                    <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to cancel this purchase order?')" 
                                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel Order
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Purchase Order Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Order Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Vendor</label>
                            <p class="text-gray-900 font-medium">{{ $purchaseOrder->vendor->name }}</p>
                            <p class="text-sm text-gray-600">{{ $purchaseOrder->vendor->code }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Branch</label>
                            <p class="text-gray-900">{{ $purchaseOrder->branch->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Payment Terms</label>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $purchaseOrder->payment_terms)) }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Expected Delivery</label>
                            <p class="text-gray-900">{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('M d, Y') : '-' }}</p>
                            @if($purchaseOrder->expected_delivery_date && $purchaseOrder->expected_delivery_date->isPast() && !$purchaseOrder->isReceived())
                                <span class="text-red-600 text-sm font-medium">Overdue by {{ $purchaseOrder->expected_delivery_date->diffForHumans() }}</span>
                            @endif
                        </div>
                        @if($purchaseOrder->actual_delivery_date)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Actual Delivery</label>
                            <p class="text-gray-900">{{ $purchaseOrder->actual_delivery_date->format('M d, Y') }}</p>
                        </div>
                        @endif
                        @if($purchaseOrder->notes)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Notes</label>
                            <p class="text-gray-900">{{ $purchaseOrder->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">₹{{ number_format($purchaseOrder->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">GST (18%)</span>
                        <span class="font-medium">₹{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Transport Cost</span>
                        <span class="font-medium">₹{{ number_format($purchaseOrder->transport_cost, 2) }}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <div class="flex justify-between">
                            <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                            <span class="text-lg font-bold text-green-600">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendor Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Information</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium">{{ $purchaseOrder->vendor->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-medium">{{ $purchaseOrder->vendor->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Address</p>
                        <p class="font-medium">{{ $purchaseOrder->vendor->address }}</p>
                    </div>
                    @if($purchaseOrder->vendor->gst_number)
                    <div>
                        <p class="text-sm text-gray-600">GST Number</p>
                        <p class="font-medium">{{ $purchaseOrder->vendor->gst_number }}</p>
                    </div>
                    @endif
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('vendors.show', $purchaseOrder->vendor) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        View Vendor Profile →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Order Items -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Items</h2>
        
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Quantity Ordered</th>
                        @if($purchaseOrder->isReceived())
                            <th>Quantity Received</th>
                        @endif
                        <th>Unit Price</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->purchaseOrderItems as $item)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                <div class="text-sm text-gray-600">{{ $item->product->unit }}</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium category-{{ $item->product->category }}">
                                    {{ ucfirst($item->product->category) }}
                                </span>
                            </td>
                            <td class="font-medium">{{ number_format($item->quantity, 2) }} {{ $item->product->unit }}</td>
                            @if($purchaseOrder->isReceived())
                                <td class="font-medium {{ $item->received_quantity == $item->quantity ? 'text-green-600' : 'text-orange-600' }}">
                                    {{ number_format($item->received_quantity ?? 0, 2) }} {{ $item->product->unit }}
                                    @if($item->received_quantity != $item->quantity)
                                        <span class="text-xs text-gray-500 block">
                                            ({{ number_format(abs($item->quantity - ($item->received_quantity ?? 0)), 2) }} {{ $item->received_quantity < $item->quantity ? 'short' : 'excess' }})
                                        </span>
                                    @endif
                                </td>
                            @endif
                            <td class="font-medium">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="font-semibold text-green-600">₹{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300">
                        <td colspan="{{ $purchaseOrder->isReceived() ? '5' : '4' }}" class="text-right font-semibold">Total:</td>
                        <td class="font-bold text-green-600">₹{{ number_format($purchaseOrder->subtotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Status Timeline -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Timeline</h2>
        
        <div class="relative">
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
            
            <!-- Draft -->
            <div class="relative flex items-center mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center z-10">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="font-medium text-gray-900">Draft Created</p>
                    <p class="text-sm text-gray-600">{{ $purchaseOrder->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Sent -->
            @if(!$purchaseOrder->isDraft())
            <div class="relative flex items-center mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center z-10">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="font-medium text-gray-900">Sent to Vendor</p>
                    <p class="text-sm text-gray-600">{{ $purchaseOrder->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
            @endif

            <!-- Confirmed -->
            @if($purchaseOrder->isConfirmed() || $purchaseOrder->isReceived())
            <div class="relative flex items-center mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-orange-600 flex items-center justify-center z-10">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="font-medium text-gray-900">Order Confirmed</p>
                    <p class="text-sm text-gray-600">Vendor confirmed the order</p>
                </div>
            </div>
            @endif

            <!-- Received -->
            @if($purchaseOrder->isReceived())
            <div class="relative flex items-center">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-600 flex items-center justify-center z-10">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="font-medium text-gray-900">Order Received</p>
                    <p class="text-sm text-gray-600">{{ $purchaseOrder->actual_delivery_date ? $purchaseOrder->actual_delivery_date->format('M d, Y H:i') : 'Received' }}</p>
                </div>
            </div>
            @endif

            <!-- Cancelled -->
            @if($purchaseOrder->isCancelled())
            <div class="relative flex items-center">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-600 flex items-center justify-center z-10">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="font-medium text-gray-900">Order Cancelled</p>
                    <p class="text-sm text-gray-600">{{ $purchaseOrder->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50" id="success-message">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('success-message').style.display = 'none';
        }, 5000);
    </script>
@endif

@if(session('error'))
    <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg z-50" id="error-message">
        {{ session('error') }}
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('error-message').style.display = 'none';
        }, 5000);
    </script>
@endif
@endsection