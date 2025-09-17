@extends('layouts.app')

@section('title', 'Debug Purchase Orders')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('branch.purchase-entries.create') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Create Purchase Entry
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Debug: Purchase Orders for Branch</h1>
        
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Current User Info</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p><strong>User ID:</strong> {{ Auth::user()->id }}</p>
                <p><strong>User Name:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Branch ID:</strong> {{ Auth::user()->branch_id }}</p>
                <p><strong>Roles:</strong> {{ Auth::user()->roles->pluck('name')->join(', ') }}</p>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">All Purchase Orders for This Branch</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($allPurchaseOrders as $order)
                        <tr class="{{ in_array($order->status, ['sent', 'approved', 'fulfilled']) && is_null($order->received_at) ? 'bg-green-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $order->po_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->order_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $order->status === 'draft' ? 'bg-gray-100 text-gray-800' : 
                                       ($order->status === 'sent' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($order->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                       ($order->status === 'fulfilled' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->vendor->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->purchaseOrderItems->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->received_at ? $order->received_at->format('M d, Y H:i') : 'Not received' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->created_at->format('M d, Y H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No purchase orders found for this branch.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Available Orders for Purchase Entry</h2>
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>Query:</strong> Orders with status 'sent' or 'confirmed' AND received_at IS NULL
                </p>
                <p class="text-sm text-blue-800 mt-2">
                    <strong>Count:</strong> {{ $availableOrders->count() }} orders available
                </p>
            </div>
        </div>

        @if($availableOrders->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($availableOrders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $order->po_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $order->status === 'sent' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($order->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $order->status === 'sent' ? 'Approved by Admin' : 
                                   ($order->status === 'confirmed' ? 'Fulfilled by Admin' : ucfirst($order->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->vendor->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->purchaseOrderItems->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-gray-500">No orders are available for purchase entry creation.</p>
        </div>
        @endif
    </div>
</div>
@endsection