@extends('layouts.app')

@section('title', 'Product Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('branch.product-orders.index') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Product Orders
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Header Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $productOrder->po_number }}</h1>
                        <p class="text-gray-600">Branch: {{ $productOrder->branch->name }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $productOrder->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : ($productOrder->status === 'sent' ? 'bg-blue-100 text-blue-800' : ($productOrder->status === 'fulfilled' ? 'bg-green-100 text-green-800' : ($productOrder->status === 'received' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'))) }}">
                            <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            {{ ucfirst($productOrder->status) }}
                        </span>
                        @if($productOrder->priority)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $productOrder->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($productOrder->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($productOrder->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                {{ ucfirst($productOrder->priority) }} Priority
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Order Progress Bar -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-medium text-gray-700">Order Progress</h3>
                        <span class="text-sm text-gray-600">{{ number_format(($productOrder->total_received_quantity / max($productOrder->total_ordered_quantity, 1)) * 100, 1) }}% Complete</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ ($productOrder->total_received_quantity / max($productOrder->total_ordered_quantity, 1)) * 100 }}%"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-600">
                        <span>Ordered: {{ number_format($productOrder->total_ordered_quantity, 2) }}</span>
                        <span class="text-green-600 font-medium">Received: {{ number_format($productOrder->total_received_quantity, 2) }}</span>
                        <span class="text-orange-600 font-medium">Remaining: {{ number_format($productOrder->total_ordered_quantity - $productOrder->total_received_quantity, 2) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center mb-2">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <div class="text-xs text-gray-500">Requested By</div>
                        </div>
                        <div class="text-sm font-medium text-gray-900">{{ $productOrder->user->name }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center mb-2">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div class="text-xs text-gray-500">Expected Delivery</div>
                        </div>
                        <div class="text-sm font-medium text-gray-900">{{ optional($productOrder->expected_delivery_date)->format('M d, Y') ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center mb-2">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-xs text-gray-500">Receive Status</div>
                        </div>
                        <div class="text-sm font-medium text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $productOrder->getReceiveStatusBadgeClass() }}">{{ $productOrder->getReceiveStatusDisplayText() }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Order Items</h2>
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($itemTracking as $tracking)
                                @php
                                    $progressPercent = $tracking['ordered_quantity'] > 0 ? ($tracking['received_quantity'] / $tracking['ordered_quantity']) * 100 : 0;
                                    $isComplete = $tracking['remaining_quantity'] <= 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $tracking['item']->product->name }}</div>
                                                <div class="text-xs text-gray-500">SKU: {{ $tracking['item']->product->sku }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="text-sm font-medium text-gray-900">{{ number_format($tracking['ordered_quantity'], 2) }}</div>
                                        <div class="text-xs text-gray-500">units</div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="text-sm font-medium {{ $tracking['received_quantity'] > 0 ? 'text-green-600' : 'text-gray-400' }}">{{ number_format($tracking['received_quantity'], 2) }}</div>
                                        <div class="text-xs text-gray-500">units</div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="text-sm font-medium {{ $tracking['remaining_quantity'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ number_format($tracking['remaining_quantity'], 2) }}</div>
                                        <div class="text-xs text-gray-500">units</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="w-full">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-xs font-medium {{ $isComplete ? 'text-green-600' : 'text-gray-600' }}">{{ number_format($progressPercent, 0) }}%</span>
                                                @if($isComplete)
                                                <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                @endif
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="{{ $isComplete ? 'bg-green-500' : 'bg-blue-500' }} h-1.5 rounded-full transition-all duration-300" style="width: {{ min($progressPercent, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-900">₹{{ number_format($tracking['unit_price'], 2) }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="text-sm font-medium text-gray-900">₹{{ number_format($tracking['total_price'], 2) }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <!-- Order Status Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Status</h2>
                
                <!-- Status Timeline -->
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="{{ in_array($productOrder->status, ['draft', 'sent', 'fulfilled', 'received']) ? 'bg-green-500' : 'bg-gray-300' }} h-8 w-8 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Order Created</p>
                            <p class="text-xs text-gray-500">{{ $productOrder->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                    
                    @if(in_array($productOrder->status, ['sent', 'fulfilled', 'received']))
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-green-500 h-8 w-8 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Sent to Admin</p>
                            <p class="text-xs text-gray-500">Admin will process and fulfill</p>
                        </div>
                    </div>
                    @endif
                    
                    @if(in_array($productOrder->status, ['fulfilled', 'received']) || $productOrder->receive_status !== 'not_received')
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="{{ $productOrder->receive_status === 'complete' ? 'bg-green-500' : ($productOrder->receive_status === 'partial' ? 'bg-yellow-500' : 'bg-gray-300') }} h-8 w-8 rounded-full flex items-center justify-center">
                                @if($productOrder->receive_status === 'complete')
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                @elseif($productOrder->receive_status === 'partial')
                                <span class="text-white text-xs font-bold">{{ number_format(($productOrder->total_received_quantity / max($productOrder->total_ordered_quantity, 1)) * 100, 0) }}%</span>
                                @else
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Materials Received</p>
                            <p class="text-xs text-gray-500">{{ $productOrder->getReceiveStatusDisplayText() }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Action Messages -->
                <div class="mt-6">
                    @if($productOrder->status === 'draft')
                        <div class="rounded-lg bg-blue-50 p-4">
                            <p class="text-sm text-blue-800">Your order has been created and will be sent to admin for processing.</p>
                        </div>
                        <a href="{{ route('branch.product-orders.edit', $productOrder) }}" class="mt-3 btn btn-primary w-full text-center">Edit Order</a>
                    @elseif($productOrder->status === 'sent')
                        <div class="rounded-lg bg-green-50 p-4">
                            <p class="text-sm text-green-800">Admin has received your order and is processing your request.</p>
                        </div>
                    @elseif($productOrder->status === 'fulfilled' || $productOrder->status === 'received')
                        @if($productOrder->receive_status === 'complete')
                            <div class="rounded-lg bg-green-50 p-4">
                                <p class="text-sm text-green-800">All items have been received successfully!</p>
                            </div>
                        @elseif($productOrder->receive_status === 'partial')
                            <div class="rounded-lg bg-yellow-50 p-4">
                                <p class="text-sm text-yellow-800">Some items are still pending delivery.</p>
                            </div>
                        @else
                            <div class="rounded-lg bg-orange-50 p-4">
                                <p class="text-sm text-orange-800">Order is ready for receiving. Please record receipt when materials arrive.</p>
                            </div>
                        @endif
                    @elseif($productOrder->status === 'cancelled')
                        <div class="rounded-lg bg-red-50 p-4">
                            <p class="text-sm text-red-800">This order was cancelled by admin.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Financial Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Financial Details</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="font-medium">₹{{ number_format($financials['subtotal'], 2) }}</span></div>
                    @if(($financials['discount'] ?? 0) > 0)
                        <div class="flex justify-between"><span class="text-gray-600">Discounts</span><span class="font-medium text-green-700">-₹{{ number_format($financials['discount'], 2) }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-gray-600">GST</span><span class="font-medium">₹{{ number_format($financials['tax_total'], 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Transport</span><span class="font-medium">₹{{ number_format($financials['transport'], 2) }}</span></div>
                    <div class="border-t pt-2 flex justify-between text-base font-semibold"><span>Grand Total</span><span>₹{{ number_format($financials['grand_total'], 2) }}</span></div>
                </div>
                <div class="mt-4 text-xs text-gray-500">GST may include CGST/SGST or IGST as applicable.</div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('purchase-orders.pdf', $productOrder) }}" target="_blank" class="btn btn-secondary w-full text-center">Download PDF</a>
                </div>
                @if(config('app.debug'))
                <form method="POST" action="{{ route('branch.product-orders.sync-aggregates', $productOrder) }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-warning w-full text-center">Force Sync</button>
                </form>
                @endif
            </div>

            <!-- Receive Remaining Items Action -->
            @php
                $hasRemaining = collect($itemTracking)->some(fn($t) => $t['remaining_quantity'] > 0);
            @endphp
            @if($hasRemaining)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Actions</h2>
                <button type="button" onclick="document.getElementById('receiveModal').classList.remove('hidden')" class="btn btn-success w-full">Receive Remaining Items</button>
            </div>
            @endif
            @if($productOrder->purchaseEntries->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Receipt History</h2>
                <div class="space-y-3">
                    @foreach($productOrder->purchaseEntries as $entry)
                    <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors duration-150">
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('enhanced-purchase-entries.entry', $entry) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ $entry->entry_number }}
                            </a>
                            <span class="text-xs text-gray-500">{{ $entry->entry_date->format('M d, Y') }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div>
                                <span class="text-gray-500">Received:</span>
                                <span class="font-medium text-green-600 ml-1">{{ number_format($entry->total_received_quantity, 2) }}</span>
                            </div>
                            @if($entry->total_spoiled_quantity > 0)
                            <div>
                                <span class="text-gray-500">Spoiled:</span>
                                <span class="font-medium text-red-600 ml-1">{{ number_format($entry->total_spoiled_quantity, 2) }}</span>
                            </div>
                            @endif
                            @if($entry->total_damaged_quantity > 0)
                            <div>
                                <span class="text-gray-500">Damaged:</span>
                                <span class="font-medium text-orange-600 ml-1">{{ number_format($entry->total_damaged_quantity, 2) }}</span>
                            </div>
                            @endif
                        </div>
                        @if($entry->is_partial_receipt)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                Partial Receipt
                            </span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                
                <!-- Receipt Summary -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="text-sm space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Receipts:</span>
                            <span class="font-medium">{{ $productOrder->purchaseEntries->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Received:</span>
                            <span class="font-medium text-green-600">{{ number_format($productOrder->purchaseEntries->sum('total_received_quantity'), 2) }}</span>
                        </div>
                        @if($productOrder->purchaseEntries->sum('total_spoiled_quantity') > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Spoiled:</span>
                            <span class="font-medium text-red-600">{{ number_format($productOrder->purchaseEntries->sum('total_spoiled_quantity'), 2) }}</span>
                        </div>
                        @endif
                        @if($productOrder->purchaseEntries->sum('total_damaged_quantity') > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Damaged:</span>
                            <span class="font-medium text-orange-600">{{ number_format($productOrder->purchaseEntries->sum('total_damaged_quantity'), 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Receive Remaining Items Modal -->
<div id="receiveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-40" onclick="document.getElementById('receiveModal').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Receive Remaining Items - {{ $productOrder->po_number }}</h3>
            <button class="text-gray-500 hover:text-gray-700" onclick="document.getElementById('receiveModal').classList.add('hidden')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('enhanced-purchase-entries.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="purchase_order_id" value="{{ $productOrder->id }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Entry Date *</label>
                    <input type="date" name="entry_date" value="{{ now()->format('Y-m-d') }}" class="form-input" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Delivery Notes</label>
                    <textarea name="delivery_notes" rows="2" class="form-input" placeholder="Optional notes"></textarea>
                </div>
            </div>

            <div class="overflow-x-auto border rounded-lg max-h-96">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receive Now</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spoiled</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $rowIndex = 0; @endphp
                        @foreach($itemTracking as $t)
                            @if($t['remaining_quantity'] > 0)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $t['item']->product->name }}</div>
                                        <div class="text-xs text-gray-500">SKU: {{ $t['item']->product->sku }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-orange-600 font-medium">{{ number_format($t['remaining_quantity'], 2) }}</td>
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="items[{{ $rowIndex }}][item_id]" value="{{ $t['item']->id }}">
                                        <input type="number" name="items[{{ $rowIndex }}][received_quantity]" step="0.01" min="0" max="{{ $t['remaining_quantity'] }}" value="{{ $t['remaining_quantity'] }}" class="form-input w-28">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="items[{{ $rowIndex }}][spoiled_quantity]" step="0.01" min="0" value="0" class="form-input w-24">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="items[{{ $rowIndex }}][damaged_quantity]" step="0.01" min="0" value="0" class="form-input w-24">
                                    </td>
                                </tr>
                                @php $rowIndex++; @endphp
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('receiveModal').classList.add('hidden')">Cancel</button>
                <button type="submit" class="btn btn-success">Save Receipt</button>
            </div>
        </form>
    </div>
</div>
@endsection

