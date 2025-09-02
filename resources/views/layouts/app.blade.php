<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Food Company Management') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-64 bg-gradient-to-b from-blue-800 to-blue-900">
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 px-4 bg-blue-900">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-leaf text-white text-xl"></i>
                        </div>
                        <span class="text-white text-xl font-bold">FoodCo</span>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white shadow-lg' : 'hover:bg-blue-700 hover:text-white' }}">
                        <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('products.index') }}" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-blue-700 text-white shadow-lg' : 'hover:bg-blue-700 hover:text-white' }}">
                        <i class="fas fa-box w-5 h-5 mr-3"></i>
                        <span class="font-medium">Products</span>
                    </a>

                    <a href="{{ route('orders.index') }}" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 {{ request()->routeIs('orders.*') ? 'bg-blue-700 text-white shadow-lg' : 'hover:bg-blue-700 hover:text-white' }}">
                        <i class="fas fa-shopping-cart w-5 h-5 mr-3"></i>
                        <span class="font-medium">Orders</span>
                    </a>

                    <a href="{{ route('inventory.index') }}" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 {{ request()->routeIs('inventory.*') ? 'bg-blue-700 text-white shadow-lg' : 'hover:bg-blue-700 hover:text-white' }}">
                        <i class="fas fa-warehouse w-5 h-5 mr-3"></i>
                        <span class="font-medium">Inventory</span>
                    </a>

                    <a href="{{ route('customers.index') }}" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 {{ request()->routeIs('customers.*') ? 'bg-blue-700 text-white shadow-lg' : 'hover:bg-blue-700 hover:text-white' }}">
                        <i class="fas fa-users w-5 h-5 mr-3"></i>
                        <span class="font-medium">Customers</span>
                    </a>

                    <div class="relative">
                        <button onclick="toggleSubmenu('vendors-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 hover:bg-blue-700 hover:text-white">
                            <div class="flex items-center">
                                <i class="fas fa-building w-5 h-5 mr-3"></i>
                                <span class="font-medium">Vendors & Purchases</span>
                            </div>
                            <i class="fas fa-chevron-down w-4 h-4 transition-transform duration-200" id="vendors-chevron"></i>
                        </button>
                        <div id="vendors-submenu" class="hidden ml-4 mt-2 space-y-1">
                            <a href="{{ route('vendors.index') }}" class="flex items-center px-4 py-2 text-blue-200 rounded-lg transition-all duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('vendors.*') ? 'bg-blue-700 text-white' : '' }}">
                                <i class="fas fa-user-tie w-4 h-4 mr-3"></i>
                                <span class="text-sm">Vendors</span>
                            </a>
                            <a href="{{ route('purchase-orders.index') }}" class="flex items-center px-4 py-2 text-blue-200 rounded-lg transition-all duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('purchase-orders.*') ? 'bg-blue-700 text-white' : '' }}">
                                <i class="fas fa-file-invoice w-4 h-4 mr-3"></i>
                                <span class="text-sm">Purchase Orders</span>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-blue-700 text-white shadow-lg' : 'hover:bg-blue-700 hover:text-white' }}">
                        <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                        <span class="font-medium">Reports</span>
                    </a>

                    <a href="{{ route('billing.quickSale') }}" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 {{ request()->routeIs('billing.*') ? 'bg-blue-700 text-white shadow-lg' : 'hover:bg-blue-700 hover:text-white' }}">
                        <i class="fas fa-plus-circle w-5 h-5 mr-3"></i>
                        <span class="font-medium">Quick Sale</span>
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-code-branch w-5 h-5 mr-3"></i>
                        <span class="font-medium">Branches</span>
                    </a>
                </nav>

                <!-- User Profile -->
                <div class="p-4 border-t border-blue-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-lg">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-xs text-blue-200 truncate">System Administrator</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Navigation -->
            <div class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button type="button" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars w-6 h-6"></i>
                    </button>

                    <!-- Page title -->
                    <div class="flex-1 px-4 lg:px-0">
                        <h1 class="text-2xl font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-bell w-5 h-5"></i>
                        </button>

                        <!-- Date -->
                        <div class="hidden sm:block text-sm text-gray-500">
                            {{ Carbon\Carbon::now()->format('M d, Y') }}
                        </div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                                <i class="fas fa-sign-out-alt w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu" class="fixed inset-0 z-50 lg:hidden hidden">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="toggleMobileMenu()"></div>
        <div class="fixed inset-y-0 left-0 flex flex-col w-64 bg-gradient-to-b from-blue-800 to-blue-900">
            <!-- Mobile menu content -->
            <div class="flex items-center justify-center h-16 px-4 bg-blue-900">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-leaf text-white text-xl"></i>
                    </div>
                    <span class="text-white text-xl font-bold">FoodCo</span>
                </div>
            </div>
            
            <!-- Mobile navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-blue-100 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white shadow-lg' : 'hover:bg-blue-700 hover:text-white' }}">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <!-- Add other mobile menu items here -->
            </nav>
        </div>
    </div>

    <script>
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            const chevron = document.getElementById(id.replace('-submenu', '-chevron'));
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        }
    </script>
</body>
</html>