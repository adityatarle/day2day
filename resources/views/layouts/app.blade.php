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
            <div class="flex flex-col w-72 bg-gradient-to-b from-slate-900 via-blue-900 to-indigo-900 shadow-2xl">
                <!-- Logo -->
                <div class="flex items-center justify-center h-20 px-6 bg-gradient-to-r from-blue-900 to-indigo-900 border-b border-blue-700/50">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg float-animation">
                            <i class="fas fa-leaf text-white text-2xl"></i>
                        </div>
                        <div>
                            <span class="text-white text-2xl font-bold bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">FoodCo</span>
                            <p class="text-blue-200 text-xs font-medium">Management System</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-6 py-8 space-y-3 overflow-y-auto custom-scrollbar">
                    <a href="{{ route('dashboard') }}" class="group flex items-center px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xl border border-blue-500/50' : 'hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 {{ request()->routeIs('dashboard') ? 'bg-white/20' : 'bg-blue-700/30 group-hover:bg-blue-600/50' }} transition-all duration-300">
                            <i class="fas fa-tachometer-alt text-lg"></i>
                        </div>
                        <span class="font-semibold text-lg">Dashboard</span>
                    </a>

                    <a href="{{ route('products.index') }}" class="group flex items-center px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 {{ request()->routeIs('products.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xl border border-blue-500/50' : 'hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 {{ request()->routeIs('products.*') ? 'bg-white/20' : 'bg-blue-700/30 group-hover:bg-blue-600/50' }} transition-all duration-300">
                            <i class="fas fa-box text-lg"></i>
                        </div>
                        <span class="font-semibold text-lg">Products</span>
                    </a>

                    <a href="{{ route('orders.index') }}" class="group flex items-center px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 {{ request()->routeIs('orders.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xl border border-blue-500/50' : 'hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 {{ request()->routeIs('orders.*') ? 'bg-white/20' : 'bg-blue-700/30 group-hover:bg-blue-600/50' }} transition-all duration-300">
                            <i class="fas fa-shopping-cart text-lg"></i>
                        </div>
                        <span class="font-semibold text-lg">Orders</span>
                    </a>

                    <a href="{{ route('inventory.index') }}" class="group flex items-center px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 {{ request()->routeIs('inventory.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xl border border-blue-500/50' : 'hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 {{ request()->routeIs('inventory.*') ? 'bg-white/20' : 'bg-blue-700/30 group-hover:bg-blue-600/50' }} transition-all duration-300">
                            <i class="fas fa-warehouse text-lg"></i>
                        </div>
                        <span class="font-semibold text-lg">Inventory</span>
                    </a>

                    <a href="{{ route('customers.index') }}" class="group flex items-center px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 {{ request()->routeIs('customers.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xl border border-blue-500/50' : 'hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 {{ request()->routeIs('customers.*') ? 'bg-white/20' : 'bg-blue-700/30 group-hover:bg-blue-600/50' }} transition-all duration-300">
                            <i class="fas fa-users text-lg"></i>
                        </div>
                        <span class="font-semibold text-lg">Customers</span>
                    </a>

                    <div class="relative">
                        <button onclick="toggleSubmenu('vendors-submenu')" class="group w-full flex items-center justify-between px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 bg-blue-700/30 group-hover:bg-blue-600/50 transition-all duration-300">
                                    <i class="fas fa-building text-lg"></i>
                                </div>
                                <span class="font-semibold text-lg">Vendors & Purchases</span>
                            </div>
                            <i class="fas fa-chevron-down w-4 h-4 transition-transform duration-300" id="vendors-chevron"></i>
                        </button>
                        <div id="vendors-submenu" class="hidden ml-6 mt-3 space-y-2">
                            <a href="{{ route('vendors.index') }}" class="group flex items-center px-4 py-3 text-blue-200 rounded-xl transition-all duration-300 hover:bg-blue-700/50 hover:text-white {{ request()->routeIs('vendors.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg' : '' }}">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-blue-700/30 group-hover:bg-blue-600/50 transition-all duration-300">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                                <span class="text-sm font-medium">Vendors</span>
                            </a>
                            <a href="{{ route('purchase-orders.index') }}" class="group flex items-center px-4 py-3 text-blue-200 rounded-xl transition-all duration-300 hover:bg-blue-700/50 hover:text-white {{ request()->routeIs('purchase-orders.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg' : '' }}">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-blue-700/30 group-hover:bg-blue-600/50 transition-all duration-300">
                                    <i class="fas fa-file-invoice text-sm"></i>
                                </div>
                                <span class="text-sm font-medium">Purchase Orders</span>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('reports.index') }}" class="group flex items-center px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 {{ request()->routeIs('reports.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xl border border-blue-500/50' : 'hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 {{ request()->routeIs('reports.*') ? 'bg-white/20' : 'bg-blue-700/30 group-hover:bg-blue-600/50' }} transition-all duration-300">
                            <i class="fas fa-chart-bar text-lg"></i>
                        </div>
                        <span class="font-semibold text-lg">Reports</span>
                    </a>

                    <a href="{{ route('billing.quickSale') }}" class="group flex items-center px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 {{ request()->routeIs('billing.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xl border border-blue-500/50' : 'hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 {{ request()->routeIs('billing.*') ? 'bg-white/20' : 'bg-blue-700/30 group-hover:bg-blue-600/50' }} transition-all duration-300">
                            <i class="fas fa-plus-circle text-lg"></i>
                        </div>
                        <span class="font-semibold text-lg">Quick Sale</span>
                    </a>

                    <a href="#" class="group flex items-center px-5 py-4 text-blue-100 rounded-2xl transition-all duration-300 hover:bg-blue-700/50 hover:text-white hover:shadow-lg backdrop-blur-sm">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 bg-blue-700/30 group-hover:bg-blue-600/50 transition-all duration-300">
                            <i class="fas fa-code-branch text-lg"></i>
                        </div>
                        <span class="font-semibold text-lg">Branches</span>
                    </a>
                </nav>

                <!-- User Profile -->
                <div class="p-6 border-t border-blue-700/50 bg-gradient-to-r from-blue-800/50 to-indigo-800/50 backdrop-blur-sm">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-lg">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-blue-900"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-xs text-blue-200 truncate flex items-center">
                                <i class="fas fa-crown mr-1"></i>
                                System Administrator
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Navigation -->
            <div class="bg-white/80 backdrop-blur-sm shadow-lg border-b border-gray-200/50">
                <div class="flex items-center justify-between h-20 px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button type="button" class="lg:hidden p-3 rounded-2xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all duration-300" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars w-6 h-6"></i>
                    </button>

                    <!-- Page title -->
                    <div class="flex-1 px-4 lg:px-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-tachometer-alt text-white text-sm"></i>
                            </div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-blue-600 bg-clip-text text-transparent">
                                @yield('title', 'Dashboard')
                            </h1>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-3 text-gray-400 hover:text-gray-600 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 rounded-2xl transition-all duration-300 group">
                                <i class="fas fa-bell w-5 h-5 group-hover:scale-110 transition-transform"></i>
                                <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                            </button>
                        </div>

                        <!-- Date -->
                        <div class="hidden sm:block bg-gradient-to-r from-gray-50 to-blue-50 px-4 py-2 rounded-2xl border border-gray-200">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-calendar-alt text-gray-500 text-sm"></i>
                                <span class="text-sm font-semibold text-gray-700">{{ Carbon\Carbon::now()->format('M d, Y') }}</span>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="flex items-center space-x-3 bg-gradient-to-r from-gray-50 to-blue-50 px-4 py-2 rounded-2xl border border-gray-200">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                            </div>
                            <span class="hidden md:block text-sm font-semibold text-gray-700">{{ auth()->user()->name ?? 'User' }}</span>
                        </div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="p-3 text-gray-400 hover:text-red-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 rounded-2xl transition-all duration-300 group">
                                <i class="fas fa-sign-out-alt w-5 h-5 group-hover:scale-110 transition-transform"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 custom-scrollbar">
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