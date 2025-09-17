@extends('layouts.app')

@section('title', 'Purchase Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Purchase Order Details</h1>
            <p class="text-gray-600">Order #{{ $purchaseOrder->po_number }} - Detailed quantity tracking and receipt history</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('enhanced-purchase-entries.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
            @if($orderStats['completion_percentage'] < 100)
                <a href="{{ route('enhanced-purchase-entries.create') }}?order_id={{ $purchaseOrder->id }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Receipt Entry
                </a>
            @endif
        </div>
    </div>

    <!-- Order Summary Cards -->
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
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($orderStats['total_expected'], 2) }}</p>
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
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($orderStats['total_received'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Remaining Quantity</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($orderStats['total_remaining'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11m-6 0a2 2 0 012-2h4a2 2 0 012 2m-6 0h6" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Receipt Entries</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $orderStats['receipt_count'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Progress -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Order Completion Progress</h3>
            <span class="text-2xl font-bold text-gray-900">{{ number_format($orderStats['completion_percentage'], 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-green-600 h-4 rounded-full transition-all duration-300" style="width: {{ min(100, $orderStats['completion_percentage']) }}%"></div>
        </div>
        <div class="flex justify-between text-sm text-gray-600 mt-2">
            <span>0</span>
            <span>{{ number_format($orderStats['total_received'], 2) }} / {{ number_format($orderStats['total_expected'], 2) }}</span>
            <span>{{ number_format($orderStats['total_expected'], 2) }}</span>
        </div>
    </div>

    <!-- Order Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Order Details -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Order Number:</span>
                    <span class="font-medium">{{ $purchaseOrder->po_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Vendor:</span>
                    <span class="font-medium">{{ $purchaseOrder->vendor ? $purchaseOrder->vendor->name : 'Admin' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Order Date:</span>
                    <span class="font-medium">{{ $purchaseOrder->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium">{{ ucfirst($purchaseOrder->status) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Receive Status:</span>
                    <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $purchaseOrder->receive_status)) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="font-medium">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Loss Summary -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Loss Summary</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Spoiled:</span>
                    <span class="font-medium text-red-600">{{ number_format($orderStats['total_spoiled'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Damaged:</span>
                    <span class="font-medium text-red-600">{{ number_format($orderStats['total_damaged'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Usable:</span>
                    <span class="font-medium text-green-600">{{ number_format($orderStats['total_usable'], 2) }}</span>
                </div>
                <div class="flex justify-between border-t pt-3">
                    <span class="text-gray-600">Total Loss:</span>
                    <span class="font-medium text-red-600">{{ number_format($orderStats['total_spoiled'] + $orderStats['total_damaged'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Item-wise Tracking -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Item-wise Quantity Tracking</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Expected</th>
                        <th>Received</th>
                        <th>Remaining</th>
                        <th>Spoiled</th>
                        <th>Damaged</th>
                        <th>Usable</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itemTracking as $tracking)
                        <tr class="hover:bg-gray-50">
                            <td>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $tracking['item']->product->name }}</div>
                                    <div class="text-sm text-gray-500">₹{{ number_format($tracking['item']->unit_price, 2) }}/unit</div>
                                </div>
                            </td>
                            <td>
                                <span class="text-gray-900 font-medium">{{ $tracking['item']->product->sku }}</span>
                            </td>
                            <td>
                                <span class="text-gray-900 font-medium">{{ number_format($tracking['expected_quantity'], 2) }}</span>
                            </td>
                            <td>
                                <span class="text-green-600 font-medium">{{ number_format($tracking['received_quantity'], 2) }}</span>
                            </td>
                            <td>
                                @if($tracking['remaining_quantity'] > 0)
                                    <span class="text-orange-600 font-medium">{{ number_format($tracking['remaining_quantity'], 2) }}</span>
                                @else
                                    <span class="text-green-600 font-medium">0</span>
                                @endif
                            </td>
                            <td>
                                @if($tracking['spoiled_quantity'] > 0)
                                    <span class="text-red-600 font-medium">{{ number_format($tracking['spoiled_quantity'], 2) }}</span>
                                @else
                                    <span class="text-gray-500">0</span>
                                @endif
                            </td>
                            <td>
                                @if($tracking['damaged_quantity'] > 0)
                                    <span class="text-red-600 font-medium">{{ number_format($tracking['damaged_quantity'], 2) }}</span>
                                @else
                                    <span class="text-gray-500">0</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-green-600 font-medium">{{ number_format($tracking['usable_quantity'], 2) }}</span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(100, $tracking['completion_percentage']) }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ number_format($tracking['completion_percentage'], 1) }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($tracking['is_complete'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Complete
                                    </span>
                                @elseif($tracking['completion_percentage'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        Partial
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Receipt History -->
    @if($purchaseOrder->purchaseEntries->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Receipt History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Entry Number</th>
                            <th>Entry Date</th>
                            <th>Received Qty</th>
                            <th>Spoiled Qty</th>
                            <th>Damaged Qty</th>
                            <th>Usable Qty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->purchaseEntries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td>
                                    <a href="{{ route('enhanced-purchase-entries.entry', $entry) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{ $entry->entry_number }}
                                    </a>
                                </td>
                                <td>{{ $entry->entry_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="text-green-600 font-medium">{{ number_format($entry->total_received_quantity, 2) }}</span>
                                </td>
                                <td>
                                    @if($entry->total_spoiled_quantity > 0)
                                        <span class="text-red-600 font-medium">{{ number_format($entry->total_spoiled_quantity, 2) }}</span>
                                    @else
                                        <span class="text-gray-500">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($entry->total_damaged_quantity > 0)
                                        <span class="text-red-600 font-medium">{{ number_format($entry->total_damaged_quantity, 2) }}</span>
                                    @else
                                        <span class="text-gray-500">0</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-green-600 font-medium">{{ number_format($entry->total_usable_quantity, 2) }}</span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entry->getStatusBadgeClass() }}">
                                        {{ $entry->getStatusDisplayText() }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('enhanced-purchase-entries.entry', $entry) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection