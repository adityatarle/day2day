@extends('layouts.app')

@section('title', 'Edit Order ' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('orders.show', $order) }}" class="text-gray-600 hover:text-gray-800">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Edit Order {{ $order->order_number }}</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form id="order-edit-form" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['pending','confirmed','processing','ready','delivered','cancelled','returned'] as $st)
                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <select id="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['pending','paid','failed','refunded'] as $pst)
                            <option value="{{ $pst }}" {{ ($order->payment_status ?? 'pending') === $pst ? 'selected' : '' }}>{{ ucfirst($pst) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Amount (₹)</label>
                    <input type="number" id="discount_amount" step="0.01" min="0" value="{{ $order->discount_amount }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $order->notes }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('orders.show', $order) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('order-edit-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value;
        const xsrfCookie = (document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN=')) || '').split('=')[1];
        const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : '';

        const payload = {
            status: document.getElementById('status').value,
            payment_status: document.getElementById('payment_status').value,
            notes: document.getElementById('notes').value,
            discount_amount: parseFloat(document.getElementById('discount_amount').value || 0)
        };

        try {
            try { await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' }); } catch (e) {}

            const response = await fetch('/api/orders/{{ $order->id }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-XSRF-TOKEN': xsrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (!response.ok) {
                console.error('Update failed:', data);
                alert(data.message || 'Failed to update order');
                return;
            }

            window.location.href = '{{ route('orders.show', $order) }}';
        } catch (error) {
            console.error('Error updating order:', error);
            alert('An unexpected error occurred');
        }
    });
});
</script>
@endsection

