@extends('layouts.super-admin')

@section('title', 'Branch Reports - ' . $branch->name)

@section('content')
<div class="p-6 space-y-8">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $branch->name }} - Reports</h1>
            <p class="text-gray-600 mt-1">Comprehensive analytics and performance reports for this branch.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.branches.show', $branch) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Branch
            </a>
            <button onclick="exportReports()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-download mr-2"></i>Export Reports
            </button>
        </div>
    </div>

    <!-- Sales Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Daily Sales</p>
                    <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($reports['daily_sales'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-calendar-week text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Weekly Sales</p>
                    <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($reports['weekly_sales'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Monthly Sales</p>
                    <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($reports['monthly_sales'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Selling Products</h3>
            </div>
            <div class="p-6">
                @if($reports['top_products']->count() > 0)
                <div class="space-y-4">
                    @foreach($reports['top_products'] as $item)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $item['product']->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $item['product']->sku }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">{{ $item['quantity'] }} sold</p>
                            <p class="text-sm text-gray-600">₹{{ number_format($item['revenue'], 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <i class="fas fa-chart-bar text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">No sales data available</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sales Chart Placeholder -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Sales Trend</h3>
            </div>
            <div class="p-6">
                <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">Sales chart will be displayed here</p>
                        <p class="text-sm text-gray-400">Integration with charting library needed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Detailed Reports</h3>
                <div class="flex items-center space-x-4">
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="daily">Daily Report</option>
                        <option value="weekly">Weekly Report</option>
                        <option value="monthly">Monthly Report</option>
                        <option value="yearly">Yearly Report</option>
                    </select>
                    <input type="date" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Top Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Sample data - replace with actual data -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ now()->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ rand(5, 25) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($reports['daily_sales'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($reports['daily_sales'] / max(1, rand(5, 25)), 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $reports['top_products']->first()['product']->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="viewDetailedReport('daily')" class="text-blue-600 hover:text-blue-900">View Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">This Week</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ rand(25, 100) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($reports['weekly_sales'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($reports['weekly_sales'] / max(1, rand(25, 100)), 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $reports['top_products']->first()['product']->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="viewDetailedReport('weekly')" class="text-blue-600 hover:text-blue-900">View Details</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">This Month</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ rand(100, 500) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($reports['monthly_sales'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($reports['monthly_sales'] / max(1, rand(100, 500)), 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $reports['top_products']->first()['product']->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="viewDetailedReport('monthly')" class="text-blue-600 hover:text-blue-900">View Details</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Staff Performance</h4>
            <div class="space-y-3">
                @foreach($branch->users->take(5) as $user)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $user->name }}</span>
                    <span class="text-sm font-medium text-gray-900">₹{{ number_format(rand(1000, 10000), 0) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Customer Metrics</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Total Customers</span>
                    <span class="text-sm font-medium text-gray-900">{{ rand(100, 1000) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">New This Month</span>
                    <span class="text-sm font-medium text-gray-900">{{ rand(10, 50) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Repeat Customers</span>
                    <span class="text-sm font-medium text-gray-900">{{ rand(50, 200) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Inventory Status</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Total Products</span>
                    <span class="text-sm font-medium text-gray-900">{{ $branch->products->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Low Stock Items</span>
                    <span class="text-sm font-medium text-yellow-600">{{ rand(5, 20) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Out of Stock</span>
                    <span class="text-sm font-medium text-red-600">{{ rand(0, 5) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReports() {
    alert('Export reports functionality - Generate PDF/Excel reports');
}

function viewDetailedReport(period) {
    alert('View detailed ' + period + ' report');
}
</script>
@endsection