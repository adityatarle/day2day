<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin - FoodCo Management')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/app.js'])
    @endif
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Super Admin Theme - Gold & Blue */
        .sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 50%, #1d4ed8 100%);
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            background: rgba(251, 191, 36, 0.15);
            transform: translateX(8px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4);
        }
        
        .nav-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .nav-link:hover .nav-icon,
        .nav-link.active .nav-icon {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
        
        /* Logo Animation */
        .logo-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            animation: logoFloat 6s ease-in-out infinite;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4);
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(5deg); }
        }
        
        /* Main Content */
        .main-content {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .top-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-bottom: 2px solid rgba(245, 158, 11, 0.2);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Super Admin specific styles */
        .super-admin-card {
            background: linear-gradient(135deg, #ffffff 0%, #fef3c7 100%);
            border: 2px solid #fbbf24;
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.2);
        }
        
        .super-admin-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                position: fixed;
                z-index: 1001;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-1000 hidden lg:hidden" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-80 text-white z-50 flex flex-col">
        <!-- Logo Section -->
        <div class="p-4 sm:p-6 text-center border-b border-white/20">
            <div class="logo-icon w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4 rounded-xl sm:rounded-2xl flex items-center justify-center">
                <i class="fas fa-crown text-xl sm:text-2xl text-white"></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">FoodCo</h1>
            <p class="text-xs sm:text-sm text-amber-100">Super Admin Panel</p>
            <div class="mt-2 sm:mt-3 inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs super-admin-badge">
                <i class="fas fa-shield-alt mr-1 text-xs"></i>
                System Administrator
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex-1 min-h-0 overflow-y-auto">
            @include('partials.navigation.super-admin')
        </div>
        
        <!-- User Profile -->
        <div class="mt-auto p-4 sm:p-6 border-t border-white/20 bg-black/30">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <span class="text-white font-bold text-sm sm:text-base">{{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                    <p class="text-xs text-amber-200">Super Administrator</p>
                </div>
                <div class="flex flex-col space-y-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-gray-300 hover:text-white transition-colors touch-target p-1">
                            <i class="fas fa-sign-out-alt text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content ml-80 lg:ml-80">
        <!-- Top Navigation -->
        <div class="top-nav sticky top-0 z-40">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
                <!-- Mobile Menu Button -->
                <button type="button" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors touch-target" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-lg sm:text-xl"></i>
                </button>
                
                <!-- Page Title -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                            <i class="fas fa-crown text-white text-sm sm:text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 truncate">@yield('title', 'Super Admin Dashboard')</h1>
                            <p class="text-xs sm:text-sm text-gray-500 truncate">Complete system control and management</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Actions -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- System Status -->
                    <div class="hidden sm:flex items-center space-x-2 bg-green-50 px-3 sm:px-4 py-2 rounded-lg border border-green-200">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs sm:text-sm font-medium text-green-700">System Online</span>
                    </div>

                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notifications-button" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors touch-target">
                            <i class="fas fa-bell text-base sm:text-lg"></i>
                            <span id="notifications-badge" class="hidden absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                        </button>
                        <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border rounded-lg shadow-lg z-50">
                            <div class="px-4 py-2 border-b flex items-center justify-between">
                                <span class="font-semibold text-gray-700 text-sm">Notifications</span>
                                <button id="notifications-mark-all" class="text-xs text-blue-600 hover:underline">Mark all as read</button>
                            </div>
                            <div id="notifications-list" class="max-h-96 overflow-auto">
                                <div class="p-4 text-sm text-gray-500">No notifications</div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Date -->
                    <div class="hidden sm:flex items-center space-x-2 bg-white px-3 sm:px-4 py-2 rounded-lg border shadow-sm">
                        <i class="fas fa-calendar-day text-amber-500 text-sm"></i>
                        <span class="text-xs sm:text-sm font-medium text-gray-700">{{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <div class="p-4 sm:p-6 lg:p-8">
            @yield('content')
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>