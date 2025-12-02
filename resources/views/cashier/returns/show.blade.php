@extends('layouts.cashier')

@section('title', 'Return Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('cashier.returns.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Return #{{ $return->id }}</h1>
                    <p class="text-sm text-gray-600">Created on {{ $return->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
            <span class="px-4 py-2 text-sm font-medium rounded-full 
                @if($return->status == 'completed' || $return->status == 'processed') bg-green-100 text-green-800
                @elseif($return->status == 'approved') bg-blue-100 text-blue-800
                @elseif($return->status == 'pending') bg-yellow-100 text-yellow-800
                @else bg-red-100 text-red-800
                @endif">
                {{ ucfirst($return->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Return Items -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Returned Items</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($return->returnItems as $item)
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-box text-orange-600 text-xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-lg font-medium text-gray-900">
                                        {{ $item->orderItem->product->name ?? 'Product' }}
                                    </h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        SKU: {{ $item->orderItem->product->sku ?? 'N/A' }}
                                    </p>
                                    @if($item->condition_notes)
                                        <div class="mt-2 flex items-start space-x-2">
                                            <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-700">Condition Notes:</p>
                                                <p class="text-sm text-gray-600">{{ $item->condition_notes }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-lg font-semibold text-gray-900">
                                        ₹{{ number_format($item->refund_amount, 2) }}
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Qty: {{ $item->returned_quantity }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        @ ₹{{ number_format($item->orderItem->unit_price, 2) }}/unit
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Return Reason -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Return Reason</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700">{{ $return->reason ?? $return->return_reason ?? 'No reason provided' }}</p>
                </div>
                @if($return->notes)
                    <div class="mt-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Additional Notes</h4>
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-gray-700">{{ $return->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Order Information -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Order Number</span>
                        <span class="text-sm font-medium text-gray-900">{{ $return->order->order_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Order Date</span>
                        <span class="text-sm text-gray-900">{{ $return->order->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Order Total</span>
                        <span class="text-sm font-medium text-gray-900">₹{{ number_format($return->order->total_amount, 2) }}</span>
                    </div>
                    <div class="pt-3 border-t border-gray-200">
                        <a href="{{ route('cashier.orders.show', $return->order) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fas fa-external-link-alt mr-1"></i>View Original Order
                        </a>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
                @if($return->order->customer)
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $return->order->customer->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $return->order->customer->phone }}</p>
                            </div>
                        </div>
                        @if($return->order->customer->email)
                            <div class="pt-3 border-t border-gray-200">
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-envelope mr-2"></i>{{ $return->order->customer->email }}
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-user-slash text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-600">Walk-in Customer</p>
                    </div>
                @endif
            </div>

            <!-- Refund Summary -->
            <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-xl shadow-lg p-6 border border-red-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Refund Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Refund Method</span>
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-white text-gray-900 border border-gray-200">
                            {{ ucfirst($return->refund_method) }}
                        </span>
                    </div>
                    
                    @if($return->cash_refund_amount || $return->upi_refund_amount)
                        <div class="pt-3 border-t border-red-200 space-y-2">
                            @if($return->cash_refund_amount)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>Cash Refund
                                    </span>
                                    <span class="text-sm font-semibold text-green-700">
                                        ₹{{ number_format($return->cash_refund_amount, 2) }}
                                    </span>
                                </div>
                            @endif
                            @if($return->upi_refund_amount)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-mobile-alt text-purple-600 mr-2"></i>UPI Refund
                                    </span>
                                    <span class="text-sm font-semibold text-purple-700">
                                        ₹{{ number_format($return->upi_refund_amount, 2) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <div class="pt-3 border-t border-red-200">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900">Total Refund</span>
                            <span class="text-2xl font-bold text-red-600">
                                ₹{{ number_format($return->refund_amount, 2) }}
                            </span>
                        </div>
                    </div>

                    @if($return->return_date)
                        <div class="pt-3 border-t border-red-200">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-700">Refund Date</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($return->return_date)->format('M d, Y h:i A') }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-yellow-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Return Created</p>
                            <p class="text-xs text-gray-600">{{ $return->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                    
                    @if($return->status != 'pending')
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Status: {{ ucfirst($return->status) }}</p>
                                <p class="text-xs text-gray-600">{{ $return->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('cashier.returns.index') }}" 
               class="flex-1 sm:flex-initial px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors text-center">
                <i class="fas fa-arrow-left mr-2"></i>Back to Returns
            </a>
            <button onclick="window.print()" 
                    class="flex-1 sm:flex-initial px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-print mr-2"></i>Print
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white;
        }
    }
</style>
@endpush
@endsection





