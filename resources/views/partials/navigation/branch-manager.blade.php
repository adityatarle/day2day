<!-- Branch Manager Navigation -->
<nav class="p-6 space-y-2">
    <a href="{{ route('dashboard.branch_manager') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('dashboard.branch_manager') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        <span class="font-medium">Branch Dashboard</span>
    </a>

    <!-- Branch Operations -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Branch Operations</p>
    </div>
    
    
    <!-- Ordering -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Ordering</p>
    </div>
    
    <a href="{{ route('branch.product-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.product-orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <span class="font-medium">Product Orders</span>
    </a>
    
    <a href="{{ route('branch.product-orders.create') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.product-orders.create') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-plus-circle"></i>
        </div>
        <span class="font-medium">Order Products</span>
    </a>

    <a href="{{ route('branch.purchase-entries.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.purchase-entries.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-truck"></i>
        </div>
        <span class="font-medium">Purchase Entry</span>
    </a>

    <!-- Reports -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Reports & Analytics</p>
    </div>
    
    <a href="{{ route('branch.reports.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.reports.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-chart-bar"></i>
        </div>
        <span class="font-medium">Branch Reports</span>
    </a>
</nav>