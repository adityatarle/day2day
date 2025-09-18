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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $productOrder->po_number }}</h1>
                        <p class="text-gray-600">Branch: {{ $productOrder->branch->name }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productOrder->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : ($productOrder->status === 'sent' ? 'bg-blue-100 text-blue-800' : ($productOrder->status === 'fulfilled' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                            {{ ucfirst($productOrder->status) }}
                        </span>
                        @if($productOrder->priority)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productOrder->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($productOrder->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($productOrder->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($productOrder->priority) }} Priority
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Requested By</div>
                        <div class="text-sm font-medium text-gray-900">{{ $productOrder->user->name }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Expected Delivery</div>
                        <div class="text-sm font-medium text-gray-900">{{ optional($productOrder->expected_delivery_date)->format('M d, Y') ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Status</div>
                        <div class="text-sm font-medium text-gray-900">{{ ucfirst($productOrder->status) }}</div>
                        <div class="mt-1 text-xs">
                            <span class="inline-flex items-center px-2 py-0.5 rounded {{ $productOrder->getReceiveStatusBadgeClass() }}">{{ $productOrder->getReceiveStatusDisplayText() }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Order Items</h2>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($itemTracking as $tracking)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $tracking['item']->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $tracking['item']->product->sku }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($tracking['ordered_quantity'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-green-700 font-medium">{{ number_format($tracking['received_quantity'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm {{ $tracking['remaining_quantity'] > 0 ? 'text-orange-600' : 'text-gray-600' }} font-medium">{{ number_format($tracking['remaining_quantity'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">₹{{ number_format($tracking['unit_price'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">₹{{ number_format($tracking['total_price'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Order Status</h2>
                @if($productOrder->status === 'draft')
                    <div class="alert alert-info">Your order has been sent to admin. Admin will purchase materials and fulfill your request.</div>
                    <a href="{{ route('branch.product-orders.edit', $productOrder) }}" class="btn btn-primary w-full text-center">Edit Order</a>
                @elseif($productOrder->status === 'sent')
                    <div class="alert alert-success">Admin approved your order. Awaiting delivery.</div>
                    <div class="text-sm text-gray-600">Admin is processing your request and will purchase materials from vendors.</div>
                @elseif($productOrder->status === 'fulfilled')
                    <div class="alert alert-success">Order delivered by admin. Please record receipt in Purchase Entry if not done yet.</div>
                @elseif($productOrder->status === 'cancelled')
                    <div class="alert alert-error">Order was cancelled by admin.</div>
                @endif
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
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Receipt Entries</h2>
                <ul class="space-y-2 text-sm">
                    @foreach($productOrder->purchaseEntries as $entry)
                    <li class="flex justify-between">
                        <a href="{{ route('enhanced-purchase-entries.entry', $entry) }}" class="text-blue-600 hover:text-blue-800">{{ $entry->entry_number }}</a>
                        <span class="text-gray-600">{{ $entry->entry_date->format('M d, Y') }}</span>
                    </li>
                    @endforeach
                </ul>
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
                <div class="flex items-end gap-2">
                    <input type="checkbox" id="is_partial_receipt" name="is_partial_receipt" value="1" class="form-checkbox" checked>
                    <label for="is_partial_receipt" class="text-sm text-gray-700">This is a partial receipt</label>
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

