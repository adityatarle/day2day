<!-- Super Admin Navigation -->
<nav class="p-6 space-y-2">
    <a href="{{ route('dashboard.super_admin') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('dashboard.super_admin') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-crown"></i>
        </div>
        <span class="font-medium">Super Admin Dashboard</span>
    </a>

    <!-- System Management -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">System Management</p>
    </div>
    
    <a href="{{ route('admin.users.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.users.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-users-cog"></i>
        </div>
        <span class="font-medium">User Management</span>
    </a>
    
    <a href="{{ route('admin.branches.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.branches.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-store-alt"></i>
        </div>
        <span class="font-medium">Branch Management</span>
    </a>
    
    <a href="{{ route('admin.roles.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.roles.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shield-alt"></i>
        </div>
        <span class="font-medium">Roles & Permissions</span>
    </a>

    <!-- Business Operations -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Business Operations</p>
    </div>
    
    <a href="{{ route('products.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('products.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-apple-alt"></i>
        </div>
        <span class="font-medium">Products</span>
    </a>
    
    <a href="{{ route('inventory.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('inventory.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-warehouse"></i>
        </div>
        <span class="font-medium">Global Inventory</span>
    </a>
    
    <a href="{{ route('vendors.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('vendors.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-handshake"></i>
        </div>
        <span class="font-medium">Vendor Management</span>
    </a>
    
    <a href="{{ route('purchase-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('purchase-orders.*') && !request()->routeIs('admin.branch-orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-file-invoice"></i>
        </div>
        <span class="font-medium">Purchase Orders</span>
    </a>

    <a href="{{ route('admin.branch-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.branch-orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-store"></i>
        </div>
        <span class="font-medium">Orders from Branches</span>
    </a>

    <!-- Analytics & Reports -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Analytics & Reports</p>
    </div>
    
    <a href="{{ route('reports.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('reports.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-chart-line"></i>
        </div>
        <span class="font-medium">System Reports</span>
    </a>
    
    <a href="{{ route('admin.analytics') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.analytics') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-chart-pie"></i>
        </div>
        <span class="font-medium">Business Analytics</span>
    </a>

    <!-- System Settings -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">System Settings</p>
    </div>
    
    <a href="{{ route('admin.settings') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.settings') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-cogs"></i>
        </div>
        <span class="font-medium">System Settings</span>
    </a>
</nav>