@extends('layouts.cashier')

@section('title', 'Cashier Dashboard')

@section('content')
<div class="p-6 space-y-6 bg-gray-50 min-h-screen">
    <!-- Welcome Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cash-register text-xl text-gray-700"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">
                            POS Terminal
                        </h1>
                        <p class="text-gray-600 text-sm">{{ $branch_info['name'] }} - {{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4 mt-4">
                    @if($current_session)
                    <div class="flex items-center space-x-2 bg-green-50 rounded-md px-3 py-1.5 border border-green-200">
                        <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                        <span class="text-sm font-medium text-green-800">Session Active</span>
                    </div>
                    @else
                    <div class="flex items-center space-x-2 bg-gray-100 rounded-md px-3 py-1.5 border border-gray-300">
                        <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-700">Session Inactive</span>
                    </div>
                    @endif
                    @if(isset($previous_closing_balance))
                    <div class="flex items-center space-x-2 bg-gray-50 rounded-md px-3 py-1.5 border border-gray-200">
                        <i class="fas fa-wallet text-gray-600 text-xs"></i>
                        <span class="text-sm font-medium text-gray-700">Prev Close: ₹{{ number_format($previous_closing_balance ?? 0, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex items-center space-x-2 bg-gray-50 rounded-md px-3 py-1.5 border border-gray-200">
                        <i class="fas fa-map-marker-alt text-gray-600 text-xs"></i>
                        <span class="text-sm font-medium text-gray-700">{{ $branch_info['address'] ?? 'Address not available' }}</span>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="text-right space-y-1">
                    <div class="text-2xl font-semibold text-gray-900" id="ist-time">
                        {{ Carbon\Carbon::now('Asia/Kolkata')->format('H:i') }}
                    </div>
                    <div class="text-gray-600 text-sm" id="ist-date">
                        {{ Carbon\Carbon::now('Asia/Kolkata')->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- POS Session Status -->
    @if($current_session)
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Active POS Session</h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p>
                        <i class="fas fa-user mr-1.5"></i>
                        Handled by: <span class="font-medium text-gray-900">{{ $current_session->handled_by }}</span>
                    </p>
                    <p><i class="fas fa-clock mr-1.5"></i>Started: {{ $current_session->started_at->format('M d, Y H:i') }}</p>
                    <p><i class="fas fa-hourglass-half mr-1.5"></i>Duration: {{ $current_session->started_at->diffForHumans(null, true) }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-semibold text-gray-900">₹{{ number_format($today_stats['session_sales'], 2) }}</div>
                <div class="text-sm text-gray-600">{{ $today_stats['session_orders'] }} orders</div>
                <div class="mt-4 flex space-x-2">
                    <a href="{{ route('pos.index') }}" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        <i class="fas fa-shopping-cart mr-1"></i>Continue Selling
                    </a>
                    <a href="{{ route('pos.session-manager') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300">
                        <i class="fas fa-tasks mr-1"></i>Manage Session
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No Active Session</h3>
                <p class="text-sm text-gray-600">You need to start a POS session to process sales</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('pos.session-manager') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-md font-medium transition-colors border border-gray-300">
                    <i class="fas fa-tasks mr-2"></i>Session Manager
                </a>
                <a href="{{ route('pos.sessions.create') }}" class="bg-gray-900 hover:bg-gray-800 text-white px-6 py-2.5 rounded-md font-semibold transition-colors">
                    <i class="fas fa-play mr-2"></i>Start Session
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Today's Performance -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Today's Orders -->
        <div class="bg-white rounded-lg p-5 shadow-sm hover:shadow transition-all duration-200 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Today's Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($today_stats['today_orders']) }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-gray-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Today's Sales -->
        <div class="bg-white rounded-lg p-5 shadow-sm hover:shadow transition-all duration-200 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Today's Sales</p>
                    <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($today_stats['today_sales'], 2) }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-gray-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Available Products -->
        <div class="bg-white rounded-lg p-5 shadow-sm hover:shadow transition-all duration-200 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Available Products</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($branch_info['total_products']) }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-gray-600 text-lg"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs text-gray-600">{{ $quick_stats['out_of_stock_items'] }} Out of Stock</span>
            </div>
        </div>

        <!-- Today's Customers -->
        <div class="bg-white rounded-lg p-5 shadow-sm hover:shadow transition-all duration-200 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Today's Customers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($quick_stats['total_customers_today']) }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-gray-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders and Quick Product Access -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Recent Orders</h3>
            <div class="space-y-3">
                @foreach($recent_orders->take(8) as $order)
                <div class="flex items-center justify-between p-3 rounded-md hover:bg-gray-50 transition-colors border border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-gray-100 rounded-md flex items-center justify-center">
                            <i class="fas fa-receipt text-gray-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">#{{ $order->id }}</p>
                            <p class="text-xs text-gray-600">{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900 text-sm">₹{{ number_format($order->total_amount, 2) }}</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Product Access -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Product Access</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($available_products->take(10) as $product)
                <div class="p-3 rounded-md border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-gray-900 text-xs">{{ Str::limit($product['name'], 20) }}</h4>
                            <p class="text-xs text-gray-500">{{ $product['code'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900 text-xs">₹{{ number_format($product['price'], 2) }}</p>
                            <p class="text-xs text-gray-500">{{ $product['stock'] }} left</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Session History and Recent Customers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Session History -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Sessions</h3>
            <div class="space-y-3">
                @foreach($session_history->take(5) as $session)
                <div class="flex items-center justify-between p-3 rounded-md bg-gray-50 border border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-gray-100 rounded-md flex items-center justify-center">
                            <i class="fas fa-clock text-gray-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">Session #{{ $session['id'] }}</p>
                            <p class="text-xs text-gray-600">{{ $session['started_at']->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900 text-sm">₹{{ number_format($session['total_sales'], 2) }}</p>
                        <p class="text-xs text-gray-600">{{ $session['total_orders'] }} orders</p>
                        <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-md {{ $session['status'] == 'active' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                            {{ ucfirst($session['status']) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Customers -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Customers</h3>
            <div class="space-y-3">
                @foreach($recent_customers as $customer)
                <div class="flex items-center justify-between p-3 rounded-md hover:bg-gray-50 transition-colors border border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-gray-100 rounded-md flex items-center justify-center">
                            <i class="fas fa-user text-gray-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $customer->name }}</p>
                            <p class="text-xs text-gray-600">{{ $customer->phone ?? 'No phone' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900 text-sm">{{ $customer->orders_count }} orders</p>
                        <p class="text-xs text-gray-500">{{ $customer->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('pos.index') }}" class="bg-white rounded-lg p-5 border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200 shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="w-11 h-11 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cash-register text-white text-lg"></i>
                </div>
                <div>
                    <h4 class="text-base font-semibold text-gray-900">POS System</h4>
                    <p class="text-gray-600 text-sm">Process sales</p>
                </div>
            </div>
        </a>

        <a href="{{ route('orders.create') }}" class="bg-white rounded-lg p-5 border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200 shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="w-11 h-11 bg-gray-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus text-white text-lg"></i>
                </div>
                <div>
                    <h4 class="text-base font-semibold text-gray-900">New Order</h4>
                    <p class="text-gray-600 text-sm">Create order</p>
                </div>
            </div>
        </a>

        <a href="{{ route('customers.create') }}" class="bg-white rounded-lg p-5 border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200 shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="w-11 h-11 bg-gray-800 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-plus text-white text-lg"></i>
                </div>
                <div>
                    <h4 class="text-base font-semibold text-gray-900">Add Customer</h4>
                    <p class="text-gray-600 text-sm">Register customer</p>
                </div>
            </div>
        </a>
    </div>
</div>


<script>
// Update IST time every second
function updateISTTime() {
    const timeElement = document.getElementById('ist-time');
    const dateElement = document.getElementById('ist-date');
    
    if (timeElement || dateElement) {
        // Get current time in IST (UTC+5:30)
        const now = new Date();
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const ist = new Date(utc + (5.5 * 3600000)); // IST is UTC+5:30
        
        if (timeElement) {
            const hours = String(ist.getHours()).padStart(2, '0');
            const minutes = String(ist.getMinutes()).padStart(2, '0');
            timeElement.textContent = `${hours}:${minutes}`;
        }
        
        if (dateElement) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month = months[ist.getMonth()];
            const day = ist.getDate();
            const year = ist.getFullYear();
            dateElement.textContent = `${month} ${day}, ${year}`;
        }
    }
}

// Update immediately and then every second
updateISTTime();
setInterval(updateISTTime, 1000);
</script>
@endsection