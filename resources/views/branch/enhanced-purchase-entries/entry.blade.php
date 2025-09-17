@extends('layouts.app')

@section('title', 'Purchase Entry Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Purchase Entry Details</h1>
            <p class="text-gray-600">Entry #{{ $purchaseEntry->entry_number }} - Detailed receipt information</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('enhanced-purchase-entries.show', $purchaseEntry->purchaseOrder) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Order
            </a>
        </div>
    </div>

    <!-- Entry Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Expected Quantity</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($purchaseEntry->total_expected_quantity, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Received Quantity</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($purchaseEntry->total_received_quantity, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Loss</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($purchaseEntry->total_spoiled_quantity + $purchaseEntry->total_damaged_quantity, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Usable Quantity</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($purchaseEntry->total_usable_quantity, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Entry Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Entry Details -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Entry Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Entry Number:</span>
                    <span class="font-medium">{{ $purchaseEntry->entry_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Order Number:</span>
                    <span class="font-medium">{{ $purchaseEntry->purchaseOrder->po_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Entry Date:</span>
                    <span class="font-medium">{{ $purchaseEntry->entry_date->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Delivery Date:</span>
                    <span class="font-medium">{{ $purchaseEntry->delivery_date ? $purchaseEntry->delivery_date->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $purchaseEntry->getStatusBadgeClass() }}">
                        {{ $purchaseEntry->getStatusDisplayText() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Receipt Type:</span>
                    <span class="font-medium">{{ $purchaseEntry->is_partial_receipt ? 'Partial' : 'Complete' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Created By:</span>
                    <span class="font-medium">{{ $purchaseEntry->user->name }}</span>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Delivery Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Delivery Person:</span>
                    <span class="font-medium">{{ $purchaseEntry->delivery_person ?: 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Delivery Vehicle:</span>
                    <span class="font-medium">{{ $purchaseEntry->delivery_vehicle ?: 'N/A' }}</span>
                </div>
                @if($purchaseEntry->delivery_notes)
                <div class="mt-4">
                    <span class="text-gray-600 block mb-2">Delivery Notes:</span>
                    <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $purchaseEntry->delivery_notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Weight Information -->
    @if($purchaseEntry->total_expected_weight || $purchaseEntry->total_actual_weight)
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Weight Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <p class="text-sm text-gray-600">Expected Weight</p>
                <p class="text-2xl font-bold text-gray-900">{{ $purchaseEntry->total_expected_weight ? number_format($purchaseEntry->total_expected_weight, 3) . ' kg' : 'N/A' }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Actual Weight</p>
                <p class="text-2xl font-bold text-gray-900">{{ $purchaseEntry->total_actual_weight ? number_format($purchaseEntry->total_actual_weight, 3) . ' kg' : 'N/A' }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Weight Difference</p>
                <p class="text-2xl font-bold {{ $purchaseEntry->total_weight_difference > 0 ? 'text-green-600' : ($purchaseEntry->total_weight_difference < 0 ? 'text-red-600' : 'text-gray-900') }}">
                    {{ $purchaseEntry->total_weight_difference ? number_format($purchaseEntry->total_weight_difference, 3) . ' kg' : 'N/A' }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Item Details -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Item Details</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Expected</th>
                        <th>Received</th>
                        <th>Spoiled</th>
                        <th>Damaged</th>
                        <th>Usable</th>
                        <th>Weight (kg)</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseEntry->purchaseEntryItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                    <div class="text-sm text-gray-500">SKU: {{ $item->product->sku }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="text-gray-900 font-medium">{{ number_format($item->expected_quantity, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-green-600 font-medium">{{ number_format($item->received_quantity, 2) }}</span>
                            </td>
                            <td>
                                @if($item->spoiled_quantity > 0)
                                    <span class="text-red-600 font-medium">{{ number_format($item->spoiled_quantity, 2) }}</span>
                                @else
                                    <span class="text-gray-500">0</span>
                                @endif
                            </td>
                            <td>
                                @if($item->damaged_quantity > 0)
                                    <span class="text-red-600 font-medium">{{ number_format($item->damaged_quantity, 2) }}</span>
                                @else
                                    <span class="text-gray-500">0</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-green-600 font-medium">{{ number_format($item->usable_quantity, 2) }}</span>
                            </td>
                            <td>
                                @if($item->actual_weight)
                                    <span class="text-gray-900 font-medium">{{ number_format($item->actual_weight, 3) }}</span>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-gray-900 font-medium">₹{{ number_format($item->total_price, 2) }}</span>
                            </td>
                            <td>
                                @if($item->hasDiscrepancies())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Discrepancy
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Good
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Notes Section -->
    @if($purchaseEntry->quality_notes || $purchaseEntry->discrepancy_notes)
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
        <div class="space-y-4">
            @if($purchaseEntry->quality_notes)
            <div>
                <h4 class="font-medium text-gray-900 mb-2">Quality Notes</h4>
                <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $purchaseEntry->quality_notes }}</p>
            </div>
            @endif
            
            @if($purchaseEntry->discrepancy_notes)
            <div>
                <h4 class="font-medium text-gray-900 mb-2">Discrepancy Notes</h4>
                <p class="text-sm text-gray-700 bg-red-50 p-3 rounded-lg">{{ $purchaseEntry->discrepancy_notes }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection