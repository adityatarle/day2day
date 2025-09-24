@extends('layouts.app')

@section('title', 'Customer Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Customer Reports</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Top Customers</h2>
            <ul class="divide-y">
                @forelse(($topCustomers ?? []) as $customer)
                <li class="py-2 flex justify-between">
                    <span>{{ $customer->name }}</span>
                    <span class="font-semibold">₹{{ number_format($customer->orders_sum_total_amount, 2) }}</span>
                </li>
                @empty
                <li class="py-2 text-gray-500">No data</li>
                @endforelse
            </ul>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Summary</h2>
            <p class="text-gray-600">This section summarizes purchase totals per customer.</p>
        </div>
    </div>

    <div class="table-container overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Total Orders</th>
                    <th>Total Spent</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($customers ?? []) as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ number_format($customer->orders_count) }}</td>
                    <td>₹{{ number_format($customer->orders_sum_total_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-gray-500 p-4">No customers</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ method_exists(($customers ?? null), 'links') ? $customers->links() : '' }}
    </div>
</div>
@endsection

