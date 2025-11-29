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
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                min-height: 48px;
                min-width: 48px;
            }
            
            .form-input {
                padding: 0.75rem 1rem;
                font-size: 1rem;
                min-height: 48px;
            }
            
            .card {
                padding: 1rem;
                margin-bottom: 0.75rem;
                border-radius: 1rem;
            }
            
            /* Mobile-specific improvements */
            .mobile-stack {
                flex-direction: column !important;
                gap: 0.75rem !important;
            }
            
            .mobile-full-width {
                width: 100% !important;
            }
            
            .mobile-center {
                text-align: center !important;
            }
            
            .mobile-hide {
                display: none !important;
            }
            
            .mobile-show {
                display: block !important;
            }
            
            /* Improved typography for mobile */
            h1 { font-size: 1.75rem; line-height: 1.2; }
            h2 { font-size: 1.5rem; line-height: 1.3; }
            h3 { font-size: 1.25rem; line-height: 1.4; }
            h4 { font-size: 1.125rem; line-height: 1.4; }
            
            /* Better spacing for mobile */
            .space-y-8 > * + * { margin-top: 1rem; }
            .space-y-6 > * + * { margin-top: 0.875rem; }
            .space-y-4 > * + * { margin-top: 0.75rem; }
            .space-y-3 > * + * { margin-top: 0.625rem; }
            .space-y-2 > * + * { margin-top: 0.5rem; }
        }

        @media (max-width: 768px) {
            .metric-card {
                padding: 1.25rem;
            }
            
            .grid {
                gap: 1rem;
            }
            
            /* Tablet-specific adjustments */
            .tablet-hide {
                display: none !important;
            }
            
            .tablet-show {
                display: block !important;
            }
        }

        /* Touch-friendly elements */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Professional Modern Sidebar */
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
            background: linear-gradient(180deg, #fbbf24, #f59e0b);
            transform: scaleY(0);
            transition: transform 0.25s ease;
        }
        
        .nav-link:hover {
            background: rgba(251, 191, 36, 0.1);
            padding-left: 1rem;
        }
        
        .nav-link:hover::before {
            transform: scaleY(1);
        }
        
        .nav-link.active {
            background: rgba(251, 191, 36, 0.15);
            border-left: 3px solid #fbbf24;
            padding-left: calc(0.75rem - 3px);
        }
        
        .nav-icon {
            width: 2.25rem;
            height: 2.25rem;
            background: rgba(251, 191, 36, 0.1);
            transition: all 0.25s ease;
        }
        
        .nav-link:hover .nav-icon {
            background: rgba(251, 191, 36, 0.2);
            transform: scale(1.05);
        }
        
        .nav-link.active .nav-icon {
            background: rgba(251, 191, 36, 0.25);
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
        }
        
        /* Logo Styling */
        .logo-icon {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.4);
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
        
        /* Role Badge */
        .role-badge {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
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
            background: rgba(251, 191, 36, 0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(251, 191, 36, 0.5);
        }
        
        /* Show scrollbar on hover for webkit browsers */
        .sidebar:hover {
            scrollbar-width: thin; /* Firefox */
        }

        /* Enhanced Mobile Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: fixed;
                z-index: 1001;
                width: 100vw;
                max-width: 320px;
                height: 100vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 0.5rem;
                width: 100% !important;
            }
            
            .nav-link {
                padding: 1rem 1.25rem;
                margin: 0.375rem 0.75rem;
                font-size: 0.95rem;
                border-radius: 0.75rem;
                min-height: 48px;
                display: flex;
                align-items: center;
            }
            
            .nav-icon {
                width: 2.25rem;
                height: 2.25rem;
                margin-right: 0.875rem;
            }
            
            /* Mobile overlay improvements */
            #mobile-overlay {
                backdrop-filter: blur(8px);
                background: rgba(0, 0, 0, 0.6);
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
            
            /* Mobile-first table improvements */
            .mobile-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            
            .mobile-table table {
                width: 100%;
                min-width: 600px;
            }
            
            /* Enhanced Table wrapper for horizontal scroll */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 0.75rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
            
            .table-container::-webkit-scrollbar {
                height: 8px;
            }
            
            .table-container::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 4px;
            }
            
            .table-container::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
            
            .table-container::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
            
            /* Mobile-friendly buttons */
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .btn-group .btn {
                width: 100%;
                justify-content: center;
                min-height: 48px;
            }
            
            /* Mobile card stack layout */
            .mobile-card-list .card {
                border-left: 4px solid #3b82f6;
                margin-bottom: 1rem;
            }
            
            .mobile-card-list .card:last-child {
                margin-bottom: 0;
            }
        }

        @media (max-width: 640px) {
            .main-content {
                padding: 0.25rem;
            }
            
            /* Force full width on mobile */
            .container {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            /* Ensure proper mobile layout */
            .overflow-x-auto {
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
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
        .nav-link::before {
            background: linear-gradient(180deg, #34d399, #10b981);
        }
        .nav-link.active {
            border-left-color: #34d399;
        }
        .nav-icon {
            background: rgba(16, 185, 129, 0.1);
        }
        .nav-link:hover .nav-icon {
            background: rgba(16, 185, 129, 0.2);
        }
        .nav-link.active .nav-icon {
            background: rgba(16, 185, 129, 0.25);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
        }
        .logo-icon { 
            background: linear-gradient(135deg, #34d399, #10b981); 
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4); 
        }
        .role-badge { 
            background: linear-gradient(135deg, #34d399, #10b981);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
    </style>
    @elseif(auth()->check() && auth()->user()->isCashier())
    <style>
        /* Cashier Theme Overrides */
        .nav-link::before {
            background: linear-gradient(180deg, #a78bfa, #8b5cf6);
        }
        .nav-link.active {
            border-left-color: #a78bfa;
        }
        .nav-icon {
            background: rgba(139, 92, 246, 0.1);
        }
        .nav-link:hover .nav-icon {
            background: rgba(139, 92, 246, 0.2);
        }
        .nav-link.active .nav-icon {
            background: rgba(139, 92, 246, 0.25);
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.3);
        }
        .logo-icon { 
            background: linear-gradient(135deg, #a78bfa, #8b5cf6); 
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4); 
        }
        .role-badge { 
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
    </style>
    @endif
</head>
<body class="bg-gray-50">
    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-72 text-white flex flex-col z-50 overflow-y-auto">
        <!-- Logo Section -->
        <div class="p-6 border-b border-slate-700/50">
            <div class="flex items-center space-x-3 mb-4">
                <div class="logo-icon w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                    @if(auth()->user()->isSuperAdmin())
                        <i class="fas fa-crown text-xl text-white"></i>
                    @elseif(auth()->user()->isBranchManager())
                        <i class="fas fa-store text-xl text-white"></i>
                    @elseif(auth()->user()->isCashier())
                        <i class="fas fa-cash-register text-xl text-white"></i>
                    @else
                        <i class="fas fa-user text-xl text-white"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-white">FoodCo</h1>
                    <p class="text-xs text-slate-400">
                        @if(auth()->user()->isSuperAdmin())
                            Admin Panel
                        @elseif(auth()->user()->isBranchManager())
                            Branch Manager
                        @elseif(auth()->user()->isCashier())
                            POS System
                        @else
                            User Panel
                        @endif
                    </p>
                </div>
            </div>
            <div class="role-badge inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white w-full justify-center">
                @if(auth()->user()->isSuperAdmin())
                    <i class="fas fa-shield-alt mr-2 text-sm"></i>
                    <span>Super Administrator</span>
                @elseif(auth()->user()->isBranchManager())
                    <i class="fas fa-user-tie mr-2 text-sm"></i>
                    <span>Branch Manager</span>
                @elseif(auth()->user()->isCashier())
                    <i class="fas fa-user-check mr-2 text-sm"></i>
                    <span>Cashier</span>
                @else
                    <i class="fas fa-user mr-2 text-sm"></i>
                    <span>{{ auth()->user()->role->display_name ?? 'User' }}</span>
                @endif
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex-1">
            @if(auth()->check() && auth()->user()->hasRole('branch_manager'))
                @include('partials.navigation.branch-manager')
            @elseif(auth()->check() && auth()->user()->hasRole('cashier'))
                @include('partials.navigation.cashier')
            @else
                @include('partials.navigation.super-admin')
            @endif
        </div>
        
        <!-- User Profile -->
        <div class="mt-auto p-4 border-t border-slate-700/50 bg-slate-900/50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br 
                    @if(auth()->user()->isBranchManager())
                        from-emerald-500 to-green-600
                    @elseif(auth()->user()->isCashier())
                        from-violet-500 to-purple-600
                    @else
                        from-amber-400 to-orange-500
                    @endif
                    rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-xs text-slate-400">{{ auth()->user()->role->display_name ?? 'User' }}</p>
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
        <div class="top-nav sticky top-0 z-30">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
                <!-- Mobile Menu Button -->
                <button type="button" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Page Title -->
                <div class="flex-1 px-2 sm:px-0">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                            @if(auth()->user()->isSuperAdmin())
                                <i class="fas fa-crown text-white text-sm sm:text-lg"></i>
                            @elseif(auth()->user()->isBranchManager())
                                <i class="fas fa-store text-white text-sm sm:text-lg"></i>
                            @elseif(auth()->user()->isCashier())
                                <i class="fas fa-cash-register text-white text-sm sm:text-lg"></i>
                            @else
                                <i class="fas fa-user text-white text-sm sm:text-lg"></i>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 truncate">
                                @hasSection('title')
                                    @yield('title')
                                @else
                                    @if(auth()->user()->isSuperAdmin())
                                        Super Admin Dashboard
                                    @elseif(auth()->user()->isBranchManager())
                                        Branch Manager Dashboard
                                    @elseif(auth()->user()->isCashier())
                                        POS Dashboard
                                    @else
                                        User Dashboard
                                    @endif
                                @endif
                            </h1>
                            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">
                                @if(auth()->user()->isSuperAdmin())
                                    Complete system control and management
                                @elseif(auth()->user()->isBranchManager())
                                    Manage your branch operations
                                @elseif(auth()->user()->isCashier())
                                    Point of Sale System
                                @else
                                    User panel
                                @endif
                            </p>
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