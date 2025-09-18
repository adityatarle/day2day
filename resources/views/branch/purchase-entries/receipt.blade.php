@extends('layouts.app')

@section('title', 'Delivery Receipt')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('branch.purchase-entries.show', $purchaseEntry) }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Purchase Entry
        </a>
        <div></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Delivery Receipt</h1>
                <p class="text-gray-600">Order #{{ $purchaseEntry->po_number }} | Branch: {{ $purchaseEntry->branch->name }}</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-700">Received On</div>
                <div class="text-lg font-semibold text-gray-900">{{ $purchaseEntry->received_at->format('M d, Y H:i') }}</div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-xs text-gray-500">Subtotal</div>
                <div class="text-sm font-medium text-gray-900">₹{{ number_format($purchaseEntry->subtotal ?? ($purchaseEntry->total_amount - ($purchaseEntry->tax_amount ?? 0) - ($purchaseEntry->transport_cost ?? 0)), 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-xs text-gray-500">Taxes</div>
                <div class="text-sm font-medium text-gray-900">₹{{ number_format($purchaseEntry->tax_amount ?? 0, 2) }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-xs text-gray-500">Grand Total</div>
                <div class="text-sm font-semibold text-gray-900">₹{{ number_format($purchaseEntry->total_amount ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-xs text-gray-500">Delivery Person</div>
                <div class="text-sm font-medium text-gray-900">{{ $purchaseEntry->delivery_person ?? '-' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-xs text-gray-500">Vehicle</div>
                <div class="text-sm font-medium text-gray-900">{{ $purchaseEntry->delivery_vehicle ?? '-' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-xs text-gray-500">Notes</div>
                <div class="text-sm font-medium text-gray-900">{{ $purchaseEntry->delivery_notes ?? '-' }}</div>
            </div>
        </div>

        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spoiled</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usable</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight Diff (kg)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quality Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($purchaseEntry->purchaseOrderItems as $item)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                            <div class="text-xs text-gray-500">{{ $item->product->category }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->fulfilled_quantity ?? $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->actual_received_quantity ?? 0, 2, '.', ''), '0'), '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->spoiled_quantity ?? 0, 2, '.', ''), '0'), '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->damaged_quantity ?? 0, 2, '.', ''), '0'), '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->usable_quantity ?? 0, 2, '.', ''), '0'), '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ rtrim(rtrim(number_format($item->weight_difference ?? 0, 2, '.', ''), '0'), '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->quality_notes ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-xs text-green-700">Total Expected</div>
                <div class="text-xl font-bold text-green-900">{{ rtrim(rtrim(number_format($discrepancySummary['expected'], 2, '.', ''), '0'), '.') }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-xs text-blue-700">Total Received</div>
                <div class="text-xl font-bold text-blue-900">{{ rtrim(rtrim(number_format($discrepancySummary['received'], 2, '.', ''), '0'), '.') }}</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-xs text-yellow-700">Loss %</div>
                <div class="text-xl font-bold text-yellow-900">{{ number_format($discrepancySummary['loss_percentage'], 2) }}%</div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ route('purchase-orders.pdf', $purchaseEntry) }}" target="_blank" class="btn btn-secondary">Download PDF</a>
            <button onclick="window.print()" class="ml-2 btn btn-primary">Print</button>
        </div>
    </div>
</div>
@endsection

