<!-- Branch Manager Navigation -->
<nav class="p-6 space-y-2">
    <a href="{{ route('dashboard.branch_manager') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('dashboard.branch_manager') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        <span class="font-medium">Branch Dashboard</span>
    </a>

    <!-- Stock -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Stock</p>
    </div>
    <a href="{{ route('branch.inventory.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.inventory.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-warehouse"></i>
        </div>
        <span class="font-medium">Current Stock</span>
    </a>

    <!-- Inventory -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Inventory</p>
    </div>
    <div class="space-y-1">
        <a href="{{ route('inventory.addStockForm') }}" class="nav-link flex items-center p-2.5 rounded-lg text-gray-300 ml-12 {{ request()->routeIs('inventory.addStockForm') ? 'active text-white' : '' }}">
            <i class="fas fa-circle-arrow-down mr-3"></i>
            <span class="text-sm">Add Stock (Inward)</span>
        </a>
        <a href="{{ route('inventory.stockMovements') }}" class="nav-link flex items-center p-2.5 rounded-lg text-gray-300 ml-12 {{ request()->routeIs('inventory.stockMovements') ? 'active text-white' : '' }}">
            <i class="fas fa-right-left mr-3"></i>
            <span class="text-sm">Stock Movements (Outward)</span>
        </a>
        <a href="{{ route('inventory.recordLossForm') }}" class="nav-link flex items-center p-2.5 rounded-lg text-gray-300 ml-12 {{ request()->routeIs('inventory.recordLossForm') ? 'active text-white' : '' }}">
            <i class="fas fa-circle-minus mr-3"></i>
            <span class="text-sm">Adjustments (Loss/Damage)</span>
        </a>
        <a href="{{ route('inventory.batches') }}" class="nav-link flex items-center p-2.5 rounded-lg text-gray-300 ml-12 {{ request()->routeIs('inventory.batches') ? 'active text-white' : '' }}">
            <i class="fas fa-layer-group mr-3"></i>
            <span class="text-sm">Batches</span>
        </a>
        <a href="{{ route('inventory.valuation') }}" class="nav-link flex items-center p-2.5 rounded-lg text-gray-300 ml-12 {{ request()->routeIs('inventory.valuation') ? 'active text-white' : '' }}">
            <i class="fas fa-coins mr-3"></i>
            <span class="text-sm">Valuation</span>
        </a>
        <a href="{{ route('inventory.lossTracking') }}" class="nav-link flex items-center p-2.5 rounded-lg text-gray-300 ml-12 {{ request()->routeIs('inventory.lossTracking') ? 'active text-white' : '' }}">
            <i class="fas fa-triangle-exclamation mr-3"></i>
            <span class="text-sm">Loss Tracking</span>
        </a>
        <a href="{{ route('inventory.lowStockAlerts') }}" class="nav-link flex items-center p-2.5 rounded-lg text-gray-300 ml-12 {{ request()->routeIs('inventory.lowStockAlerts') ? 'active text-white' : '' }}">
            <i class="fas fa-bell mr-3"></i>
            <span class="text-sm">Low Stock Alerts</span>
        </a>
    </div>

    <!-- Order Products -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Order Products</p>
    </div>
    <a href="{{ route('branch.product-orders.create') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.product-orders.create') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-cart-plus"></i>
        </div>
        <span class="font-medium">Order Products</span>
    </a>
    <a href="{{ route('branch.product-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.product-orders.*') && !request()->routeIs('branch.product-orders.create') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-list"></i>
        </div>
        <span class="font-medium">My Orders</span>
    </a>

    <!-- Purchase Entries -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Purchase Entries</p>
    </div>
    <a href="{{ route('branch.purchase-entries.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.purchase-entries.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-truck"></i>
        </div>
        <span class="font-medium">Purchase Entries</span>
    </a>

    <!-- Local Purchases -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Local Purchases</p>
    </div>
    <a href="{{ route('branch.local-purchases.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.local-purchases.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shopping-basket"></i>
        </div>
        <span class="font-medium">Local Purchases</span>
    </a>

    <!-- POS -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">POS</p>
    </div>
    <a href="{{ route('pos.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-cash-register"></i>
        </div>
        <span class="font-medium">POS</span>
    </a>

    <!-- Staff Management -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Staff Management</p>
    </div>
    <a href="{{ route('branch.staff.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.staff.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-users"></i>
        </div>
        <span class="font-medium">Manage Staff</span>
    </a>

    <!-- Reports (Optional) -->
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