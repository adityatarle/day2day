@extends('layouts.super-admin')

@section('title', 'Roles & Permissions')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Roles & Permissions</h1>
                <p class="text-gray-600 mt-1">Manage user roles and their associated permissions across the system.</p>
            </div>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($roles as $role)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover-lift">
            <div class="p-6">
                <!-- Role Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center
                            {{ $role->name === 'admin' ? 'bg-red-100' : 
                               ($role->name === 'branch_manager' ? 'bg-blue-100' : 
                               ($role->name === 'cashier' ? 'bg-green-100' : 'bg-yellow-100')) }}">
                            @if($role->name === 'admin')
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            @elseif($role->name === 'branch_manager')
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            @elseif($role->name === 'cashier')
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</h3>
                            <p class="text-sm text-gray-500">{{ $role->users_count }} users</p>
                        </div>
                    </div>
                </div>

                <!-- Role Description -->
                <div class="mb-4">
                    @if($role->name === 'admin')
                        <p class="text-sm text-gray-600">Full system access, user management, all modules and settings. Can manage all branches and users.</p>
                    @elseif($role->name === 'branch_manager')
                        <p class="text-sm text-gray-600">Branch-specific management, inventory control, order processing, and staff supervision.</p>
                    @elseif($role->name === 'cashier')
                        <p class="text-sm text-gray-600">Order processing, billing, customer service, and payment handling for on-shop sales.</p>
                    @else
                        <p class="text-sm text-gray-600">Delivery management, returns processing, and customer adjustments for online orders.</p>
                    @endif
                </div>

                <!-- Permissions List -->
                <div class="space-y-2">
                    <h4 class="text-sm font-semibold text-gray-900">Key Permissions:</h4>
                    <div class="space-y-1">
                        @if($role->name === 'admin')
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                User & Role Management
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Branch Management
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                System Settings
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                All Reports & Analytics
                            </div>
                        @elseif($role->name === 'branch_manager')
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Inventory Management
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Order Processing
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Branch Reports
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Vendor Management
                            </div>
                        @elseif($role->name === 'cashier')
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Order Creation & Billing
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Payment Processing
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Customer Service
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Limited Inventory View
                            </div>
                        @else
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Delivery Management
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Return Processing
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Customer Adjustments
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Mobile App Access
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Role Stats -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $role->users_count }}</p>
                        <p class="text-xs text-gray-500">Active Users</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Role Hierarchy -->
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Role Hierarchy & Access Levels</h3>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                <!-- Admin Level -->
                <div class="flex items-start space-x-4 p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="bg-red-100 p-2 rounded-lg mt-1">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-red-900">Admin (Owner/Manager)</h4>
                        <p class="text-sm text-red-800 mb-2">Highest level access with complete system control</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-red-700">
                            <div>✓ User & Role Management</div>
                            <div>✓ Branch Management</div>
                            <div>✓ System Configuration</div>
                            <div>✓ Financial Reports</div>
                            <div>✓ Business Intelligence</div>
                            <div>✓ All Module Access</div>
                        </div>
                    </div>
                </div>

                <!-- Branch Manager Level -->
                <div class="flex items-start space-x-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="bg-blue-100 p-2 rounded-lg mt-1">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-blue-900">Branch Manager</h4>
                        <p class="text-sm text-blue-800 mb-2">Branch-level operations and management</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-blue-700">
                            <div>✓ Inventory Management</div>
                            <div>✓ Order Processing</div>
                            <div>✓ Vendor Relations</div>
                            <div>✓ Branch Reports</div>
                            <div>✓ Staff Supervision</div>
                            <div>✗ User Management</div>
                        </div>
                    </div>
                </div>

                <!-- Cashier Level -->
                <div class="flex items-start space-x-4 p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="bg-green-100 p-2 rounded-lg mt-1">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-green-900">Cashier (On-Shop Sales)</h4>
                        <p class="text-sm text-green-800 mb-2">Customer service and billing operations</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-green-700">
                            <div>✓ Order Creation</div>
                            <div>✓ Payment Processing</div>
                            <div>✓ Customer Service</div>
                            <div>✓ Basic Reports</div>
                            <div>✗ Inventory Management</div>
                            <div>✗ Vendor Management</div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Boy Level -->
                <div class="flex items-start space-x-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="bg-yellow-100 p-2 rounded-lg mt-1">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-yellow-900">Delivery Boy (Online Delivery)</h4>
                        <p class="text-sm text-yellow-800 mb-2">Delivery and return management</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-yellow-700">
                            <div>✓ Delivery Management</div>
                            <div>✓ Return Processing</div>
                            <div>✓ Customer Adjustments</div>
                            <div>✓ Mobile App Access</div>
                            <div>✗ Order Creation</div>
                            <div>✗ Inventory Access</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Matrix -->
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Permission Matrix</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Module / Feature</th>
                        <th class="text-center">Admin</th>
                        <th class="text-center">Branch Manager</th>
                        <th class="text-center">Cashier</th>
                        <th class="text-center">Delivery Boy</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-medium">User Management</td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="font-medium">Inventory Management</td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-medium">Order Processing</td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="font-medium">Vendor Management</td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-medium">Financial Reports</td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </td>
                        <td class="text-center">
                            <svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-center space-x-6 text-xs">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Full Access
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-yellow-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Limited Access
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    No Access
                </div>
            </div>
        </div>
    </div>
</div>
@endsection