@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Stock Movements</h1>
            <p class="text-gray-600 mt-1">Recent inventory movements across products and branches</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Movements</h2>
        </div>

        @if($movements->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($movements as $movement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement->created_at->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $movement->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $movement->product->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement->branch->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeColors = [
                                            'purchase' => 'bg-green-100 text-green-800',
                                            'sale' => 'bg-blue-100 text-blue-800',
                                            'adjustment' => 'bg-yellow-100 text-yellow-800',
                                            'loss' => 'bg-red-100 text-red-800',
                                            'return' => 'bg-purple-100 text-purple-800',
                                            'transfer_in' => 'bg-emerald-100 text-emerald-800',
                                            'transfer_out' => 'bg-orange-100 text-orange-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$movement->type] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_',' ', $movement->type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ number_format($movement->quantity, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">₹{{ number_format($movement->unit_price, 2) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $movement->notes }}">{{ $movement->notes ?: '—' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $movements->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-exchange-alt text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Stock Movements Found</h3>
                <p class="text-gray-500">Activity will appear here after you add stock, sell products, or record adjustments.</p>
            </div>
        @endif
    </div>
</div>
@endsection

