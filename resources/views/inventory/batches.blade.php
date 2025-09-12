@extends('layouts.app')

@section('title', 'Inventory Batches')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Inventory Batches</h1>
            <p class="text-gray-600 mt-1">Track product batches across branches</p>
        </div>
        @if(auth()->user()->hasRole('branch_manager') || auth()->user()->hasRole('cashier'))
            <a href="{{ route('branch.inventory.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
            </a>
        @else
            <a href="{{ route('inventory.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
            </a>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Batches</h2>
        </div>

        @if($batches->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Initial Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($batches as $batch)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $batch->batch_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $batch->product->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $batch->branch->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($batch->initial_quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($batch->current_quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($batch->purchase_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($batch->purchase_date)->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-green-100 text-green-800',
                                            'expired' => 'bg-red-100 text-red-800',
                                            'sold_out' => 'bg-gray-100 text-gray-800',
                                            'finished' => 'bg-gray-200 text-gray-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$batch->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucwords(str_replace('_',' ', $batch->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $batches->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-boxes text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Batches Found</h3>
                <p class="text-gray-500">Batches will appear here after you add stock.</p>
            </div>
        @endif
    </div>
</div>
@endsection

