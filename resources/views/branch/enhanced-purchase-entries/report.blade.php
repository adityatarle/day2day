@extends('layouts.app')

@section('title', 'Purchase Entry Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Purchase Entry Report</h1>
            <p class="text-gray-600">Comprehensive analysis of purchase entries with detailed quantity tracking and loss analysis</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('enhanced-purchase-entries.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Overall Statistics -->
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
                    <p class="text-sm font-medium text-gray-600">Total Entries</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $overallStats['total_entries'] }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Total Received</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($overallStats['total_received'], 2) }}</p>
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
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($overallStats['total_loss'], 2) }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Loss Percentage</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($overallStats['loss_percentage'], 2) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loss Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Spoiled vs Damaged -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Loss Breakdown</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 rounded-lg bg-red-50 border border-red-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Spoiled Quantity</h4>
                            <p class="text-sm text-gray-600">Items that went bad</p>
                        </div>
                    </div>
                    <span class="text-2xl font-bold text-red-600">{{ number_format($overallStats['total_spoiled'], 2) }}</span>
                </div>

                <div class="flex items-center justify-between p-4 rounded-lg bg-orange-50 border border-orange-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Damaged Quantity</h4>
                            <p class="text-sm text-gray-600">Items damaged in transit</p>
                        </div>
                    </div>
                    <span class="text-2xl font-bold text-orange-600">{{ number_format($overallStats['total_damaged'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Efficiency Metrics -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Efficiency Metrics</h3>
            <div class="space-y-4">
                <div class="text-center p-4 rounded-lg bg-green-50 border border-green-200">
                    <div class="text-3xl font-bold text-green-600">{{ number_format($overallStats['total_usable'], 2) }}</div>
                    <div class="text-sm text-gray-600 mt-1">Usable Quantity</div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $overallStats['total_received'] > 0 ? number_format(($overallStats['total_usable'] / $overallStats['total_received']) * 100, 1) : 0 }}% of received
                    </div>
                </div>

                <div class="text-center p-4 rounded-lg bg-blue-50 border border-blue-200">
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($overallStats['total_expected'], 2) }}</div>
                    <div class="text-sm text-gray-600 mt-1">Expected Quantity</div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $overallStats['total_expected'] > 0 ? number_format(($overallStats['total_received'] / $overallStats['total_expected']) * 100, 1) : 0 }}% received
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <form method="GET" action="{{ route('enhanced-purchase-entries.report') }}" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter by Date Range</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-input">
                </div>

                <div>
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-input">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full btn-primary">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Filter
                    </button>
                </div>
            </div>

            @if(request()->hasAny(['date_from', 'date_to']))
                <div class="flex justify-end">
                    <a href="{{ route('enhanced-purchase-entries.report') }}" class="btn-secondary">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Detailed Entries Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Detailed Entries</h3>
        </div>
        @if($entries->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Entry Number</th>
                            <th>Order Number</th>
                            <th>Entry Date</th>
                            <th>Expected</th>
                            <th>Received</th>
                            <th>Spoiled</th>
                            <th>Damaged</th>
                            <th>Usable</th>
                            <th>Loss %</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td>
                                    <a href="{{ route('enhanced-purchase-entries.entry', $entry) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{ $entry->entry_number }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('enhanced-purchase-entries.show', $entry->purchaseOrder) }}" class="text-gray-600 hover:text-gray-800">
                                        {{ $entry->purchaseOrder->po_number }}
                                    </a>
                                </td>
                                <td>{{ $entry->entry_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="text-gray-900 font-medium">{{ number_format($entry->total_expected_quantity, 2) }}</span>
                                </td>
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
                                    <span class="font-medium {{ $entry->getLossPercentage() > 5 ? 'text-red-600' : ($entry->getLossPercentage() > 2 ? 'text-orange-600' : 'text-green-600') }}">
                                        {{ number_format($entry->getLossPercentage(), 2) }}%
                                    </span>
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
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No entries found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['date_from', 'date_to']))
                        No entries match your current date filters.
                    @else
                        No purchase entries found for the selected period.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection