<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS System - FoodCo')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js for reactive UI -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/app.js'])
    @endif
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Alpine.js x-cloak support */
        [x-cloak] { display: none !important; }
        
        /* Professional Modern Sidebar - Cashier Theme */
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
            background: linear-gradient(180deg, #a78bfa, #8b5cf6);
            transform: scaleY(0);
            transition: transform 0.25s ease;
        }
        
        .nav-link:hover {
            background: rgba(139, 92, 246, 0.1);
            padding-left: 1rem;
        }
        
        .nav-link:hover::before {
            transform: scaleY(1);
        }
        
        .nav-link.active {
            background: rgba(139, 92, 246, 0.15);
            border-left: 3px solid #a78bfa;
            padding-left: calc(0.75rem - 3px);
        }
        
        .nav-icon {
            width: 2.25rem;
            height: 2.25rem;
            background: rgba(139, 92, 246, 0.1);
            transition: all 0.25s ease;
        }
        
        .nav-link:hover .nav-icon {
            background: rgba(139, 92, 246, 0.2);
            transform: scale(1.05);
        }
        
        .nav-link.active .nav-icon {
            background: rgba(139, 92, 246, 0.25);
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.3);
        }
        
        /* Logo Styling */
        .logo-icon {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
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
        
        /* Session Info Box */
        .session-info {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 0.75rem;
        }
        
        .session-info:hover {
            background: rgba(139, 92, 246, 0.15);
            border-color: rgba(139, 92, 246, 0.3);
        }
        
        /* Role Badge */
        .role-badge {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        
        /* Quick Action Buttons */
        .quick-action {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            transition: all 0.3s ease;
        }
        
        .quick-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
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
            background: rgba(139, 92, 246, 0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.5);
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
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-64 text-white z-50 flex flex-col overflow-y-auto">
        <!-- Logo Section -->
        <div class="p-6 border-b border-slate-700/50">
            <div class="flex items-center space-x-3 mb-4">
                <div class="logo-icon w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-cash-register text-xl text-white"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-white">FoodCo</h1>
                    <p class="text-xs text-slate-400">POS System</p>
                </div>
            </div>
            <div class="role-badge inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white w-full justify-center">
                <i class="fas fa-user-check mr-2 text-sm"></i>
                <span>Cashier</span>
            </div>
        </div>
        
        <!-- Session Info -->
        @php
            $currentSession = auth()->user()->currentPosSession();
        @endphp
        <div class="px-4 pt-4">
            <div class="session-info p-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2 mb-1">
                            @if($currentSession)
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <p class="text-xs font-semibold text-slate-200">Session Active</p>
                            @else
                                <div class="w-2 h-2 bg-slate-500 rounded-full"></div>
                                <p class="text-xs font-semibold text-slate-400">No Session</p>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->branch->name ?? 'Main Branch' }}</p>
                    </div>
                    <div class="text-right">
                        @if($currentSession)
                            <p class="text-xs text-slate-400">Sales</p>
                            <p class="text-sm font-bold text-white">₹{{ number_format($currentSession->orders()->sum('total_amount'), 2) }}</p>
                        @else
                            <button onclick="startSession()" class="text-xs bg-violet-600 hover:bg-violet-700 text-white px-2.5 py-1 rounded-md transition-colors">
                                Start
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex-1">
            @include('partials.navigation.cashier')
        </div>
        
        <!-- User Profile -->
        <div class="mt-auto p-4 border-t border-slate-700/50 bg-slate-900/50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Cashier' }}</p>
                    <p class="text-xs text-slate-400">Cashier</p>
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
    <div class="main-content ml-0 lg:ml-64">
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
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-cash-register text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">@yield('title', 'POS Dashboard')</h1>
                            <p class="text-sm text-gray-500">Point of Sale System</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Session Status -->
                    <div class="hidden sm:flex items-center space-x-2 px-4 py-2 rounded-lg border {{ $currentSession ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                        @if($currentSession)
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-green-700">Session Active</span>
                        @else
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            <span class="text-sm font-medium text-red-700">No Session</span>
                        @endif
                    </div>

                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notifications-button" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
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
                    
                    <!-- Quick Sale Button -->
                    <a href="{{ route('billing.quickSale') }}" class="quick-action px-4 py-2 text-white rounded-lg text-sm font-medium">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Sale
                    </a>
                    
                    <!-- Current Time -->
                    <div class="hidden sm:flex items-center space-x-2 bg-white px-4 py-2 rounded-lg border shadow-sm">
                        <i class="fas fa-clock text-purple-500"></i>
                        <span class="text-sm font-medium text-gray-700" id="current-time">{{ now('Asia/Kolkata')->format('H:i') }}</span>
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

        // Update current time in IST every minute
        function updateTime() {
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                // Get current time in IST (UTC+5:30)
                const now = new Date();
                const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
                const ist = new Date(utc + (5.5 * 3600000)); // IST is UTC+5:30
                
                const hours = String(ist.getHours()).padStart(2, '0');
                const minutes = String(ist.getMinutes()).padStart(2, '0');
                timeElement.textContent = `${hours}:${minutes}`;
            }
        }
        
        updateTime(); // Update immediately
        setInterval(updateTime, 60000); // Update every minute

        function startSession() {
            // Add AJAX call to start POS session
            fetch('/api/pos/session/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to start session: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to start session');
            });
        }
    </script>
</body>
</html>