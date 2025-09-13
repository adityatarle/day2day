<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Food Company Management')</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/app.js'])
    @endif
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateX(8px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
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
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        
        /* Main Content */
        .main-content {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .top-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-4px);
        }
        
        .metric-card {
            position: relative;
            overflow: hidden;
        }
        
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .metric-card:hover::before {
            opacity: 1;
        }
        
        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Form Styles */
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        /* Status Badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .badge-pending { background: #dbeafe; color: #1e40af; }
        .badge-processing { background: #fef3c7; color: #92400e; }
        .badge-completed { background: #dcfce7; color: #166534; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        
        /* Category Badges */
        .badge-fruit { background: #ffedd5; color: #9a3412; }
        .badge-vegetable { background: #dcfce7; color: #166534; }
        .badge-leafy { background: #d1fae5; color: #065f46; }
        .badge-exotic { background: #f3e8ff; color: #6b21a8; }
        
        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .data-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
        }
        
        .data-table tbody tr:hover {
            background: #f9fafb;
        }
        
        /* Alert Styles */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid;
        }
        
        .alert-success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
        .alert-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .alert-info { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
        
        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            border-top-color: #3b82f6;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Page Animations */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
            
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                display: none;
            }
            
            .mobile-overlay.active {
                display: block;
            }
        }
    </style>
    @if(auth()->check() && auth()->user()->isBranchManager())
    <style>
        /* Branch Manager Theme Overrides */
        .sidebar {
            background: linear-gradient(180deg, #065f46 0%, #047857 50%, #059669 100%);
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
        }
        .main-content { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); }
        .top-nav { border-bottom: 2px solid rgba(16, 185, 129, 0.2); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); }
        .logo-icon { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4); }
        .nav-link:hover { background: rgba(16, 185, 129, 0.15); }
        .nav-link.active { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4); }
    </style>
    @endif
</head>
<body class="bg-gray-50">
    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="mobile-overlay" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-72 text-white flex flex-col">
        <!-- Logo Section -->
        <div class="p-6 text-center border-b border-white/10 flex-shrink-0">
            <div class="logo-icon w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center">
                <i class="fas fa-leaf text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">FoodCo</h1>
            <p class="text-sm text-gray-300">Management System</p>
        </div>
        
        <!-- Navigation -->
        @php $user = auth()->user(); @endphp
        <div class="flex-1 overflow-y-auto">
        @if($user && $user->isBranchManager())
            @include('partials.navigation.branch-manager')
        @elseif($user && $user->isCashier())
            @include('partials.navigation.cashier')
        @elseif($user && $user->isSuperAdmin())
            @include('partials.navigation.super-admin')
        @else
        <nav class="p-6 space-y-2">
            <a href="{{ route('dashboard') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('dashboard') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="{{ route('products.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('products.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-box"></i>
                </div>
                <span class="font-medium">Products</span>
            </a>
            
            <a href="{{ route('orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('orders.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="font-medium">Orders</span>
            </a>
            
            <a href="{{ route('inventory.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('inventory.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-warehouse"></i>
                </div>
                <span class="font-medium">Inventory</span>
            </a>
            
            <a href="{{ route('customers.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('customers.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-users"></i>
                </div>
                <span class="font-medium">Customers</span>
            </a>
            
            <a href="{{ route('vendors.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('vendors.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-building"></i>
                </div>
                <span class="font-medium">Vendors</span>
            </a>
            
            <a href="{{ route('purchase-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('purchase-orders.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <span class="font-medium">Purchase Orders</span>
            </a>

            @if($user && $user->isAdmin())
            <a href="{{ route('admin.branch-orders.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('admin.branch-orders.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-store"></i>
                </div>
                <span class="font-medium">Orders from Branches</span>
            </a>
            @endif
            
            <a href="{{ route('reports.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('reports.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <span class="font-medium">Reports</span>
            </a>

            @if($user && ($user->isAdmin() || $user->isBranchManager()))
            <a href="{{ route('outlets.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('outlets.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-store"></i>
                </div>
                <span class="font-medium">Outlets</span>
            </a>
            @endif

            @if($user && ($user->isAdmin() || $user->isBranchManager() || $user->isCashier()))
            <a href="{{ route('pos.index') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('pos.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-cash-register"></i>
                </div>
                <span class="font-medium">POS System</span>
            </a>
            @endif
            
            <a href="{{ route('billing.quickSale') }}" class="nav-link flex items-center p-3 rounded-xl text-gray-300 {{ request()->routeIs('billing.*') ? 'active text-white' : '' }}">
                <div class="nav-icon rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <span class="font-medium">Quick Sale</span>
            </a>
        </nav>
        @endif
        </div>
        
        <!-- User Profile -->
        <div class="flex-shrink-0 p-6 border-t border-white/10 bg-black/20">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    @php
                        $roleLabel = 'User';
                        if (auth()->check()) {
                            $u = auth()->user();
                            if ($u->isSuperAdmin()) { $roleLabel = 'Super Admin'; }
                            elseif ($u->isAdmin()) { $roleLabel = 'Admin'; }
                            elseif ($u->isBranchManager()) { $roleLabel = 'Branch Manager'; }
                            elseif ($u->isCashier()) { $roleLabel = 'Cashier'; }
                            elseif ($u->isDeliveryBoy()) { $roleLabel = 'Delivery Boy'; }
                        }
                    @endphp
                    <p class="text-xs text-gray-300">{{ $roleLabel }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content ml-72">
        <!-- Top Navigation -->
        <div class="top-nav sticky top-0 z-50">
            <div class="flex items-center justify-between px-8 py-4">
                <!-- Mobile Menu Button -->
                <button type="button" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Page Title -->
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tachometer-alt text-white text-sm"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>
                
                <!-- Right Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Current Date -->
                    <div class="hidden sm:flex items-center space-x-2 bg-white px-4 py-2 rounded-lg border shadow-sm">
                        <i class="fas fa-calendar-alt text-gray-500"></i>
                        <span class="text-sm font-medium text-gray-700">{{ now()->format('M d, Y') }}</span>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex items-center space-x-3 bg-white px-4 py-2 rounded-lg border shadow-sm">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <span class="hidden md:block text-sm font-medium text-gray-700">{{ auth()->user()->name ?? 'User' }}</span>
                    </div>
                    
                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-sign-out-alt text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <main class="p-8 fade-in">
            @yield('content')
        </main>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            const menuButton = event.target.closest('[onclick="toggleMobileMenu()"]');
            
            if (!menuButton && !sidebar.contains(event.target) && window.innerWidth < 1024) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            }
        });
        
        // Form validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            const inputs = form.querySelectorAll('.form-input[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#ef4444';
                    input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                    isValid = false;
                } else {
                    input.style.borderColor = '#e5e7eb';
                    input.style.boxShadow = 'none';
                }
            });
            
            return isValid;
        }
        
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
            
            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.card, .metric-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>