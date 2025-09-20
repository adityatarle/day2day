<!-- Super Admin / Admin Navigation -->
<nav class="p-6 space-y-2">
    <!-- Main Menu -->
    <div class="pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Main Menu</p>
    </div>

    <!-- Dashboard -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.dashboard') ? 'admin.dashboard' : (\Illuminate\Support\Facades\Route::has('dashboard.admin') ? 'dashboard.admin' : 'dashboard.super_admin')) }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.dashboard') || request()->routeIs('dashboard.admin') || request()->routeIs('dashboard.super_admin') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-gauge"></i>
        </div>
        <span class="font-medium">Dashboard</span>
    </a>

    <!-- Products -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.products.index') ? 'admin.products.index' : 'products.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.products.*') || request()->routeIs('products.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-apple-alt"></i>
        </div>
        <span class="font-medium">Products</span>
    </a>

    <!-- Orders -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.orders.index') ? 'admin.orders.index' : 'orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.orders.*') || (request()->routeIs('orders.*') && !request()->routeIs('orders.workflow.*')) ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <span class="font-medium">Orders</span>
    </a>

    <!-- Inventory -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.inventory.index') ? 'admin.inventory.index' : 'inventory.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.inventory.*') || request()->routeIs('inventory.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-warehouse"></i>
        </div>
        <span class="font-medium">Inventory</span>
    </a>

    <!-- Customers -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.customers.index') ? 'admin.customers.index' : 'customers.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.customers.*') || request()->routeIs('customers.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-users"></i>
        </div>
        <span class="font-medium">Customers</span>
    </a>

    <!-- Vendors -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.vendors.index') ? 'admin.vendors.index' : 'vendors.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.vendors.*') || request()->routeIs('vendors.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-handshake"></i>
        </div>
        <span class="font-medium">Vendors</span>
    </a>

    <!-- Purchase Orders -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.purchase-orders.index') ? 'admin.purchase-orders.index' : 'purchase-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.purchase-orders.*') || request()->routeIs('purchase-orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-file-invoice"></i>
        </div>
        <span class="font-medium">Purchase Orders</span>
    </a>

    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <!-- Orders from Branches -->
    <a href="{{ route('admin.branch-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.branch-orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-store"></i>
        </div>
        <span class="font-medium">Orders from Branches</span>
    </a>

    <!-- Local Purchase Requests with Pending Count -->
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
    @endif

    <!-- Reports -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.reports.index') ? 'admin.reports.index' : 'reports.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.reports.*') || request()->routeIs('reports.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-chart-line"></i>
        </div>
        <span class="font-medium">Reports</span>
    </a>

    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <!-- Outlets (Admin Branches Index) -->
    <a href="{{ route('admin.branches.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.branches.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-store-alt"></i>
        </div>
        <span class="font-medium">Outlets</span>
    </a>
    @endif

    <!-- POS System -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.pos.index') ? 'admin.pos.index' : 'pos.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.pos.*') || (request()->routeIs('pos.*') && !request()->routeIs('pos.sessions.*')) ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-cash-register"></i>
        </div>
        <span class="font-medium">POS System</span>
    </a>

    <!-- Quick Sale -->
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('admin.quick-sale.index') ? 'admin.quick-sale.index' : 'billing.quickSale') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.quick-sale.*') || request()->routeIs('billing.quickSale') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-bolt"></i>
        </div>
        <span class="font-medium">Quick Sale</span>
    </a>

    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <!-- Optional: Additional Management -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Management</p>
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
    @endif
</nav>