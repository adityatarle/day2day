<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin - FoodCo Management')</title>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-80 text-white z-50">
        <!-- Logo Section -->
        <div class="p-6 text-center border-b border-white/20">
            <div class="logo-icon w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center">
                <i class="fas fa-crown text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">FoodCo</h1>
            <p class="text-sm text-amber-100">Super Admin Panel</p>
            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs super-admin-badge">
                <i class="fas fa-shield-alt mr-1"></i>
                System Administrator
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto pb-24">
            @include('partials.navigation.super-admin')
        </div>
        
        <!-- User Profile -->
        <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-white/20 bg-black/30">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                    <p class="text-xs text-amber-200">Super Administrator</p>
                </div>
                <div class="flex flex-col space-y-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-gray-300 hover:text-white transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content ml-80">
        <!-- Top Navigation -->
        <div class="top-nav sticky top-0 z-40">
            <div class="flex items-center justify-between px-8 py-4">
                <!-- Mobile Menu Button -->
                <button type="button" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Page Title -->
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-crown text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">@yield('title', 'Super Admin Dashboard')</h1>
                            <p class="text-sm text-gray-500">Complete system control and management</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Actions -->
                <div class="flex items-center space-x-4">
                    <!-- System Status -->
                    <div class="hidden sm:flex items-center space-x-2 bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-green-700">System Online</span>
                    </div>
                    
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Current Date -->
                    <div class="hidden sm:flex items-center space-x-2 bg-white px-4 py-2 rounded-lg border shadow-sm">
                        <i class="fas fa-calendar-day text-amber-500"></i>
                        <span class="text-sm font-medium text-gray-700">{{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <div class="p-8">
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