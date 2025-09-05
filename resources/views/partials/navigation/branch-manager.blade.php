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
    
    <a href="{{ route('branch.staff.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.staff.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-users"></i>
        </div>
        <span class="font-medium">Staff Management</span>
        <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full">{{ auth()->user()->branch->cashiers()->count() }}</span>
    </a>
    
    <a href="{{ route('branch.inventory.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.inventory.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-boxes"></i>
        </div>
        <span class="font-medium">Branch Inventory</span>
    </a>
    
    <a href="{{ route('branch.orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <span class="font-medium">Branch Orders</span>
    </a>

    <!-- Sales & POS -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Sales & POS</p>
    </div>
    
    <a href="{{ route('pos.sessions.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.sessions.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-cash-register"></i>
        </div>
        <span class="font-medium">POS Sessions</span>
        @if(auth()->user()->branch->activePosSessionsCount() > 0)
        <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">{{ auth()->user()->branch->activePosSessionsCount() }} Active</span>
        @endif
    </a>
    
    <a href="{{ route('billing.quickSale') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('billing.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-plus-circle"></i>
        </div>
        <span class="font-medium">Quick Sale</span>
    </a>

    <!-- Branch Management -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Branch Management</p>
    </div>
    
    <a href="{{ route('branch.customers.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.customers.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-user-friends"></i>
        </div>
        <span class="font-medium">Customers</span>
    </a>
    
    <a href="{{ route('purchase-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('purchase-orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-file-invoice"></i>
        </div>
        <span class="font-medium">Purchase Orders</span>
    </a>
    
    <a href="{{ route('branch.expenses.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('branch.expenses.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-receipt"></i>
        </div>
        <span class="font-medium">Expenses</span>
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