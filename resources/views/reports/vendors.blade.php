@extends('layouts.app')

@section('title', 'Vendor Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Vendor Reports</h1>

    <div class="table-container overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>PO Count</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($vendors ?? []) as $vendor)
                <tr>
                    <td>{{ $vendor->name }}</td>
                    <td>{{ number_format($vendor->purchase_orders_count) }}</td>
                    <td>₹{{ number_format($vendor->purchase_orders_sum_total_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-gray-500 p-4">No vendor data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ method_exists(($vendors ?? null), 'links') ? $vendors->links() : '' }}
    </div>
</div>
@endsection

