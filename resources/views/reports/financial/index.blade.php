@extends('layouts.app')

@section('title', 'Financial Reports')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Financial Reports</h1>
                    <p class="text-gray-600">Comprehensive financial analysis and compliance reports</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-green-700">Financial System Active</span>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profit & Loss Reports -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-pie text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Profit & Loss Statement</h3>
                </div>
                <p class="text-gray-600 mt-2">Revenue, expenses, and profitability analysis</p>
            </div>
            <div class="p-6">
                <form action="{{ route('reports.financial.profit-loss') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-input" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Branch (Optional)</label>
                        <select name="branch_id" class="form-input">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" name="format" value="view" class="btn-primary">
                            <i class="fas fa-eye mr-2"></i>View Report
                        </button>
                        <button type="submit" name="format" value="excel" class="btn-success">
                            <i class="fas fa-file-excel mr-2"></i>Excel
                        </button>
                        <button type="submit" name="format" value="pdf" class="btn-danger">
                            <i class="fas fa-file-pdf mr-2"></i>PDF
                        </button>
                        <button type="submit" name="format" value="csv" class="btn-secondary">
                            <i class="fas fa-file-csv mr-2"></i>CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cash Flow Reports -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-green-50 to-emerald-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-coins text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Cash Flow Statement</h3>
                </div>
                <p class="text-gray-600 mt-2">Operating, investing, and financing activities</p>
            </div>
            <div class="p-6">
                <form action="{{ route('reports.financial.cash-flow') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-input" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Branch (Optional)</label>
                        <select name="branch_id" class="form-input">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" name="format" value="view" class="btn-primary">
                            <i class="fas fa-eye mr-2"></i>View Report
                        </button>
                        <button type="submit" name="format" value="excel" class="btn-success">
                            <i class="fas fa-file-excel mr-2"></i>Excel
                        </button>
                        <button type="submit" name="format" value="pdf" class="btn-danger">
                            <i class="fas fa-file-pdf mr-2"></i>PDF
                        </button>
                        <button type="submit" name="format" value="csv" class="btn-secondary">
                            <i class="fas fa-file-csv mr-2"></i>CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Balance Sheet -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-purple-50 to-violet-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-balance-scale text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Balance Sheet</h3>
                </div>
                <p class="text-gray-600 mt-2">Assets, liabilities, and equity position</p>
            </div>
            <div class="p-6">
                <form action="{{ route('reports.financial.balance-sheet') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label">As of Date</label>
                        <input type="date" name="as_of_date" class="form-input" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Branch (Optional)</label>
                        <select name="branch_id" class="form-input">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" name="format" value="view" class="btn-primary">
                            <i class="fas fa-eye mr-2"></i>View Report
                        </button>
                        <button type="submit" name="format" value="excel" class="btn-success">
                            <i class="fas fa-file-excel mr-2"></i>Excel
                        </button>
                        <button type="submit" name="format" value="pdf" class="btn-danger">
                            <i class="fas fa-file-pdf mr-2"></i>PDF
                        </button>
                        <button type="submit" name="format" value="csv" class="btn-secondary">
                            <i class="fas fa-file-csv mr-2"></i>CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- GST Compliance Reports -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-orange-50 to-red-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-invoice text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">GST Compliance</h3>
                </div>
                <p class="text-gray-600 mt-2">Sales & Purchase registers for tax compliance</p>
            </div>
            <div class="p-6 space-y-4">
                <!-- Sales Register -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Sales Register</h4>
                    <form action="{{ route('reports.financial.sales-register') }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-sm">Start Date</label>
                                <input type="date" name="start_date" class="form-input text-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                            </div>
                            <div>
                                <label class="form-label text-sm">End Date</label>
                                <input type="date" name="end_date" class="form-input text-sm" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label text-sm">Branch (Optional)</label>
                            <select name="branch_id" class="form-input text-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" name="format" value="view" class="btn-primary text-sm px-3 py-2">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                            <button type="submit" name="format" value="excel" class="btn-success text-sm px-3 py-2">
                                <i class="fas fa-file-excel mr-1"></i>Excel
                            </button>
                            <button type="submit" name="format" value="pdf" class="btn-danger text-sm px-3 py-2">
                                <i class="fas fa-file-pdf mr-1"></i>PDF
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Purchase Register -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Purchase Register</h4>
                    <form action="{{ route('reports.financial.purchase-register') }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-sm">Start Date</label>
                                <input type="date" name="start_date" class="form-input text-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                            </div>
                            <div>
                                <label class="form-label text-sm">End Date</label>
                                <input type="date" name="end_date" class="form-input text-sm" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label text-sm">Branch (Optional)</label>
                            <select name="branch_id" class="form-input text-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" name="format" value="view" class="btn-primary text-sm px-3 py-2">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                            <button type="submit" name="format" value="excel" class="btn-success text-sm px-3 py-2">
                                <i class="fas fa-file-excel mr-1"></i>Excel
                            </button>
                            <button type="submit" name="format" value="pdf" class="btn-danger text-sm px-3 py-2">
                                <i class="fas fa-file-pdf mr-1"></i>PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Expense Analysis -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden lg:col-span-2">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-teal-50 to-cyan-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-bar text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Expense Analysis</h3>
                </div>
                <p class="text-gray-600 mt-2">Category-wise breakdown, variance analysis, and cost per unit</p>
            </div>
            <div class="p-6">
                <form action="{{ route('reports.financial.expense-analysis') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-input" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="form-label">Branch (Optional)</label>
                            <select name="branch_id" class="form-input">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="flex flex-wrap gap-2 w-full">
                                <button type="submit" name="format" value="view" class="btn-primary flex-1">
                                    <i class="fas fa-eye mr-2"></i>View Report
                                </button>
                                <button type="submit" name="format" value="excel" class="btn-success flex-1">
                                    <i class="fas fa-file-excel mr-2"></i>Excel
                                </button>
                                <button type="submit" name="format" value="pdf" class="btn-danger flex-1">
                                    <i class="fas fa-file-pdf mr-2"></i>PDF
                                </button>
                                <button type="submit" name="format" value="csv" class="btn-secondary flex-1">
                                    <i class="fas fa-file-csv mr-2"></i>CSV
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="{{ route('reports.index') }}" class="group flex flex-col items-center p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl hover:shadow-lg transition-all duration-300">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-arrow-left text-white"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Back to Reports</span>
            </a>
            
            <a href="{{ route('dashboard') }}" class="group flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl hover:shadow-lg transition-all duration-300">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-home text-white"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Dashboard</span>
            </a>
            
            <a href="{{ route('orders.index') }}" class="group flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl hover:shadow-lg transition-all duration-300">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-shopping-cart text-white"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Orders</span>
            </a>
            
            <a href="{{ route('inventory.index') }}" class="group flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-violet-100 rounded-xl hover:shadow-lg transition-all duration-300">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-warehouse text-white"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Inventory</span>
            </a>
            
            <a href="{{ route('expenses.index') }}" class="group flex flex-col items-center p-4 bg-gradient-to-br from-orange-50 to-red-100 rounded-xl hover:shadow-lg transition-all duration-300">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-receipt text-white"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Expenses</span>
            </a>
            
            <a href="{{ route('purchase-orders.index') }}" class="group flex flex-col items-center p-4 bg-gradient-to-br from-teal-50 to-cyan-100 rounded-xl hover:shadow-lg transition-all duration-300">
                <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-shopping-bag text-white"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Purchase Orders</span>
            </a>
        </div>
    </div>
</div>
@endsection
