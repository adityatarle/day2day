@extends('layouts.super-admin')

@section('title', 'Branch Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.branch-orders.index') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Orders from Branches
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $branchOrder->po_number }}</h1>
                        <p class="text-gray-600">Requested by {{ $branchOrder->user->name }} for branch {{ $branchOrder->branch->name }}</p>
                    </div>
                    <div class="text-right">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $branchOrder->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : ($branchOrder->status === 'sent' ? 'bg-blue-100 text-blue-800' : ($branchOrder->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                {{ $branchOrder->status === 'sent' ? 'Approved' : ($branchOrder->status === 'confirmed' ? 'Fulfilled' : ucfirst($branchOrder->status)) }}
                            </span>
                        </div>
                        @if($branchOrder->priority)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $branchOrder->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($branchOrder->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($branchOrder->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($branchOrder->priority) }} Priority
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Branch</div>
                        <div class="text-sm font-medium text-gray-900">{{ $branchOrder->branch->name }} ({{ $branchOrder->branch->code }})</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Expected Delivery</div>
                        <div class="text-sm font-medium text-gray-900">{{ optional($branchOrder->expected_delivery_date)->format('M d, Y') ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Vendor</div>
                        <div class="text-sm font-medium text-gray-900">{{ $branchOrder->vendor?->name ?? 'Not assigned' }}</div>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Items</h2>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimated Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($branchOrder->purchaseOrderItems as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->category }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">₹{{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">₹{{ number_format($item->total_price ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($branchOrder->status === 'sent')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Fulfill Order</h2>
                    <a class="btn btn-success" href="{{ route('admin.branch-orders.fulfill-form', $branchOrder) }}">Open Fulfillment Form</a>
                </div>
                <p class="text-sm text-gray-600">Proceed to fulfill this order from existing stock or after procuring from vendor.</p>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Admin Actions</h2>

                @if($branchOrder->status === 'draft')
                <form method="POST" action="{{ route('admin.branch-orders.approve', $branchOrder) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label">Admin Notes (optional)</label>
                        <textarea name="admin_notes" rows="3" class="form-input" placeholder="Notes for approval or instructions"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary">Approve Order</button>
                        @if($branchOrder->canBeCancelled())
                        <button type="button" onclick="document.getElementById('cancel-form').classList.toggle('hidden')" class="btn btn-danger">Cancel Order</button>
                        @endif
                    </div>
                </form>
                @elseif($branchOrder->status === 'sent')
                <div class="space-y-4">
                    <div class="alert alert-info">
                        <strong>Order approved!</strong> Now you need to purchase materials from vendors before fulfilling this order.
                    </div>
                    
                    <!-- Create Vendor Purchase Order Form -->
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="font-semibold text-gray-900 mb-3">Step 1: Create Vendor Purchase Order</h3>
                        <form method="POST" action="{{ route('admin.branch-orders.create-vendor-po', $branchOrder) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="form-label">Select Vendor</label>
                                <select name="vendor_id" class="form-input" required>
                                    <option value="">Choose vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">
                                            {{ $vendor->name }} ({{ $vendor->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="expected_delivery_date" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label">Payment Terms</label>
                                    <select name="payment_terms" class="form-input" required>
                                        <option value="immediate">Immediate</option>
                                        <option value="7_days">7 Days</option>
                                        <option value="15_days">15 Days</option>
                                        <option value="30_days">30 Days</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Admin Notes (optional)</label>
                                <textarea name="admin_notes" rows="2" class="form-input" placeholder="Notes for vendor purchase order"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-full">Create Vendor Purchase Order</button>
                        </form>
                    </div>
                    
                    <!-- Fulfill Order Option -->
                    <div class="border rounded-lg p-4 bg-green-50">
                        <h3 class="font-semibold text-gray-900 mb-2">Step 2: Fulfill Order</h3>
                        <p class="text-sm text-gray-600 mb-3">After purchasing materials from vendors, fulfill this order to send materials to the branch.</p>
                        <a href="{{ route('admin.branch-orders.fulfill-form', $branchOrder) }}" class="btn btn-success w-full text-center">Fulfill Order from Stock</a>
                    </div>
                </div>
                @elseif($branchOrder->status === 'confirmed')
                <div class="alert alert-success">Order fulfilled on {{ optional($branchOrder->fulfilled_at)->format('M d, Y H:i') }}</div>
                @elseif($branchOrder->status === 'cancelled')
                <div class="alert alert-error">Order was cancelled.</div>
                @endif

                @if($branchOrder->canBeCancelled())
                <div id="cancel-form" class="mt-4 {{ $branchOrder->status === 'draft' ? 'hidden' : '' }}">
                    <form method="POST" action="{{ route('admin.branch-orders.cancel', $branchOrder) }}" class="space-y-3">
                        @csrf
                        <label class="form-label">Cancellation Reason</label>
                        <textarea name="cancellation_reason" rows="2" class="form-input" required></textarea>
                        <button type="submit" class="btn btn-danger w-full">Confirm Cancel</button>
                    </form>
                </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li>Requested: {{ $branchOrder->created_at->format('M d, Y H:i') }}</li>
                    @if($branchOrder->approved_at)
                    <li>Approved: {{ $branchOrder->approved_at->format('M d, Y H:i') }}</li>
                    @endif
                    @if($branchOrder->fulfilled_at)
                    <li>Fulfilled: {{ $branchOrder->fulfilled_at->format('M d, Y H:i') }}</li>
                    @endif
                    @if($branchOrder->received_at)
                    <li>Receipt Recorded by Branch: {{ $branchOrder->received_at->format('M d, Y H:i') }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

