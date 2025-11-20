@extends('layouts.app')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-pie text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Profit & Loss Statement</h1>
                    <p class="text-gray-600">
                        {{ \Carbon\Carbon::parse($report['period']['start_date'])->format('M d, Y') }} - 
                        {{ \Carbon\Carbon::parse($report['period']['end_date'])->format('M d, Y') }}
                        @if($branch)
                            | {{ $branch->name }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.financial.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Reports
                </a>
                <button onclick="window.print()" class="btn-primary">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>
    </div>

    <!-- Report Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600">₹{{ number_format($report['revenue']['total'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-arrow-up text-white"></i>
                </div>
            </div>
        </div>

        <!-- Gross Profit -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Gross Profit</p>
                    <p class="text-2xl font-bold text-blue-600">₹{{ number_format($report['gross_profit'], 2) }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($report['gross_profit_margin'], 2) }}% margin</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Net Profit</p>
                    <p class="text-2xl font-bold {{ $report['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        ₹{{ number_format($report['net_profit'], 2) }}
                    </p>
                    <p class="text-xs text-gray-500">{{ number_format($report['net_profit_margin'], 2) }}% margin</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br {{ $report['net_profit'] >= 0 ? 'from-green-500 to-emerald-600' : 'from-red-500 to-red-600' }} rounded-xl flex items-center justify-center">
                    <i class="fas {{ $report['net_profit'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} text-white"></i>
                </div>
            </div>
        </div>

        <!-- Operating Expenses -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Operating Expenses</p>
                    <p class="text-2xl font-bold text-orange-600">₹{{ number_format($report['operating_expenses'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-receipt text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Breakdown -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-green-50 to-emerald-50">
                <h3 class="text-lg font-bold text-gray-900">Revenue by Channel</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-store text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-900">Retail Sales</span>
                        </div>
                        <span class="font-bold text-gray-900">₹{{ number_format($report['revenue']['by_channel']['retail'], 2) }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-900">Wholesale Sales</span>
                        </div>
                        <span class="font-bold text-gray-900">₹{{ number_format($report['revenue']['by_channel']['wholesale'], 2) }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-globe text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-900">Online Sales</span>
                        </div>
                        <span class="font-bold text-gray-900">₹{{ number_format($report['revenue']['by_channel']['online'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Month-over-Month Comparison -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h3 class="text-lg font-bold text-gray-900">Month-over-Month Comparison</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <span class="font-medium text-gray-900">Revenue Change</span>
                        <div class="flex items-center space-x-2">
                            <span class="font-bold {{ $report['comparison']['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₹{{ number_format($report['comparison']['revenue_change'], 2) }}
                            </span>
                            <span class="text-sm {{ $report['comparison']['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ({{ number_format($report['comparison']['revenue_change_percentage'], 2) }}%)
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <span class="font-medium text-gray-900">Profit Change</span>
                        <div class="flex items-center space-x-2">
                            <span class="font-bold {{ $report['comparison']['profit_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₹{{ number_format($report['comparison']['profit_change'], 2) }}
                            </span>
                            <span class="text-sm {{ $report['comparison']['profit_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ({{ number_format($report['comparison']['profit_change_percentage'], 2) }}%)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed P&L Table -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
        <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50 to-blue-50">
            <h3 class="text-lg font-bold text-gray-900">Detailed Profit & Loss Statement</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Revenue Section -->
                    <tr class="bg-green-50">
                        <td class="px-6 py-4 font-bold text-green-800">REVENUE</td>
                        <td class="px-6 py-4"></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 pl-12 text-gray-900">Retail Sales</td>
                        <td class="px-6 py-4 text-right font-medium">₹{{ number_format($report['revenue']['by_channel']['retail'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 pl-12 text-gray-900">Wholesale Sales</td>
                        <td class="px-6 py-4 text-right font-medium">₹{{ number_format($report['revenue']['by_channel']['wholesale'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 pl-12 text-gray-900">Online Sales</td>
                        <td class="px-6 py-4 text-right font-medium">₹{{ number_format($report['revenue']['by_channel']['online'], 2) }}</td>
                    </tr>
                    <tr class="border-t-2 border-green-200">
                        <td class="px-6 py-4 font-bold text-green-800">Total Revenue</td>
                        <td class="px-6 py-4 text-right font-bold text-green-800">₹{{ number_format($report['revenue']['total'], 2) }}</td>
                    </tr>
                    
                    <!-- COGS Section -->
                    <tr class="bg-orange-50">
                        <td class="px-6 py-4 font-bold text-orange-800">COST OF GOODS SOLD</td>
                        <td class="px-6 py-4"></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 pl-12 text-gray-900">Cost of Goods Sold</td>
                        <td class="px-6 py-4 text-right font-medium">₹{{ number_format($report['cost_of_goods_sold'], 2) }}</td>
                    </tr>
                    <tr class="border-t-2 border-orange-200">
                        <td class="px-6 py-4 font-bold text-orange-800">Total COGS</td>
                        <td class="px-6 py-4 text-right font-bold text-orange-800">₹{{ number_format($report['cost_of_goods_sold'], 2) }}</td>
                    </tr>
                    
                    <!-- Gross Profit -->
                    <tr class="bg-blue-50">
                        <td class="px-6 py-4 font-bold text-blue-800">Gross Profit</td>
                        <td class="px-6 py-4 text-right font-bold text-blue-800">₹{{ number_format($report['gross_profit'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 pl-12 text-gray-900">Gross Profit Margin</td>
                        <td class="px-6 py-4 text-right font-medium">{{ number_format($report['gross_profit_margin'], 2) }}%</td>
                    </tr>
                    
                    <!-- Operating Expenses -->
                    <tr class="bg-red-50">
                        <td class="px-6 py-4 font-bold text-red-800">OPERATING EXPENSES</td>
                        <td class="px-6 py-4"></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 pl-12 text-gray-900">Total Operating Expenses</td>
                        <td class="px-6 py-4 text-right font-medium">₹{{ number_format($report['operating_expenses'], 2) }}</td>
                    </tr>
                    <tr class="border-t-2 border-red-200">
                        <td class="px-6 py-4 font-bold text-red-800">Total Operating Expenses</td>
                        <td class="px-6 py-4 text-right font-bold text-red-800">₹{{ number_format($report['operating_expenses'], 2) }}</td>
                    </tr>
                    
                    <!-- Net Profit -->
                    <tr class="bg-gray-100 border-t-4 border-gray-400">
                        <td class="px-6 py-4 font-bold text-lg {{ $report['net_profit'] >= 0 ? 'text-green-800' : 'text-red-800' }}">NET PROFIT</td>
                        <td class="px-6 py-4 text-right font-bold text-lg {{ $report['net_profit'] >= 0 ? 'text-green-800' : 'text-red-800' }}">
                            ₹{{ number_format($report['net_profit'], 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 pl-12 text-gray-900">Net Profit Margin</td>
                        <td class="px-6 py-4 text-right font-medium">{{ number_format($report['net_profit_margin'], 2) }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Export Report</h3>
        <div class="flex flex-wrap gap-3">
            <form action="{{ route('reports.financial.profit-loss') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="start_date" value="{{ $report['period']['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $report['period']['end_date'] }}">
                <input type="hidden" name="branch_id" value="{{ $report['period']['branch_id'] }}">
                <button type="submit" name="format" value="excel" class="btn-success">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </form>
            
            <form action="{{ route('reports.financial.profit-loss') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="start_date" value="{{ $report['period']['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $report['period']['end_date'] }}">
                <input type="hidden" name="branch_id" value="{{ $report['period']['branch_id'] }}">
                <button type="submit" name="format" value="pdf" class="btn-danger">
                    <i class="fas fa-file-pdf mr-2"></i>Export to PDF
                </button>
            </form>
            
            <form action="{{ route('reports.financial.profit-loss') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="start_date" value="{{ $report['period']['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $report['period']['end_date'] }}">
                <input type="hidden" name="branch_id" value="{{ $report['period']['branch_id'] }}">
                <button type="submit" name="format" value="csv" class="btn-secondary">
                    <i class="fas fa-file-csv mr-2"></i>Export to CSV
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .bg-gradient-to-br {
        background: white !important;
    }
    
    .shadow-modern-lg {
        box-shadow: none !important;
    }
}
</style>
@endsection
