@extends('layouts.app')

@section('title', 'Purchase Entry Details')

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $purchaseEntry->po_number }}</h1>
                        <p class="text-gray-600">Vendor: {{ $purchaseEntry->vendor?->name ?? 'Admin Fulfillment' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $purchaseEntry->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ $purchaseEntry->status === 'approved' ? 'Awaiting Delivery' : 'Delivered' }}
                        </span>
                        @if($purchaseEntry->received_at)
                        <div class="mt-2 text-xs text-green-700">Receipt recorded on {{ $purchaseEntry->received_at->format('M d, Y') }}</div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Branch</div>
                        <div class="text-sm font-medium text-gray-900">{{ $purchaseEntry->branch->name }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Approved On</div>
                        <div class="text-sm font-medium text-gray-900">{{ optional($purchaseEntry->approved_at)->format('M d, Y') ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-xs text-gray-500">Delivered On</div>
                        <div class="text-sm font-medium text-gray-900">{{ optional($purchaseEntry->fulfilled_at)->format('M d, Y') ?? '-' }}</div>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Items</h2>
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
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($purchaseEntry->status === 'fulfilled' && !$purchaseEntry->received_at)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Record Delivery Receipt</h2>
                    <a class="btn btn-success" href="{{ route('branch.purchase-entries.create-receipt', $purchaseEntry) }}">Open Receipt Form</a>
                </div>
                <p class="text-sm text-gray-600">Record actual received quantities and discrepancies like spoiled or damaged goods.</p>
            </div>
            @endif
        </div>

        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Delivery Details</h2>
                <ul class="text-sm text-gray-700 space-y-2">
                    <li>Delivery Person: {{ $purchaseEntry->delivery_person ?? '-' }}</li>
                    <li>Vehicle: {{ $purchaseEntry->delivery_vehicle ?? '-' }}</li>
                    <li>Notes: {{ $purchaseEntry->delivery_notes ?? '-' }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

