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
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/app.js'])
    @endif
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Professional Modern Sidebar - Branch Manager Theme */
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.3);
        }

        .nav-link {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: linear-gradient(180deg, #34d399, #10b981);
            transform: scaleY(0);
            transition: transform 0.25s ease;
        }

        .nav-link:hover {
            background: rgba(16, 185, 129, 0.1);
            padding-left: 1rem;
        }

        .nav-link:hover::before {
            transform: scaleY(1);
        }

        .nav-link.active {
            background: rgba(16, 185, 129, 0.15);
            border-left: 3px solid #34d399;
            padding-left: calc(0.75rem - 3px);
        }

        .nav-icon {
            width: 2.25rem;
            height: 2.25rem;
            background: rgba(16, 185, 129, 0.1);
            transition: all 0.25s ease;
        }

        .nav-link:hover .nav-icon {
            background: rgba(16, 185, 129, 0.2);
            transform: scale(1.05);
        }

        .nav-link.active .nav-icon {
            background: rgba(16, 185, 129, 0.25);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
        }

        /* Logo Styling */
        .logo-icon {
            background: linear-gradient(135deg, #34d399, #10b981);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        /* Main Content */
        .main-content {
            background: #f8fafc;
            min-height: 100vh;
        }

        .top-nav {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Branch Info Box */
        .branch-info {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 0.75rem;
        }

        .branch-info:hover {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
        }

        /* Role Badge */
        .role-badge {
            background: linear-gradient(135deg, #34d399, #10b981);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Section Dividers */
        .section-divider {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            padding: 0 0.75rem;
        }

        /* Scrollbar Styling - Hidden by default, show on hover */
        .sidebar {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
            opacity: 0;
        }
        
        .sidebar:hover::-webkit-scrollbar {
            opacity: 1;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.5);
        }
        
        /* Show scrollbar on hover for webkit browsers */
        .sidebar:hover {
            scrollbar-width: thin; /* Firefox */
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-72 text-white z-50 flex flex-col overflow-y-auto">
        <!-- Logo Section -->
        <div class="p-6 border-b border-slate-700/50">
            <div class="flex items-center space-x-3 mb-4">
                <div class="logo-icon w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-store text-xl text-white"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-white">FoodCo</h1>
                    <p class="text-xs text-slate-400">Branch Manager</p>
                </div>
            </div>
            <div class="role-badge inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white w-full justify-center">
                <i class="fas fa-user-tie mr-2 text-sm"></i>
                <span>Branch Manager</span>
            </div>
        </div>

        <!-- Branch Info -->
        @if(auth()->user()->branch)
        <div class="px-4 pt-4">
            <div class="branch-info p-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-200 truncate">{{ auth()->user()->branch->name }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()->branch->code }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-400">Today's Sales</p>
                        <p class="text-sm font-bold text-white">₹{{ number_format(auth()->user()->branch->todaySales(), 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Navigation -->
        <div class="flex-1">
            @include('partials.navigation.branch-manager')
        </div>

        <!-- User Profile -->
        <div class="mt-auto p-4 border-t border-slate-700/50 bg-slate-900/50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr(auth()->user()->name ?? 'BM', 0, 2)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Branch Manager' }}</p>
                    <p class="text-xs text-slate-400">Branch Manager</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-0 lg:ml-72">
        <!-- Top Navigation -->
        <div class="top-nav sticky top-0 z-40">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
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
                    <div class="relative">
                        <button id="notifications-button" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-bell text-lg"></i>
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
                    <div class="hidden sm:flex items-center space-x-2 bg-white px-4 py-2 rounded-lg border shadow-sm">
                        <i class="fas fa-calendar-day text-green-500"></i>
                        <span class="text-sm font-medium text-gray-700">{{ now()->format('M d, Y') }}</span>
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