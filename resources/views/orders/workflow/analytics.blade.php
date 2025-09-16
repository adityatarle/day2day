@extends('layouts.app')

@section('title', 'Order Workflow Analytics')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Order Workflow Analytics</h1>
            <p class="text-gray-600 mt-2">Detailed analytics and performance metrics for order processing</p>
        </div>
        <div class="flex gap-4">
            <button onclick="exportAnalytics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
            <button onclick="refreshAnalytics()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <form id="analyticsFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <select name="period" id="period" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div id="customDateRange" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div id="customEndDate" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Branches</option>
                    <!-- Add branch options here -->
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900" id="totalOrders">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completion Rate</p>
                    <p class="text-2xl font-bold text-gray-900" id="completionRate">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Avg Processing Time</p>
                    <p class="text-2xl font-bold text-gray-900" id="avgProcessingTime">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Delayed Orders</p>
                    <p class="text-2xl font-bold text-gray-900" id="delayedOrders">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Order Status Distribution -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Status Distribution</h3>
                            <canvas id="statusChart" width="400" height="200"></canvas>
        </div>

        <!-- Processing Time Trends -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Processing Time Trends</h3>
                            <canvas id="processingTimeChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Daily Order Volume -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Order Volume</h3>
                            <canvas id="dailyVolumeChart" width="400" height="200"></canvas>
        </div>

        <!-- Workflow Efficiency -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Workflow Efficiency</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Order to Confirmed</span>
                        <span id="orderToConfirmed">-</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" id="orderToConfirmedBar" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Confirmed to Processing</span>
                        <span id="confirmedToProcessing">-</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-orange-600 h-2 rounded-full" id="confirmedToProcessingBar" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Processing to Ready</span>
                        <span id="processingToReady">-</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-600 h-2 rounded-full" id="processingToReadyBar" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Ready to Delivered</span>
                        <span id="readyToDelivered">-</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" id="readyToDeliveredBar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Detailed Analytics</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metric</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Today</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">This Week</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">This Month</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="analyticsTableBody">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let charts = {};

// Initialize analytics on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAnalytics();
    
    // Period change handler
    document.getElementById('period').addEventListener('change', function() {
        const customRange = document.getElementById('customDateRange');
        const customEndDate = document.getElementById('customEndDate');
        
        if (this.value === 'custom') {
            customRange.classList.remove('hidden');
            customEndDate.classList.remove('hidden');
        } else {
            customRange.classList.add('hidden');
            customEndDate.classList.add('hidden');
        }
    });
    
    // Form submission
    document.getElementById('analyticsFilters').addEventListener('submit', function(e) {
        e.preventDefault();
        loadAnalytics();
    });
});

async function loadAnalytics() {
    const formData = new FormData(document.getElementById('analyticsFilters'));
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    
    try {
        const response = await fetch(`/orders/workflow/analytics?${params.toString()}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            updateMetrics(result.data);
            updateCharts(result.data);
            updateTable(result.data);
        }
    } catch (error) {
        console.error('Failed to load analytics:', error);
    }
}

function updateMetrics(data) {
    const stats = data.statistics;
    const processingTimes = data.processing_times;
    
    // Calculate totals
    const totalOrders = Object.values(stats).reduce((sum, stat) => sum + stat.count, 0);
    const completedOrders = stats.delivered?.count || 0;
    const completionRate = totalOrders > 0 ? ((completedOrders / totalOrders) * 100).toFixed(1) : 0;
    const avgProcessingTime = processingTimes.total_processing_time || 0;
    const delayedOrders = data.delayed_orders || 0;
    
    document.getElementById('totalOrders').textContent = totalOrders;
    document.getElementById('completionRate').textContent = completionRate + '%';
    document.getElementById('avgProcessingTime').textContent = avgProcessingTime + 'm';
    document.getElementById('delayedOrders').textContent = delayedOrders;
}

function updateCharts(data) {
    const stats = data.statistics;
    const processingTimes = data.processing_times;
    
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    if (charts.statusChart) charts.statusChart.destroy();
    
    charts.statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(stats).map(status => stats[status].config.name),
            datasets: [{
                data: Object.values(stats).map(stat => stat.count),
                backgroundColor: [
                    '#3B82F6', '#F59E0B', '#10B981', '#EF4444', '#8B5CF6', '#F97316'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Processing Time Chart
    const processingCtx = document.getElementById('processingTimeChart').getContext('2d');
    if (charts.processingTimeChart) charts.processingTimeChart.destroy();
    
    charts.processingTimeChart = new Chart(processingCtx, {
        type: 'line',
        data: {
            labels: ['Order to Confirmed', 'Confirmed to Processing', 'Processing to Ready', 'Ready to Delivered'],
            datasets: [{
                label: 'Time (minutes)',
                data: [
                    processingTimes.order_to_confirmed,
                    processingTimes.confirmed_to_processing,
                    processingTimes.processing_to_ready,
                    processingTimes.ready_to_delivered
                ],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Daily Volume Chart
    const dailyCtx = document.getElementById('dailyVolumeChart').getContext('2d');
    if (charts.dailyVolumeChart) charts.dailyVolumeChart.destroy();
    
    const dailyData = data.daily_orders || {};
    const labels = Object.keys(dailyData).sort();
    const datasets = [];
    
    // Create datasets for each status
    const statuses = ['pending', 'processing', 'ready', 'delivered', 'cancelled'];
    const colors = ['#3B82F6', '#F59E0B', '#10B981', '#EF4444', '#8B5CF6'];
    
    statuses.forEach((status, index) => {
        datasets.push({
            label: status.charAt(0).toUpperCase() + status.slice(1),
            data: labels.map(date => dailyData[date]?.find(d => d.status === status)?.count || 0),
            backgroundColor: colors[index] + '20',
            borderColor: colors[index],
            tension: 0.4
        });
    });
    
    charts.dailyVolumeChart = new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: false
                }
            }
        }
    });
    
    // Update efficiency bars
    updateEfficiencyBars(processingTimes);
}

function updateEfficiencyBars(processingTimes) {
    const maxTime = Math.max(
        processingTimes.order_to_confirmed,
        processingTimes.confirmed_to_processing,
        processingTimes.processing_to_ready,
        processingTimes.ready_to_delivered
    );
    
    const updates = [
        { id: 'orderToConfirmed', barId: 'orderToConfirmedBar', value: processingTimes.order_to_confirmed },
        { id: 'confirmedToProcessing', barId: 'confirmedToProcessingBar', value: processingTimes.confirmed_to_processing },
        { id: 'processingToReady', barId: 'processingToReadyBar', value: processingTimes.processing_to_ready },
        { id: 'readyToDelivered', barId: 'readyToDeliveredBar', value: processingTimes.ready_to_delivered }
    ];
    
    updates.forEach(update => {
        document.getElementById(update.id).textContent = update.value + 'm';
        const percentage = maxTime > 0 ? (update.value / maxTime) * 100 : 0;
        document.getElementById(update.barId).style.width = percentage + '%';
    });
}

function updateTable(data) {
    const tbody = document.getElementById('analyticsTableBody');
    tbody.innerHTML = '';
    
    // This would be populated with detailed analytics data
    // For now, showing placeholder data
    const metrics = [
        { name: 'Total Orders', today: 0, week: 0, month: 0, trend: 'up' },
        { name: 'Completed Orders', today: 0, week: 0, month: 0, trend: 'up' },
        { name: 'Average Processing Time', today: 0, week: 0, month: 0, trend: 'down' },
        { name: 'Customer Satisfaction', today: 0, week: 0, month: 0, trend: 'up' }
    ];
    
    metrics.forEach(metric => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${metric.name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${metric.today}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${metric.week}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${metric.month}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${metric.trend === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${metric.trend === 'up' ? '↗' : '↘'} ${metric.trend}
                </span>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function refreshAnalytics() {
    loadAnalytics();
}

function exportAnalytics() {
    // Implement export functionality
    alert('Export functionality would be implemented here');
}
</script>
@endsection