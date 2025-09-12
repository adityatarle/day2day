@extends('layouts.app')

@section('title', 'Record Delivery Receipt')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('branch.purchase-entries.show', $purchaseEntry) }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Purchase Entry
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Record Receipt for #{{ $purchaseEntry->po_number }}</h1>
                <p class="text-gray-600">Branch: {{ $purchaseEntry->branch->name }}</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Delivered</span>
        </div>

        <form method="POST" action="{{ route('branch.purchase-entries.store-receipt', $purchaseEntry) }}" id="receipt-form" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Delivery Person</label>
                    <input type="text" name="delivery_person" class="form-input" placeholder="Name of the delivery person">
                </div>
                <div>
                    <label class="form-label">Delivery Vehicle</label>
                    <input type="text" name="delivery_vehicle" class="form-input" placeholder="Vehicle number/details">
                </div>
                <div class="md:col-span-3">
                    <label class="form-label">Delivery Notes</label>
                    <textarea name="delivery_notes" rows="2" class="form-input" placeholder="Any delivery notes or observations"></textarea>
                </div>
            </div>

            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Wt (kg)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Wt (kg)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spoiled</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quality Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($purchaseEntry->purchaseOrderItems as $idx => $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->product->category }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->fulfilled_quantity ?? $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                            <td class="px-4 py-3">
                                <input type="hidden" name="received_items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][actual_received_quantity]" value="{{ $item->fulfilled_quantity ?? $item->quantity }}" class="form-input w-28">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][expected_weight]" class="form-input w-28" placeholder="0.00">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][actual_weight]" class="form-input w-28" placeholder="0.00">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][spoiled_quantity]" value="0" class="form-input w-24">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" min="0" name="received_items[{{ $idx }}][damaged_quantity]" value="0" class="form-input w-24">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="received_items[{{ $idx }}][quality_notes]" class="form-input w-full" placeholder="Optional notes">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('branch.purchase-entries.show', $purchaseEntry) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Save Receipt & Update Inventory</button>
            </div>
        </form>
    </div>
</div>
@endsection

