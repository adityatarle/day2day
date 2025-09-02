@extends('layouts.app')

@section('title', 'Credit Management - ' . $vendor->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('vendors.show', $vendor) }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Credit Management</h1>
                <p class="text-gray-600">Manage credit transactions with {{ $vendor->name }}</p>
            </div>
        </div>
    </div>

    <!-- Credit Balance Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="text-center">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Current Credit Balance</h2>
            <p class="text-4xl font-bold {{ $creditBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                ₹{{ number_format(abs($creditBalance), 2) }}
                @if($creditBalance < 0)
                    <span class="text-lg font-medium">(Amount Due)</span>
                @endif
            </p>
            <p class="text-gray-600 mt-2">
                @if($creditBalance > 0)
                    You owe this amount to the vendor
                @elseif($creditBalance < 0)
                    Vendor owes this amount to you
                @else
                    No outstanding balance
                @endif
            </p>
        </div>
    </div>

    <!-- Add New Transaction -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Add Credit Transaction</h2>
        
        <form method="POST" action="{{ route('vendors.addCreditTransaction', $vendor) }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="type" class="form-label">Transaction Type *</label>
                    <select name="type" id="type" class="form-input @error('type') border-red-500 @enderror" required>
                        <option value="">Select Type</option>
                        <option value="credit_received" {{ old('type') === 'credit_received' ? 'selected' : '' }}>Credit Received (We paid vendor)</option>
                        <option value="credit_paid" {{ old('type') === 'credit_paid' ? 'selected' : '' }}>Credit Paid (Vendor paid us)</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="form-label">Amount (₹) *</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" 
                           step="0.01" min="0.01" class="form-input @error('amount') border-red-500 @enderror" required>
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="form-label">Description *</label>
                    <input type="text" name="description" id="description" value="{{ old('description') }}" 
                           class="form-input @error('description') border-red-500 @enderror" 
                           placeholder="e.g., Payment for PO-2024-001" required>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full btn-primary">
                        <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Transaction
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Transaction History</h2>
        
        @if($creditTransactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Added By</th>
                            <th>Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningBalance = 0; @endphp
                        @foreach($creditTransactions->reverse() as $transaction)
                            @php
                                if ($transaction->type === 'credit_received') {
                                    $runningBalance += $transaction->amount;
                                } else {
                                    $runningBalance -= $transaction->amount;
                                }
                            @endphp
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction->type === 'credit_received' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $transaction->type === 'credit_received' ? 'We Paid' : 'Vendor Paid' }}
                                    </span>
                                </td>
                                <td class="font-medium {{ $transaction->type === 'credit_received' ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $transaction->type === 'credit_received' ? '-' : '+' }}₹{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ $transaction->user->name ?? 'System' }}</td>
                                <td class="font-medium {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ₹{{ number_format(abs($runningBalance), 2) }}
                                    @if($runningBalance < 0) (Due) @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($creditTransactions->hasPages())
                <div class="mt-6">
                    {{ $creditTransactions->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No credit transactions</h3>
                <p class="mt-1 text-sm text-gray-500">No credit transactions have been recorded for this vendor yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection