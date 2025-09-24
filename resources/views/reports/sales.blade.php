@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Sales Report</h1>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="form-label">Start date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">End date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-input">
            </div>
            @if(isset($branches) && $branches->count() > 1)
            <div>
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-input">
                    <option value="">All</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex items-end">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Orders (page)</p>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalOrders ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Sales (page)</p>
            <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($totalSales ?? 0, 2) }}</p>
        </div>
        @if(isset($branchSummary) && count($branchSummary))
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Top Branch</p>
            @php $top = $branchSummary->first(); @endphp
            <p class="text-2xl font-semibold text-gray-900">{{ $top?->branch?->name ?? '-' }}</p>
        </div>
        @endif
    </div>

    <div class="table-container overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Branch</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($orders ?? []) as $order)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->customer->name ?? 'Walk-in' }}</td>
                    <td>{{ $order->branch->name ?? '-' }}</td>
                    <td>₹{{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 p-4">No orders found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ method_exists(($orders ?? null), 'links') ? $orders->links() : '' }}
    </div>

    @if(isset($branchSummary) && count($branchSummary))
    <div class="bg-white rounded-lg shadow p-4 mt-6">
        <h2 class="text-xl font-semibold mb-3">Branch Summary</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Orders</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branchSummary as $summary)
                    <tr>
                        <td>{{ $summary->branch->name ?? '-' }}</td>
                        <td>{{ number_format($summary->order_count) }}</td>
                        <td>₹{{ number_format($summary->total_sales, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

