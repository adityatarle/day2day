@extends('layouts.cashier')

@section('title', 'Cash Ledger')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-xl font-semibold text-gray-900">Cash Ledger</h1>
                @if($session)
                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Session Active</span>
                @else
                <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">No Active Session</span>
                @endif
            </div>
            <form action="{{ route('pos.ledger.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                @csrf
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="entry_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="take">Take (Cash In)</option>
                        <option value="give">Give (Cash Out)</option>
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Counterparty</label>
                    <input type="text" name="counterparty" placeholder="Person or purpose" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                    <input type="text" name="reference_number" placeholder="Ref no / voucher" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div class="md:col-span-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional details"></textarea>
                </div>
                <div class="md:col-span-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg">Record</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Entries</h2>
                <div class="text-sm text-gray-700">
                    <span class="mr-4">Total In: <span class="font-semibold text-green-600">₹{{ number_format($totals['take'], 2) }}</span></span>
                    <span>Total Out: <span class="font-semibold text-red-600">₹{{ number_format($totals['give'], 2) }}</span></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="py-2">Date</th>
                            <th class="py-2">Type</th>
                            <th class="py-2">Amount</th>
                            <th class="py-2">Counterparty</th>
                            <th class="py-2">Reference</th>
                            <th class="py-2">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($entries as $entry)
                        <tr class="border-t">
                            <td class="py-2 text-gray-600">{{ $entry->entry_date->format('d M Y H:i') }}</td>
                            <td class="py-2">
                                <span class="px-2 py-1 rounded-full text-xs {{ $entry->entry_type === 'take' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($entry->entry_type) }}
                                </span>
                            </td>
                            <td class="py-2 font-semibold {{ $entry->entry_type === 'take' ? 'text-green-600' : 'text-red-600' }}">₹{{ number_format($entry->amount, 2) }}</td>
                            <td class="py-2">{{ $entry->counterparty }}</td>
                            <td class="py-2">{{ $entry->reference_number }}</td>
                            <td class="py-2">{{ $entry->notes }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="py-6 text-center text-gray-500" colspan="6">No entries yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

