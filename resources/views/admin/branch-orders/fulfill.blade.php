@extends('layouts.app')

@section('title', 'Fulfill Branch Order')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.branch-orders.show', $branchOrder) }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Order Details
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Fulfill Order #{{ $branchOrder->po_number }}</h1>
                <p class="text-gray-600">Branch: {{ $branchOrder->branch->name }} | Vendor: {{ $branchOrder->vendor?->name ?? 'Not assigned' }}</p>
            </div>
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Approved</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.branch-orders.fulfill', $branchOrder) }}" id="fulfill-form" class="space-y-6">
            @csrf
            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fulfill Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight Diff (kg)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spoiled Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
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
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="fulfilled_items[{{ $loop->index }}][fulfilled_quantity]" class="form-input w-32" value="{{ $item->quantity }}">
                                <input type="hidden" name="fulfilled_items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" name="fulfilled_items[{{ $loop->index }}][weight_difference]" class="form-input w-28" placeholder="0">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="fulfilled_items[{{ $loop->index }}][spoiled_quantity]" class="form-input w-28" value="0">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="fulfilled_items[{{ $loop->index }}][notes]" class="form-input w-full" placeholder="Optional notes">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.branch-orders.show', $branchOrder) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Mark as Fulfilled & Update Inventory</button>
            </div>
        </form>
    </div>
</div>
@endsection

