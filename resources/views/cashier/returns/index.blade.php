@extends('layouts.cashier')

@section('title', 'Returns & Refunds')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-orange-600 to-red-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Returns & Refunds</h1>
                <p class="text-orange-100">Manage customer returns and refunds</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">{{ $stats['total_returns'] }}</div>
                <div class="text-orange-100 text-sm">Total Returns</div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Today's Returns -->
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Returns</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['today_returns'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-undo text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Today's Refunds -->
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Refunds</p>
                    <p class="text-3xl font-bold text-gray-900">₹{{ number_format($stats['today_refunds'], 2) }}</p>
                    @if($stats['today_cash_refunds'] || $stats['today_upi_refunds'])
                        <div class="text-xs text-gray-500 mt-1">
                            Cash: ₹{{ number_format($stats['today_cash_refunds'] ?? 0, 2) }} | 
                            UPI: ₹{{ number_format($stats['today_upi_refunds'] ?? 0, 2) }}
                        </div>
                    @endif
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Returns -->
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Returns</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_returns'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Refunds -->
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Refunds</p>
                    <p class="text-3xl font-bold text-gray-900">₹{{ number_format($stats['total_refunds'], 2) }}</p>
                    @if($stats['total_cash_refunds'] || $stats['total_upi_refunds'])
                        <div class="text-xs text-gray-500 mt-1">
                            Cash: ₹{{ number_format($stats['total_cash_refunds'] ?? 0, 2) }} | 
                            UPI: ₹{{ number_format($stats['total_upi_refunds'] ?? 0, 2) }}
                        </div>
                    @endif
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" action="{{ route('cashier.returns.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('cashier.returns.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Returns List -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Returns List</h3>
        </div>

        @if($returns->count() > 0)
            <!-- Mobile Card Layout -->
            <div class="lg:hidden">
                @foreach($returns as $return)
                    <div class="p-6 border-b border-gray-200 last:border-b-0">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-undo text-orange-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Return #{{ $return->id }}</h4>
                                    <p class="text-sm text-gray-600">Order #{{ $return->order->id }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                @if($return->status == 'completed') bg-green-100 text-green-800
                                @elseif($return->status == 'approved') bg-blue-100 text-blue-800
                                @elseif($return->status == 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($return->status) }}
                            </span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Customer</span>
                                <span class="text-sm font-medium text-gray-900">{{ $return->order->customer->name ?? 'Walk-in Customer' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Refund Amount</span>
                                <span class="text-sm font-bold text-red-600">₹{{ number_format($return->total_amount, 2) }}</span>
                            </div>
                            @if($return->cash_refund_amount || $return->upi_refund_amount)
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                <span class="text-xs text-gray-500">Payment Breakdown:</span>
                                <div class="flex gap-2 text-xs">
                                    @if($return->cash_refund_amount)
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Cash: ₹{{ number_format($return->cash_refund_amount, 2) }}</span>
                                    @endif
                                    @if($return->upi_refund_amount)
                                        <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded">UPI: ₹{{ number_format($return->upi_refund_amount, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Date</span>
                                <span class="text-sm text-gray-900">{{ $return->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Reason</span>
                                <span class="text-sm text-gray-900">{{ Str::limit($return->reason, 30) }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2 mt-4">
                            <a href="{{ route('cashier.returns.show', $return) }}" class="flex-1 bg-blue-50 text-blue-700 text-center py-2 px-3 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop Table Layout -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Return</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($returns as $return)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-undo text-orange-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">#{{ $return->id }}</div>
                                            <div class="text-sm text-gray-500">{{ $return->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">#{{ $return->order->id }}</div>
                                    <div class="text-sm text-gray-500">{{ $return->order->order_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $return->order->customer->name ?? 'Walk-in Customer' }}</div>
                                    <div class="text-sm text-gray-500">{{ $return->order->customer->phone ?? 'No phone' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($return->status == 'completed') bg-green-100 text-green-800
                                        @elseif($return->status == 'approved') bg-blue-100 text-blue-800
                                        @elseif($return->status == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($return->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">₹{{ number_format($return->total_amount, 2) }}</div>
                                    @if($return->cash_refund_amount || $return->upi_refund_amount)
                                        <div class="text-xs text-gray-500 mt-1">
                                            @if($return->cash_refund_amount)
                                                <span class="text-green-600">Cash: ₹{{ number_format($return->cash_refund_amount, 2) }}</span>
                                            @endif
                                            @if($return->cash_refund_amount && $return->upi_refund_amount) | @endif
                                            @if($return->upi_refund_amount)
                                                <span class="text-purple-600">UPI: ₹{{ number_format($return->upi_refund_amount, 2) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $return->created_at->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $return->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('cashier.returns.show', $return) }}" class="text-blue-600 hover:text-blue-900 transition-colors">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-undo text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No returns found</h3>
                <p class="text-sm text-gray-500 mb-6">You haven't processed any returns yet.</p>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($returns->hasPages())
        <div class="mt-6">
            {{ $returns->links() }}
        </div>
    @endif
</div>
@endsection