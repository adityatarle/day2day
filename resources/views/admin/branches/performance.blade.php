@extends('layouts.app')

@section('title', 'Branch Performance Analytics')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Branch Performance Analytics</h1>
                <p class="text-gray-600 mt-1">Comprehensive analytics and performance metrics for all branches.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.branches.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Branches
                </a>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Revenue</p>
                    <p class="text-2xl font-bold">₹{{ number_format($branches->sum('orders_sum_total_amount'), 2) }}</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Orders</p>
                    <p class="text-2xl font-bold">{{ number_format($branches->sum('orders_count')) }}</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Avg Order Value</p>
                    <p class="text-2xl font-bold">₹{{ number_format($branches->avg('orders_avg_total_amount') ?? 0, 2) }}</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-xl text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Total Staff</p>
                    <p class="text-2xl font-bold">{{ number_format($branches->sum('users_count')) }}</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance Comparison -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Branch Performance Comparison</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Staff</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Avg Order</th>
                        <th>Products</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches->sortByDesc('orders_sum_total_amount') as $branch)
                    <tr class="hover:bg-gray-50">
                        <td>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-xs">{{ strtoupper(substr($branch->name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $branch->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Str::limit($branch->address, 30) }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-sm font-medium text-gray-900">{{ $branch->users_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($branch->orders_count ?? 0) }}</span>
                        </td>
                        <td>
                            <span class="text-sm font-semibold text-gray-900">₹{{ number_format($branch->orders_sum_total_amount ?? 0, 2) }}</span>
                        </td>
                        <td>
                            <span class="text-sm text-gray-900">₹{{ number_format($branch->orders_avg_total_amount ?? 0, 2) }}</span>
                        </td>
                        <td>
                            <span class="text-sm font-medium text-gray-900">{{ $branch->products_count ?? 0 }}</span>
                        </td>
                        <td>
                            @php
                                $maxRevenue = $branches->max('orders_sum_total_amount') ?: 1;
                                $performance = ($branch->orders_sum_total_amount / $maxRevenue) * 100;
                            @endphp
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full" 
                                         style="width: {{ $performance }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-600">{{ round($performance) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Revenue by Branch</h3>
        </div>
        <div class="p-6">
            <canvas id="branchRevenueChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Branch Revenue Chart
const branchData = @json($branches->values());
const ctx = document.getElementById('branchRevenueChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: branchData.map(branch => branch.name),
        datasets: [{
            label: 'Revenue (₹)',
            data: branchData.map(branch => branch.orders_sum_total_amount || 0),
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)',
            ],
            borderColor: [
                'rgba(59, 130, 246, 1)',
                'rgba(16, 185, 129, 1)',
                'rgba(139, 92, 246, 1)',
                'rgba(245, 158, 11, 1)',
                'rgba(239, 68, 68, 1)',
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endsection