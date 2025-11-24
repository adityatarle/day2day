@extends('layouts.cashier')

@section('title', 'Cashier Dashboard')

@section('content')
<div class="p-6 space-y-8 bg-gradient-to-br from-slate-50 via-teal-50 to-cyan-50 min-h-screen">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 0%, transparent 50%);"></div>
        </div>
        
        <div class="relative flex items-center justify-between">
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center float-animation">
                        <i class="fas fa-cash-register text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-1 bg-gradient-to-r from-white to-cyan-100 bg-clip-text text-transparent">
                            POS Terminal
                        </h1>
                        <p class="text-cyan-100 text-lg font-medium">{{ $branch_info['name'] }} - {{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6 mt-4">
                    @if($current_session)
                    <div class="flex items-center space-x-2 bg-green-500/20 backdrop-blur-sm rounded-full px-4 py-2 border border-green-400/30">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium">Session Active</span>
                    </div>
                    @else
                    <div class="flex items-center space-x-2 bg-red-500/20 backdrop-blur-sm rounded-full px-4 py-2 border border-red-400/30">
                        <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                        <span class="text-sm font-medium">Session Inactive</span>
                    </div>
                    @endif
                    @if(isset($previous_closing_balance))
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-wallet text-cyan-200"></i>
                        <span class="text-sm font-medium">Prev Close: ₹{{ number_format($previous_closing_balance ?? 0, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-map-marker-alt text-cyan-200"></i>
                        <span class="text-sm font-medium">{{ $branch_info['address'] ?? 'Address not available' }}</span>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="text-right space-y-2">
                    <div class="text-3xl font-bold bg-gradient-to-r from-white to-cyan-100 bg-clip-text text-transparent" id="ist-time">
                        {{ Carbon\Carbon::now('Asia/Kolkata')->format('H:i') }}
                    </div>
                    <div class="text-cyan-200 text-lg font-medium" id="ist-date">
                        {{ Carbon\Carbon::now('Asia/Kolkata')->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- POS Session Status -->
    @if($current_session)
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold">Active POS Session</h3>
                <p class="text-green-100">
                    <i class="fas fa-user mr-1"></i>
                    Handled by: <span class="font-semibold">{{ $current_session->handled_by }}</span>
                </p>
                <p class="text-green-100">Started: {{ $current_session->started_at->format('M d, Y H:i') }}</p>
                <p class="text-green-100">Duration: {{ $current_session->started_at->diffForHumans(null, true) }}</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">₹{{ number_format($today_stats['session_sales'], 2) }}</div>
                <div class="text-green-100">{{ $today_stats['session_orders'] }} orders</div>
                <div class="mt-4 flex space-x-2">
                    <a href="{{ route('pos.index') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-shopping-cart mr-1"></i>Continue Selling
                    </a>
                    <a href="{{ route('pos.session-manager') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-tasks mr-1"></i>Manage Session
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-gradient-to-r from-orange-500 to-red-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold">No Active Session</h3>
                <p class="text-orange-100">You need to start a POS session to process sales</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('pos.session-manager') }}" class="bg-white/20 hover:bg-white/30 px-4 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-tasks mr-2"></i>Session Manager
                </a>
                <a href="{{ route('pos.sessions.create') }}" class="bg-white/20 hover:bg-white/30 px-6 py-3 rounded-lg font-bold transition-colors">
                    <i class="fas fa-play mr-2"></i>Start Session
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Today's Performance -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Today's Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($today_stats['today_orders']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Today's Sales -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                    <p class="text-3xl font-bold text-gray-900">₹{{ number_format($today_stats['today_sales'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Available Products -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Available Products</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($branch_info['total_products']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-box text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-red-600 text-sm font-medium">{{ $quick_stats['out_of_stock_items'] }} Out of Stock</span>
            </div>
        </div>

        <!-- Today's Customers -->
        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Customers</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($quick_stats['total_customers_today']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders and Quick Product Access -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Your Recent Orders</h3>
            <div class="space-y-4">
                @foreach($recent_orders->take(8) as $order)
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-receipt text-teal-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">#{{ $order->id }}</p>
                            <p class="text-sm text-gray-600">{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">₹{{ number_format($order->total_amount, 2) }}</p>
                        <p class="text-sm text-gray-600">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Product Access -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Quick Product Access</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($available_products->take(10) as $product)
                <div class="p-3 rounded-lg border border-gray-200 hover:border-teal-300 hover:bg-teal-50 transition-colors cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-semibold text-gray-900 text-sm">{{ Str::limit($product['name'], 20) }}</h4>
                            <p class="text-xs text-gray-600">{{ $product['code'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-teal-600 text-sm">₹{{ number_format($product['price'], 2) }}</p>
                            <p class="text-xs text-gray-600">{{ $product['stock'] }} left</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Session History and Recent Customers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Session History -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Recent Sessions</h3>
            <div class="space-y-4">
                @foreach($session_history->take(5) as $session)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Session #{{ $session['id'] }}</p>
                            <p class="text-sm text-gray-600">{{ $session['started_at']->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">₹{{ number_format($session['total_sales'], 2) }}</p>
                        <p class="text-sm text-gray-600">{{ $session['total_orders'] }} orders</p>
                        <span class="px-2 py-1 text-xs rounded-full {{ $session['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($session['status']) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Customers -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Recent Customers</h3>
            <div class="space-y-4">
                @foreach($recent_customers as $customer)
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-purple-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $customer->name }}</p>
                            <p class="text-sm text-gray-600">{{ $customer->phone ?? 'No phone' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">{{ $customer->orders_count }} orders</p>
                        <p class="text-sm text-gray-600">{{ $customer->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="{{ route('pos.index') }}" class="bg-gradient-to-r from-teal-500 to-cyan-600 rounded-2xl p-6 text-white hover:from-teal-600 hover:to-cyan-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">POS System</h4>
                    <p class="text-cyan-100 text-sm">Process sales</p>
                </div>
            </div>
        </a>

        <a href="{{ route('orders.create') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-white hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">New Order</h4>
                    <p class="text-blue-100 text-sm">Create order</p>
                </div>
            </div>
        </a>

        <a href="{{ route('customers.create') }}" class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-6 text-white hover:from-purple-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-plus text-xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Add Customer</h4>
                    <p class="text-purple-100 text-sm">Register customer</p>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.float-animation {
    animation: float 3s ease-in-out infinite;
}
</style>

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