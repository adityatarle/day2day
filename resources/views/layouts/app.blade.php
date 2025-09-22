<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Day2Day Fresh')</title>
    
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
        
        /* Mobile-first responsive enhancements */
        @media (max-width: 640px) {
            .metric-card {
                padding: 1rem;
                margin-bottom: 0.75rem;
            }
            
            .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
                min-height: 44px;
                min-width: 44px;
            }
            
            .form-input {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
                min-height: 44px;
            }
            
            .card {
                padding: 1rem;
                margin-bottom: 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .metric-card {
                padding: 1.25rem;
            }
            
            .grid {
                gap: 1rem;
            }
        }

        /* Touch-friendly elements */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }
        
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
                width: 100%;
                max-width: 320px;
                height: 100vh;
                overflow-y: auto;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 0.5rem;
            }
            
            .nav-link {
                padding: 0.75rem 1rem;
                margin: 0.25rem 0.5rem;
                font-size: 0.9rem;
            }
            
            .nav-icon {
                width: 2rem;
                height: 2rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .top-nav {
                padding: 0.75rem 1rem;
            }
            
            .top-nav .flex {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .card {
                margin-bottom: 1rem;
                border-radius: 0.75rem;
            }
            
            .data-table {
                font-size: 0.875rem;
                min-width: 600px;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.5rem 0.75rem;
                white-space: nowrap;
            }
            
            /* Table wrapper for horizontal scroll */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Mobile-friendly buttons */
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn-group .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .top-nav {
                padding: 0.75rem;
            }
            
            .card {
                border-radius: 0.75rem;
                padding: 1rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
            
            .form-input {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }
            
            .data-table {
                font-size: 0.75rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.375rem 0.5rem;
            }
            
            /* Mobile navigation improvements */
            .nav-link span {
                font-size: 0.85rem;
            }
            
            /* Mobile grid improvements */
            .grid {
                gap: 0.75rem;
            }
            
            /* Mobile form improvements */
            .form-label {
                font-size: 0.875rem;
                margin-bottom: 0.375rem;
            }
            
            /* Mobile typography */
            h1 { font-size: 1.5rem; }
            h2 { font-size: 1.25rem; }
            h3 { font-size: 1.125rem; }
            
            /* Mobile spacing */
            .space-y-4 > * + * { margin-top: 0.75rem; }
            .space-y-6 > * + * { margin-top: 1rem; }
            .space-y-8 > * + * { margin-top: 1.5rem; }
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
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-1000 hidden lg:hidden" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-80 text-white flex flex-col">
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
        <div class="flex-1 min-h-0 overflow-y-auto">
            @if(auth()->check() && auth()->user()->hasRole('branch_manager'))
                @include('partials.navigation.branch-manager')
            @elseif(auth()->check() && auth()->user()->hasRole('cashier'))
                @include('partials.navigation.cashier')
            @else
                @include('partials.navigation.super-admin')
            @endif
        </div>
        
        <!-- User Profile -->
        <div class="mt-auto p-6 border-t border-white/20 bg-black/30">
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
        <div class="top-nav sticky top-0 z-50">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
                <!-- Mobile Menu Button -->
                <button type="button" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Page Title -->
                <div class="flex-1 px-2 sm:px-0">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-crown text-white text-sm sm:text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 truncate">@yield('title', 'Super Admin Dashboard')</h1>
                            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Complete system control and management</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Actions -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- System Status -->
                    <div class="hidden md:flex items-center space-x-2 bg-green-50 px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg border border-green-200">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs sm:text-sm font-medium text-green-700">System Online</span>
                    </div>
                    
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-bell text-base sm:text-lg"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Current Date -->
                    <div class="hidden lg:flex items-center space-x-2 bg-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg border shadow-sm">
                        <i class="fas fa-calendar-day text-amber-500 text-sm"></i>
                        <span class="text-xs sm:text-sm font-medium text-gray-700">{{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8 fade-in">
            @yield('content')
        </main>
    </div>
    
    <!-- JavaScript -->
    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('hidden');
            
            // Prevent body scroll when menu is open
            if (sidebar.classList.contains('mobile-open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            const menuButton = event.target.closest('[onclick="toggleMobileMenu()"]');
            
            if (!menuButton && !sidebar.contains(event.target) && window.innerWidth < 1024) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.add('hidden');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.add('hidden');
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
            
            // Add touch-friendly interactions
            const buttons = document.querySelectorAll('button, .btn, a[class*="btn"]');
            buttons.forEach(button => {
                button.classList.add('touch-target');
            });
            
            // Mobile responsive breakpoint detection
            function checkBreakpoint() {
                const width = window.innerWidth;
                document.body.setAttribute('data-screen-size', 
                    width < 640 ? 'mobile' : 
                    width < 768 ? 'small-tablet' : 
                    width < 1024 ? 'tablet' : 'desktop'
                );
            }
            
            checkBreakpoint();
            window.addEventListener('resize', checkBreakpoint);
        });
    </script>
</body>
</html>