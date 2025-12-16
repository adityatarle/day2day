<!-- Delivery Boy Navigation -->
<nav class="p-4 space-y-1">
    <a href="{{ route('delivery.dashboard') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('delivery.dashboard') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-motorcycle"></i>
        </div>
        <span class="font-medium">Dashboard</span>
    </a>

    <!-- Delivery Operations -->
    <div class="section-divider">My Deliveries</div>
    
    <a href="{{ route('delivery.assigned') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('delivery.assigned') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-box"></i>
        </div>
        <span class="font-medium">Assigned Deliveries</span>
        @php
            $pendingCount = \App\Models\Delivery::where('delivery_boy_id', auth()->id())
                ->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])
                ->count();
        @endphp
        @if($pendingCount > 0)
        <span class="ml-auto bg-orange-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendingCount }}</span>
        @endif
    </a>
    
    <a href="{{ route('delivery.history') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('delivery.history') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-history"></i>
        </div>
        <span class="font-medium">Delivery History</span>
    </a>

    <!-- Account -->
    <div class="section-divider">Account</div>
    
    <a href="{{ route('profile') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('profile') ? 'active text-white' : '' }}">
        <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-user"></i>
        </div>
        <span class="font-medium">My Profile</span>
    </a>
</nav>
