<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS System - FoodCo')</title>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Cashier Theme - Purple & Indigo (POS System) */
        .sidebar {
            background: linear-gradient(180deg, #4c1d95 0%, #5b21b6 50%, #6d28d9 100%);
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            background: rgba(139, 92, 246, 0.15);
            transform: translateX(8px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
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
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            animation: logoFloat 6s ease-in-out infinite;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        
        /* Main Content */
        .main-content {
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            min-height: 100vh;
        }
        
        .top-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-bottom: 2px solid rgba(139, 92, 246, 0.2);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Cashier specific styles */
        .cashier-card {
            background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);
            border: 2px solid #8b5cf6;
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.2);
        }
        
        .cashier-badge {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        /* POS Session Info */
        .session-info {
            background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
            border: 1px solid #8b5cf6;
        }
        
        /* Quick Action Buttons */
        .quick-action {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            transition: all 0.3s ease;
            transform: scale(1);
        }
        
        .quick-action:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
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
                <i class="fas fa-cash-register text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">FoodCo</h1>
            <p class="text-sm text-purple-100">POS System</p>
            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs cashier-badge">
                <i class="fas fa-user-check mr-1"></i>
                Cashier
            </div>
        </div>
        
        <!-- Session Info -->
        @php
            $currentSession = auth()->user()->currentPosSession();
        @endphp
        <div class="mx-6 mt-4 p-4 rounded-lg session-info">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-purple-800">
                        @if($currentSession)
                            Session Active
                        @else
                            No Active Session
                        @endif
                    </p>
                    <p class="text-xs text-purple-600">{{ auth()->user()->branch->name ?? 'Branch' }}</p>
                </div>
                <div class="text-right">
                    @if($currentSession)
                        <p class="text-xs text-purple-600">Session Sales</p>
                        <p class="text-sm font-bold text-purple-800">₹{{ number_format($currentSession->orders()->sum('total_amount'), 2) }}</p>
                    @else
                        <button onclick="startSession()" class="text-xs bg-purple-600 text-white px-3 py-1 rounded-full hover:bg-purple-700 transition-colors">
                            Start Session
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto pb-24">
            @include('partials.navigation.cashier')
        </div>
        
        <!-- User Profile -->
        <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-white/20 bg-black/30">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Cashier' }}</p>
                    <p class="text-xs text-purple-200">Cashier</p>
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
                    
                    <!-- Quick Sale Button -->
                    <a href="{{ route('billing.quickSale') }}" class="quick-action px-4 py-2 text-white rounded-lg text-sm font-medium">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Sale
                    </a>
                    
                    <!-- Current Time -->
                    <div class="hidden sm:flex items-center space-x-2 bg-white px-4 py-2 rounded-lg border shadow-sm">
                        <i class="fas fa-clock text-purple-500"></i>
                        <span class="text-sm font-medium text-gray-700" id="current-time">{{ now()->format('H:i') }}</span>
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

        // Update current time every minute
        function updateTime() {
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                const now = new Date();
                timeElement.textContent = now.toLocaleTimeString('en-US', { 
                    hour12: false, 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            }
        }
        
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