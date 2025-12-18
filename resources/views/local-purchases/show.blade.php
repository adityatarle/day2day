@extends('layouts.app')

@section('title', 'Local Purchase Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center text-sm text-gray-600 mb-2">
                <a href="{{ auth()->user()->isBranchManager() ? route('branch.local-purchases.index') : route('admin.local-purchases.index') }}" 
                   class="hover:text-gray-900">Local Purchases</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">{{ $localPurchase->purchase_number }}</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Local Purchase Details</h1>
            <p class="text-gray-600">{{ $localPurchase->purchase_number }}</p>
        </div>
        <div class="flex space-x-2">
            @if($localPurchase->isPending() && $localPurchase->manager_id === auth()->id())
            <a href="{{ route('branch.local-purchases.edit', $localPurchase) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            @endif
            
            @if($localPurchase->isPending() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
            <button type="button" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors" onclick="approvePurchase()">
                <i class="fas fa-check mr-2"></i>Approve
            </button>
            <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors" onclick="showRejectModal()">
                <i class="fas fa-times mr-2"></i>Reject
            </button>
            @endif
            
            <a href="{{ auth()->user()->isBranchManager() ? route('branch.local-purchases.index') : route('admin.local-purchases.index') }}" 
               class="border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    @if($localPurchase->isRejected())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium">Purchase Rejected</h3>
                <div class="mt-2 text-sm">
                    <p><strong>Rejected by:</strong> {{ $localPurchase->approvedBy->name }}</p>
                    <p><strong>Date:</strong> {{ $localPurchase->approved_at->format('d M Y, h:i A') }}</p>
                    <p><strong>Reason:</strong> {{ $localPurchase->rejection_reason ?: 'No reason provided' }}</p>
                </div>
            </div>
        </div>
    </div>
    @elseif($localPurchase->isApproved() || $localPurchase->isCompleted())
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium">Purchase Approved</h3>
                <div class="mt-2 text-sm">
                    <p><strong>Approved by:</strong> {{ $localPurchase->approvedBy->name }}</p>
                    <p><strong>Date:</strong> {{ $localPurchase->approved_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Purchase Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-4 rounded-t-lg">
                    <h2 class="text-lg font-semibold">Purchase Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Purchase Number:</dt>
                                    <dd class="text-sm text-gray-900">{{ $localPurchase->purchase_number }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Purchase Date:</dt>
                                    <dd class="text-sm text-gray-900">{{ $localPurchase->purchase_date->format('d M Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Branch:</dt>
                                    <dd class="text-sm text-gray-900">{{ $localPurchase->branch->name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Created by:</dt>
                                    <dd class="text-sm text-gray-900">{{ $localPurchase->manager->name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Status:</dt>
                                    <dd>
                                        @switch($localPurchase->status)
                                            @case('draft')
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Draft</span>
                                                @break
                                            @case('pending')
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending Approval</span>
                                                @break
                                            @case('approved')
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Rejected</span>
                                                @break
                                            @case('completed')
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Completed</span>
                                                @break
                                        @endswitch
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Vendor:</dt>
                                    <dd class="text-sm text-gray-900">{{ $localPurchase->vendor_display_name }}</dd>
                                </div>
                                @if($localPurchase->vendor_phone)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Vendor Phone:</dt>
                                    <dd class="text-sm text-gray-900">{{ $localPurchase->vendor_phone }}</dd>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Payment Method:</dt>
                                    <dd>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($localPurchase->payment_method) }}
                                        </span>
                                    </dd>
                                </div>
                                @if($localPurchase->payment_reference)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Payment Ref:</dt>
                                    <dd class="text-sm text-gray-900">{{ $localPurchase->payment_reference }}</dd>
                                </div>
                                @endif
                                @if($localPurchase->purchaseOrder)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-600">Linked PO:</dt>
                                    <dd>
                                        <a href="{{ route('branch.product-orders.show', $localPurchase->purchaseOrder) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                            {{ $localPurchase->purchaseOrder->po_number }}
                                        </a>
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Items -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-t-lg">
                    <h2 class="text-lg font-semibold">Purchase Items</h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($localPurchase->items as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        @if($item->notes)
                                        <div class="text-sm text-gray-500">{{ $item->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity }} {{ $item->unit }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₹{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₹{{ number_format($item->subtotal, 2) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($item->tax_rate > 0)
                                            <div>{{ $item->tax_rate }}%</div>
                                            <div class="text-xs text-gray-500">₹{{ number_format($item->tax_amount, 2) }}</div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($item->discount_rate > 0)
                                            <div>{{ $item->discount_rate }}%</div>
                                            <div class="text-xs text-gray-500">₹{{ number_format($item->discount_amount, 2) }}</div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        ₹{{ number_format($item->total_amount, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="7" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Subtotal:</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">₹{{ number_format($localPurchase->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Total Tax:</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">₹{{ number_format($localPurchase->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Total Discount:</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">₹{{ number_format($localPurchase->discount_amount, 2) }}</td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <td colspan="7" class="px-4 py-3 text-right text-lg font-bold text-gray-900">Grand Total:</td>
                                    <td class="px-4 py-3 text-lg font-bold text-blue-600">₹{{ number_format($localPurchase->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($localPurchase->notes)
            <!-- Notes -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Notes</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-700">{{ $localPurchase->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="lg:col-span-1 space-y-6">
            <!-- Financial Summary -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-6 py-4 rounded-t-lg">
                    <h2 class="text-lg font-semibold">Financial Summary</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Items Count:</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $localPurchase->items->count() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Total Quantity:</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $localPurchase->items->sum('quantity') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Subtotal:</dt>
                            <dd class="text-sm font-medium text-gray-900">₹{{ number_format($localPurchase->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Tax:</dt>
                            <dd class="text-sm font-medium text-gray-900">₹{{ number_format($localPurchase->tax_amount, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Discount:</dt>
                            <dd class="text-sm font-medium text-gray-900">₹{{ number_format($localPurchase->discount_amount, 2) }}</dd>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-gray-200">
                            <dt class="text-base font-semibold text-gray-900">Total Amount:</dt>
                            <dd class="text-base font-bold text-blue-600">₹{{ number_format($localPurchase->total_amount, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Receipt/Invoice -->
            @if($localPurchase->receipt_path)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Receipt/Invoice</h2>
                </div>
                <div class="p-6">
                    <a href="{{ route('branch.local-purchases.receipt', $localPurchase) }}" target="_blank" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-file-invoice mr-2"></i>View Receipt
                    </a>
                </div>
            </div>
            @endif

            <!-- Linked Expense -->
            @if($localPurchase->expense)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Expense Record</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Expense ID:</dt>
                            <dd class="text-sm font-medium text-gray-900">#{{ $localPurchase->expense->id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Category:</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $localPurchase->expense->expenseCategory->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Status:</dt>
                            <dd>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $localPurchase->expense->isApproved() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($localPurchase->expense->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Timeline</h2>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Created by <span class="font-medium text-gray-900">{{ $localPurchase->manager->name }}</span></p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $localPurchase->created_at->format('d M Y, h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            
                            @if($localPurchase->approved_at)
                            <li>
                                <div class="relative pb-8">
                                    @if($localPurchase->isCompleted())
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full {{ $localPurchase->isApproved() ? 'bg-green-500' : 'bg-red-500' }} flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                    @if($localPurchase->isApproved())
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    @else
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    @endif
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    {{ $localPurchase->isApproved() ? 'Approved' : 'Rejected' }} by 
                                                    <span class="font-medium text-gray-900">{{ $localPurchase->approvedBy->name }}</span>
                                                </p>
                                                @if($localPurchase->rejection_reason)
                                                <p class="text-sm text-red-600 mt-1">Reason: {{ $localPurchase->rejection_reason }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $localPurchase->approved_at->format('d M Y, h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                            
                            @if($localPurchase->isCompleted())
                            <li>
                                <div class="relative">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Completed - Stock updated and expense recorded</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $localPurchase->updated_at->format('d M Y, h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
@if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
<div id="rejectModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.local-purchases.reject', $localPurchase) }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Reject Local Purchase
                            </h3>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">
                                    Rejection Reason <span class="text-red-500">*</span>
                                </label>
                                <textarea name="rejection_reason" rows="3" class="mt-1 form-input" required
                                          placeholder="Please provide a reason for rejection..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Reject Purchase
                    </button>
                    <button type="button" onclick="closeRejectModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approvePurchase() {
    if (confirm('Are you sure you want to approve this local purchase? This will update the stock and create an expense record.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.local-purchases.approve', $localPurchase) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeRejectModal();
    }
});
</script>
@endif
@endsection