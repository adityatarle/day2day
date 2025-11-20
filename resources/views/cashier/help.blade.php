@extends('layouts.cashier')

@section('title', 'Help & Support - POS System')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-question-circle text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Help & Support</h1>
                <p class="text-gray-600">Get assistance with the POS system</p>
            </div>
        </div>
    </div>

    <!-- Quick Help Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Quick Sale Help -->
        <div class="cashier-card rounded-xl p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bolt text-green-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Quick Sale</h3>
            </div>
            <p class="text-gray-600 text-sm mb-4">Process fast transactions with minimal steps</p>
            <ul class="text-sm text-gray-600 space-y-2">
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Select products quickly</span>
                </li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Apply discounts instantly</span>
                </li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Multiple payment methods</span>
                </li>
            </ul>
        </div>

        <!-- Session Management -->
        <div class="cashier-card rounded-xl p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Session Management</h3>
            </div>
            <p class="text-gray-600 text-sm mb-4">Manage your POS sessions effectively</p>
            <ul class="text-sm text-gray-600 space-y-2">
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Start/End sessions</span>
                </li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Track daily sales</span>
                </li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Cash reconciliation</span>
                </li>
            </ul>
        </div>

        <!-- Returns & Refunds -->
        <div class="cashier-card rounded-xl p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-undo text-orange-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Returns & Refunds</h3>
            </div>
            <p class="text-gray-600 text-sm mb-4">Handle customer returns and refunds</p>
            <ul class="text-sm text-gray-600 space-y-2">
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Process returns easily</span>
                </li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Multiple refund options</span>
                </li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-check text-green-500 text-xs"></i>
                    <span>Return history tracking</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Detailed Help Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- POS Operations Guide -->
        <div class="cashier-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-cash-register text-purple-600 mr-3"></i>
                POS Operations Guide
            </h2>
            
            <div class="space-y-4">
                <div class="border-l-4 border-purple-500 pl-4">
                    <h3 class="font-semibold text-gray-900">Starting a Sale</h3>
                    <p class="text-sm text-gray-600 mt-1">Click "New Sale" or "Quick Sale" to begin processing a transaction</p>
                </div>
                
                <div class="border-l-4 border-green-500 pl-4">
                    <h3 class="font-semibold text-gray-900">Adding Products</h3>
                    <p class="text-sm text-gray-600 mt-1">Search and select products from the inventory. Use barcode scanner if available</p>
                </div>
                
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-semibold text-gray-900">Payment Processing</h3>
                    <p class="text-sm text-gray-600 mt-1">Accept cash, card, or digital payments. Print receipt when complete</p>
                </div>
                
                <div class="border-l-4 border-orange-500 pl-4">
                    <h3 class="font-semibold text-gray-900">Session Management</h3>
                    <p class="text-sm text-gray-600 mt-1">Start your session at the beginning of your shift and end it when finished</p>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="cashier-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-tools text-red-600 mr-3"></i>
                Troubleshooting
            </h2>
            
            <div class="space-y-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h3 class="font-semibold text-red-800 mb-2">Printer Not Working</h3>
                    <ul class="text-sm text-red-700 space-y-1">
                        <li>• Check printer connection</li>
                        <li>• Verify paper and ink levels</li>
                        <li>• Restart printer if needed</li>
                    </ul>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="font-semibold text-yellow-800 mb-2">Slow Performance</h3>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        <li>• Clear browser cache</li>
                        <li>• Check internet connection</li>
                        <li>• Close unnecessary tabs</li>
                    </ul>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 mb-2">Login Issues</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Verify username and password</li>
                        <li>• Check with manager for account status</li>
                        <li>• Clear browser data if needed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts -->
    <div class="cashier-card rounded-xl p-6 mt-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-keyboard text-indigo-600 mr-3"></i>
            Keyboard Shortcuts
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-700">New Sale</span>
                <kbd class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Ctrl + N</kbd>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-700">Quick Sale</span>
                <kbd class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Ctrl + Q</kbd>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-700">Search Products</span>
                <kbd class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Ctrl + F</kbd>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-700">Print Receipt</span>
                <kbd class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Ctrl + P</kbd>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-700">Customer Lookup</span>
                <kbd class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Ctrl + C</kbd>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-700">Help</span>
                <kbd class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">F1</kbd>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="cashier-card rounded-xl p-6 mt-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-headset text-green-600 mr-3"></i>
            Contact Support
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-phone text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Phone Support</h3>
                        <p class="text-sm text-gray-600">+1 (555) 123-4567</p>
                        <p class="text-xs text-gray-500">Mon-Fri 8AM-6PM</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-envelope text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Email Support</h3>
                        <p class="text-sm text-gray-600">support@foodco.com</p>
                        <p class="text-xs text-gray-500">24/7 Response</p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-tie text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Manager Support</h3>
                        <p class="text-sm text-gray-600">Contact your branch manager</p>
                        <p class="text-xs text-gray-500">For urgent issues</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-book text-orange-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Documentation</h3>
                        <p class="text-sm text-gray-600">User Manual & Guides</p>
                        <p class="text-xs text-gray-500">Available online</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-wrap gap-4 mt-8">
        <a href="{{ route('billing.quickSale') }}" class="quick-action px-6 py-3 text-white rounded-lg font-medium">
            <i class="fas fa-bolt mr-2"></i>
            Start Quick Sale
        </a>
        
        <a href="{{ route('pos.sale') }}" class="bg-white border-2 border-purple-500 text-purple-600 px-6 py-3 rounded-lg font-medium hover:bg-purple-50 transition-colors">
            <i class="fas fa-shopping-cart mr-2"></i>
            New Sale
        </a>
        
        <a href="{{ route('cashier.customers.search') }}" class="bg-white border-2 border-blue-500 text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors">
            <i class="fas fa-search mr-2"></i>
            Customer Lookup
        </a>
        
        <a href="{{ route('dashboard.cashier') }}" class="bg-white border-2 border-gray-500 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
    </div>
</div>

<script>
// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // F1 - Help
    if (e.key === 'F1') {
        e.preventDefault();
        // Already on help page
    }
    
    // Ctrl + N - New Sale
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '{{ route("pos.sale") }}';
    }
    
    // Ctrl + Q - Quick Sale
    if (e.ctrlKey && e.key === 'q') {
        e.preventDefault();
        window.location.href = '{{ route("billing.quickSale") }}';
    }
    
    // Ctrl + C - Customer Lookup
    if (e.ctrlKey && e.key === 'c') {
        e.preventDefault();
        window.location.href = '{{ route("cashier.customers.search") }}';
    }
});
</script>
@endsection



