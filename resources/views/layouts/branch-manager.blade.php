<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Branch Manager - FoodCo Management')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Branch Manager Theme - Green & Teal */
        .sidebar {
            background: linear-gradient(180deg, #065f46 0%, #047857 50%, #059669 100%);
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
        }

        .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            background: rgba(16, 185, 129, 0.15);
            transform: translateX(8px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
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
            background: linear-gradient(135deg, #10b981, #059669);
            animation: logoFloat 6s ease-in-out infinite;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }

        @keyframes logoFloat {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        /* Main Content */
        .main-content {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
        }

        .top-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-bottom: 2px solid rgba(16, 185, 129, 0.2);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Branch Manager specific styles */
        .branch-manager-card {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
            border: 2px solid #10b981;
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.2);
        }

        .branch-manager-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* Branch Info Box */
        .branch-info {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #10b981;
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

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
            /* Hide scrollbar by default for WebKit browsers */
        }

        .scrollbar-auto::-webkit-scrollbar {
            display: block;
            /* Show scrollbar on hover */
        }

        /* For Firefox */
        .scrollbar-hide {
            scrollbar-width: none;
            /* Hide scrollbar by default */
        }

        .scrollbar-auto {
            scrollbar-width: auto;
            /* Show scrollbar on hover */
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-1000 hidden lg:hidden" onclick="toggleMobileMenu()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-80 text-white z-50 flex flex-col">
        <!-- Logo Section -->
        <div class="p-5 text-center border-b border-white/20">
            <div class="logo-icon w-5 h-5 pt-5 mx-auto mb-4 rounded-2xl flex items-center justify-center">
                <i class="fas fa-store text-2xl text-white"></i>
            </div>
            <div class="flex justify-center items-baseline gap-1">
                <h1 class="text-2xl font-bold text-white">FoodCo</h1>
                <p class="text-sm text-green-100">Branch Manager</p>
            </div>
            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs branch-manager-badge">
                <i class="fas fa-user-tie mr-1"></i>
                {{ auth()->user()->branch->name ?? 'Branch Manager' }}
            </div>
        </div>

        <!-- Branch Info -->
        @if(auth()->user()->branch)
        <div class="mx-6 mt-4 p-4 rounded-lg branch-info">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-green-800">{{ auth()->user()->branch->name }}</p>
                    <p class="text-xs text-green-600">{{ auth()->user()->branch->code }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-green-600">Today's Sales</p>
                    <p class="text-sm font-bold text-green-800">₹{{ number_format(auth()->user()->branch->todaySales(), 2) }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Navigation -->
        <div class="flex-1 min-h-0 overflow-y-auto scrollbar-hide hover:scrollbar-auto">
            @include('partials.navigation.branch-manager')
        </div>

        <!-- User Profile -->
        <div class="mt-auto p-3 border-t border-white/20 bg-black/10">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500  rounded-xl flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'BM', 0, 2)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Branch Manager' }}</p>
                    <p class="text-xs text-green-200">Branch Manager</p>
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
                        <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-store text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">@yield('title', 'Branch Dashboard')</h1>
                            <p class="text-sm text-gray-500">Manage your branch operations</p>
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Branch Status -->
                    <div class="hidden sm:flex items-center space-x-2 bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                        @if(auth()->user()->branch && auth()->user()->branch->isOpen())
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-green-700">Branch Open</span>
                        @else
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <span class="text-sm font-medium text-red-700">Branch Closed</span>
                        @endif
                    </div>

                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- Current Date -->
                    <div class="hidden sm:flex items-center space-x-2 bg-white px-4 py-2 rounded-lg border shadow-sm">
                        <i class="fas fa-calendar-day text-green-500"></i>
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