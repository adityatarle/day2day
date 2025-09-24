<!-- Cashier Navigation -->
<nav class="p-6 space-y-2">
    <a href="{{ route('dashboard.cashier') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('dashboard.cashier') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-cash-register"></i>
        </div>
        <span class="font-medium">POS Dashboard</span>
    </a>

    <!-- POS Operations -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">POS Operations</p>
    </div>
    
    <a href="{{ route('pos.sale') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.sale') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <span class="font-medium">New Sale</span>
        <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">Quick</span>
    </a>
    
    <a href="{{ route('billing.quickSale') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('billing.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-bolt"></i>
        </div>
        <span class="font-medium">Quick Sale</span>
    </a>

    <!-- Session Management -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Session Management</p>
    </div>
    
    <a href="{{ route('pos.session.current') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.session.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-clock"></i>
        </div>
        <span class="font-medium">Current Session</span>
        @php
            $currentSession = auth()->user()->currentPosSession();
        @endphp
        @if($currentSession)
        <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">Active</span>
        @else
        <span class="ml-auto bg-gray-500 text-white text-xs px-2 py-1 rounded-full">Closed</span>
        @endif
    </a>
    
    <a href="{{ route('pos.session.history') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.session.history') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-history"></i>
        </div>
        <span class="font-medium">Session History</span>
    </a>

    <a href="{{ route('pos.ledger.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.ledger.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-wallet"></i>
        </div>
        <span class="font-medium">Cash Give/Take</span>
    </a>

    <!-- Orders & Sales -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Orders & Sales</p>
    </div>
    
    <a href="{{ route('cashier.orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('cashier.orders.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-list-alt"></i>
        </div>
        <span class="font-medium">My Sales</span>
    </a>
    
    <a href="{{ route('cashier.returns.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('cashier.returns.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-undo"></i>
        </div>
        <span class="font-medium">Returns & Refunds</span>
    </a>

    <!-- Inventory (Read-only) -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Inventory</p>
    </div>
    
    <a href="{{ route('cashier.inventory.view') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('cashier.inventory.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-eye"></i>
        </div>
        <span class="font-medium">View Inventory</span>
        <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full">Read Only</span>
    </a>

    <!-- Quick Actions -->
    <div class="pt-4 pb-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Quick Actions</p>
    </div>
    
    <a href="{{ route('cashier.customers.search') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('cashier.customers.*') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-search"></i>
        </div>
        <span class="font-medium">Customer Lookup</span>
    </a>
    
    <a href="{{ route('cashier.help') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('cashier.help') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-question-circle"></i>
        </div>
        <span class="font-medium">Help & Support</span>
    </a>
</nav>