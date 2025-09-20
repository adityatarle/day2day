<!-- Super Admin Navigation -->
<nav class="p-6 space-y-2">
    <a href="{{ route('dashboard.super_admin') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('dashboard.super_admin') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-crown"></i>
        </div>
        <span class="font-medium">Dashboard</span>
    </a>

    <!-- Stock Management -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Stock Management</p>
    </div>
    
    <a href="{{ route('inventory.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('inventory.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-warehouse"></i>
        </div>
        <span class="font-medium">Inventory</span>
    </a>
    
    <a href="{{ route('products.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('products.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-apple-alt"></i>
        </div>
        <span class="font-medium">Products</span>
    </a>

    <!-- Orders & Sales -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Orders & Sales</p>
    </div>
    
    <a href="{{ route('orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('orders.*') && !request()->routeIs('orders.workflow.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <span class="font-medium">Orders</span>
    </a>
    
    <a href="{{ route('orders.workflow.dashboard') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('orders.workflow.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-tasks"></i>
        </div>
        <span class="font-medium">Order Workflow</span>
    </a>
    
    <a href="{{ route('purchase-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('purchase-orders.*') && !request()->routeIs('admin.branch-orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-file-invoice"></i>
        </div>
        <span class="font-medium">Purchase Orders</span>
    </a>

    <a href="{{ route('admin.local-purchases.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.local-purchases.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shopping-basket"></i>
        </div>
        <span class="font-medium">Local Purchase Requests</span>
        @php
            $pendingCount = \App\Models\LocalPurchase::where('status', 'pending')->count();
        @endphp
        @if($pendingCount > 0)
            <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendingCount }}</span>
        @endif
    </a>

    <a href="{{ route('admin.branch-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.branch-orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-store"></i>
        </div>
        <span class="font-medium">Branch Orders</span>
    </a>

    <a href="{{ route('admin.purchase-entries.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.purchase-entries.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-receipt"></i>
        </div>
        <span class="font-medium">Purchase Entries</span>
    </a>

    <!-- Customer Management -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Customer Management</p>
    </div>
    
    <a href="{{ route('customers.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('customers.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-users"></i>
        </div>
        <span class="font-medium">Customers</span>
    </a>

    <!-- Vendors & Partners -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Vendors & Partners</p>
    </div>
    
    <a href="{{ route('vendors.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('vendors.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-handshake"></i>
        </div>
        <span class="font-medium">Vendors</span>
    </a>

    <!-- Branches & Outlets -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Branches & Outlets</p>
    </div>
    
    <a href="{{ route('admin.branches.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.branches.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-store-alt"></i>
        </div>
        <span class="font-medium">Branches</span>
    </a>
    
    <a href="{{ route('outlets.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('outlets.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-building"></i>
        </div>
        <span class="font-medium">Outlets</span>
    </a>

    <!-- System Management -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">System Management</p>
    </div>
    
    <a href="{{ route('admin.users.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.users.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-users-cog"></i>
        </div>
        <span class="font-medium">Users</span>
    </a>
    
    <a href="{{ route('admin.roles.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.roles.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shield-alt"></i>
        </div>
        <span class="font-medium">Roles</span>
    </a>

    <!-- POS System -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">POS System</p>
    </div>
    
    <a href="{{ route('pos.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.*') && !request()->routeIs('pos.sessions.*') && !request()->routeIs('billing.quickSale') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-cash-register"></i>
        </div>
        <span class="font-medium">POS System</span>
    </a>
    
    <a href="{{ route('billing.quickSale') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('billing.quickSale') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-bolt"></i>
        </div>
        <span class="font-medium">Quick Sale</span>
    </a>
    
    <a href="{{ route('pos.sessions.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.sessions.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-clock"></i>
        </div>
        <span class="font-medium">POS Sessions</span>
    </a>

    <!-- Reports -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Reports</p>
    </div>
    
    <a href="{{ route('reports.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('reports.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-chart-line"></i>
        </div>
        <span class="font-medium">Reports</span>
    </a>
    
    <a href="{{ route('admin.analytics') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.analytics') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-chart-pie"></i>
        </div>
        <span class="font-medium">Analytics</span>
    </a>

    <!-- Settings -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Settings</p>
    </div>
    
    <a href="{{ route('admin.settings') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.settings') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-cogs"></i>
        </div>
        <span class="font-medium">Settings</span>
    </a>
    
    <a href="{{ route('admin.security') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.security') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-lock"></i>
        </div>
        <span class="font-medium">Security</span>
    </a>
</nav>