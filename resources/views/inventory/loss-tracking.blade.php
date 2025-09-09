@extends('layouts.app')

@section('title', 'Loss Tracking')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Loss Tracking</h1>
                <p class="text-gray-600 mt-1">Track and monitor weight loss, wastage, and other inventory losses</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('inventory.recordLossForm') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Record Loss
                </a>
                <a href="{{ route('inventory.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Losses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_losses'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-weight text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Weight Losses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['weight_losses'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Financial Loss</p>
                    <p class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['total_financial_loss'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Avg Loss/Incident</p>
                    <p class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['avg_loss_per_incident'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-0">
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                <select id="product_id" name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="min-w-0">
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select id="branch_id" name="branch_id" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="min-w-0">
                <label for="loss_type" class="block text-sm font-medium text-gray-700 mb-1">Loss Type</label>
                <select id="loss_type" name="loss_type" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="weight_loss" {{ request('loss_type') == 'weight_loss' ? 'selected' : '' }}>Weight Loss</option>
                    <option value="water_loss" {{ request('loss_type') == 'water_loss' ? 'selected' : '' }}>Water Loss</option>
                    <option value="wastage" {{ request('loss_type') == 'wastage' ? 'selected' : '' }}>Wastage</option>
                    <option value="complimentary" {{ request('loss_type') == 'complimentary' ? 'selected' : '' }}>Complimentary</option>
                </select>
            </div>

            <div class="min-w-0">
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                       class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="min-w-0">
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                       class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('inventory.lossTracking') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Loss Records Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Loss Records</h2>
        </div>

        @if($losses->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loss Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Lost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial Loss</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($losses as $loss)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $loss->created_at->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $loss->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $loss->product->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $loss->product->sku ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $loss->branch->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeColors = [
                                            'weight_loss' => 'bg-orange-100 text-orange-800',
                                            'water_loss' => 'bg-blue-100 text-blue-800',
                                            'wastage' => 'bg-red-100 text-red-800',
                                            'complimentary' => 'bg-green-100 text-green-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$loss->loss_type] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $loss->getLossTypeDisplayName() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ number_format($loss->quantity_lost, 2) }}</div>
                                    @if($loss->initial_quantity && $loss->final_quantity)
                                        <div class="text-sm text-gray-500">
                                            {{ number_format($loss->initial_quantity, 2) }} → {{ number_format($loss->final_quantity, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-red-600">₹{{ number_format($loss->financial_loss, 2) }}</div>
                                    @if($loss->quantity_lost > 0)
                                        <div class="text-sm text-gray-500">₹{{ number_format($loss->getAverageLossPerUnit(), 2) }}/unit</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $loss->reason }}">
                                        {{ $loss->reason ?: 'No reason provided' }}
                                    </div>
                                    @if($loss->batch)
                                        <div class="text-sm text-gray-500">Batch: {{ $loss->batch->batch_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $loss->user->name ?? 'System' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $losses->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-chart-line text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Loss Records Found</h3>
                <p class="text-gray-500 mb-4">No loss records match your current filters.</p>
                <a href="{{ route('inventory.recordLossForm') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Record First Loss
                </a>
            </div>
        @endif
    </div>
</div>
@endsection