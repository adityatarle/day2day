@extends('layouts.app')

@section('title', 'Profit & Loss')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Profit & Loss</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($totalRevenue ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Estimated Costs</p>
            <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($estimatedCosts ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Gross Profit</p>
            <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($grossProfit ?? 0, 2) }}</p>
        </div>
    </div>
</div>
@endsection

