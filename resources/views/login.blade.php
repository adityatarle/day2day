<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FoodCo') }} - Login</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        .login-container {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .role-card {
            background: rgba(30, 41, 59, 0.6);
            border: 2px solid rgba(148, 163, 184, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .role-card:hover {
            background: rgba(30, 41, 59, 0.8);
            border-color: #a78bfa;
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(167, 139, 250, 0.2);
        }
        
        .role-card.active {
            background: rgba(167, 139, 250, 0.1);
            border-color: #a78bfa;
            box-shadow: 0 0 30px rgba(167, 139, 250, 0.3);
        }
        
        .form-input {
            background: rgba(30, 41, 59, 0.5);
            border: 2px solid rgba(148, 163, 184, 0.2);
            color: #f1f5f9;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            background: rgba(30, 41, 59, 0.7);
            border-color: #a78bfa;
            box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
        }
        
        .form-input::placeholder {
            color: #64748b;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover:not(:disabled) {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(167, 139, 250, 0.4);
        }
        
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(30, 41, 59, 0.8);
            border-color: rgba(148, 163, 184, 0.4);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-form {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .login-form.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(5deg); }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        /* Mobile optimizations */
        @media (max-width: 640px) {
            .role-card {
                padding: 1rem;
            }
            
            .login-container {
                margin: 0.5rem;
                padding: 1.5rem;
            }
        }
        
        /* Loading spinner */
        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 2px solid #ffffff;
            width: 16px;
            height: 16px;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Password toggle button */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: #a78bfa;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-5xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl mb-4 shadow-2xl float-animation">
                <i class="fas fa-leaf text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-3">Welcome to FoodCo</h1>
            <p class="text-slate-400 text-lg">Fresh Produce Management System</p>
        </div>

        <!-- Role Selection Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <!-- Super Admin -->
            <div class="role-card rounded-xl p-6 text-center" onclick="selectRole('admin')" role="button" tabindex="0" aria-label="Select Super Admin Login">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl mb-4">
                    <i class="fas fa-crown text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Super Admin</h3>
                <p class="text-slate-400 text-sm">Complete system control</p>
            </div>

            <!-- Branch Manager -->
            <div class="role-card rounded-xl p-6 text-center" onclick="selectRole('branch')" role="button" tabindex="0" aria-label="Select Branch Manager Login">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl mb-4">
                    <i class="fas fa-store text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Branch Manager</h3>
                <p class="text-slate-400 text-sm">Branch operations</p>
            </div>

            <!-- Cashier -->
            <div class="role-card rounded-xl p-6 text-center sm:col-span-2 lg:col-span-1" onclick="selectRole('cashier')" role="button" tabindex="0" aria-label="Select Cashier Login">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-400 to-violet-500 rounded-xl mb-4">
                    <i class="fas fa-cash-register text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Cashier</h3>
                <p class="text-slate-400 text-sm">POS & sales</p>
            </div>

            <!-- Delivery Boy -->
            <div class="role-card rounded-xl p-6 text-center sm:col-span-2 lg:col-span-1" onclick="selectRole('delivery_boy')" role="button" tabindex="0" aria-label="Select Delivery Boy Login">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl mb-4">
                    <i class="fas fa-motorcycle text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Delivery Boy</h3>
                <p class="text-slate-400 text-sm">Order deliveries</p>
            </div>
        </div>

        <!-- Login Forms Container -->
        <div class="login-container rounded-2xl p-8 shadow-2xl">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-400 mt-0.5 mr-3"></i>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-400 mb-1">Authentication Failed</h3>
                            <ul class="text-sm text-red-300 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Default Message -->
            <div id="select-role-message" class="text-center py-16">
                <i class="fas fa-hand-pointer text-6xl text-purple-400 mb-6 opacity-50"></i>
                <h3 class="text-2xl font-bold text-white mb-2">Select Your Role</h3>
                <p class="text-slate-400">Choose your login type above to continue</p>
            </div>

            <!-- Admin Login Form -->
            <div id="admin-form" class="login-form">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl mb-4">
                        <i class="fas fa-crown text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Super Admin Login</h3>
                    <p class="text-slate-400">Full system access and control</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6" onsubmit="return handleSubmit(this)">
                    @csrf
                    <input type="hidden" name="login_type" value="admin">
                    
                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                        <input id="admin_email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               autocomplete="email"
                               class="form-input w-full px-4 py-3 rounded-lg text-base"
                               placeholder="admin@example.com"
                               aria-label="Admin email address">
                    </div>

                    <div class="relative">
                        <label for="admin_password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                        <input id="admin_password" type="password" name="password" required
                               autocomplete="current-password"
                               class="form-input w-full px-4 py-3 pr-12 rounded-lg text-base"
                               placeholder="Enter your password"
                               aria-label="Admin password">
                        <button type="button" class="password-toggle" onclick="togglePassword('admin_password')" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-purple-500 focus:ring-purple-500 focus:ring-offset-slate-800">
                            <span class="ml-2 text-slate-300">Remember me</span>
                        </label>
                        <a href="#" class="text-purple-400 hover:text-purple-300 transition-colors">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login w-full text-white font-semibold py-3 px-6 rounded-lg text-base" aria-label="Sign in as Super Admin">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In as Super Admin
                        </span>
                        <span class="btn-loading hidden">
                            <span class="spinner inline-block mr-2"></span>
                            Signing in...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Branch Manager Login Form -->
            <div id="branch-form" class="login-form">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl mb-4">
                        <i class="fas fa-store text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Branch Manager Login</h3>
                    <p class="text-slate-400">Manage your branch operations</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6" onsubmit="return handleSubmit(this)">
                    @csrf
                    <input type="hidden" name="login_type" value="branch">
                    
                    <div>
                        <label for="branch_code" class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fas fa-hashtag mr-2"></i>Outlet Code
                        </label>
                        <input id="branch_code" type="text" name="outlet_code" value="{{ old('outlet_code') }}" required
                               autocomplete="off"
                               class="form-input w-full px-4 py-3 rounded-lg text-base uppercase"
                               placeholder="e.g., MB001"
                               aria-label="Outlet code"
                               style="text-transform: uppercase;">
                        <p class="text-xs text-slate-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Enter your assigned outlet code
                        </p>
                    </div>
                    
                    <div>
                        <label for="branch_email" class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email Address
                        </label>
                        <input id="branch_email" type="email" name="email" value="{{ old('email') }}" required
                               autocomplete="email"
                               class="form-input w-full px-4 py-3 rounded-lg text-base"
                               placeholder="manager@example.com"
                               aria-label="Branch manager email">
                    </div>

                    <div class="relative">
                        <label for="branch_password" class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input id="branch_password" type="password" name="password" required
                               autocomplete="current-password"
                               class="form-input w-full px-4 py-3 pr-12 rounded-lg text-base"
                               placeholder="Enter your password"
                               aria-label="Branch manager password">
                        <button type="button" class="password-toggle" onclick="togglePassword('branch_password')" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-purple-500 focus:ring-purple-500 focus:ring-offset-slate-800">
                            <span class="ml-2 text-slate-300">Remember me</span>
                        </label>
                        <a href="#" class="text-purple-400 hover:text-purple-300 transition-colors">Need help?</a>
                    </div>

                    <button type="submit" class="btn-login w-full text-white font-semibold py-3 px-6 rounded-lg text-base" aria-label="Sign in as Branch Manager">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In as Branch Manager
                        </span>
                        <span class="btn-loading hidden">
                            <span class="spinner inline-block mr-2"></span>
                            Signing in...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Cashier Login Form -->
            <div id="cashier-form" class="login-form">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-400 to-violet-500 rounded-xl mb-4">
                        <i class="fas fa-cash-register text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Cashier Login</h3>
                    <p class="text-slate-400">Access POS and sales system</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6" onsubmit="return handleSubmit(this)">
                    @csrf
                    <input type="hidden" name="login_type" value="cashier">
                    
                    <div>
                        <label for="cashier_code" class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fas fa-hashtag mr-2"></i>Outlet Code
                        </label>
                        <input id="cashier_code" type="text" name="outlet_code" value="{{ old('outlet_code') }}" required
                               autocomplete="off"
                               class="form-input w-full px-4 py-3 rounded-lg text-base uppercase"
                               placeholder="e.g., MB001"
                               aria-label="Outlet code"
                               style="text-transform: uppercase;">
                        <p class="text-xs text-slate-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Enter your assigned outlet code
                        </p>
                    </div>
                    
                    <div>
                        <label for="cashier_email" class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email Address
                        </label>
                        <input id="cashier_email" type="email" name="email" value="{{ old('email') }}" required
                               autocomplete="email"
                               class="form-input w-full px-4 py-3 rounded-lg text-base"
                               placeholder="cashier@example.com"
                               aria-label="Cashier email">
                    </div>

                    <div class="relative">
                        <label for="cashier_password" class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input id="cashier_password" type="password" name="password" required
                               autocomplete="current-password"
                               class="form-input w-full px-4 py-3 pr-12 rounded-lg text-base"
                               placeholder="Enter your password"
                               aria-label="Cashier password">
                        <button type="button" class="password-toggle" onclick="togglePassword('cashier_password')" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-purple-500 focus:ring-purple-500 focus:ring-offset-slate-800">
                            <span class="ml-2 text-slate-300">Remember me</span>
                        </label>
                        <a href="#" class="text-purple-400 hover:text-purple-300 transition-colors">Need help?</a>
                    </div>

                    <button type="submit" class="btn-login w-full text-white font-semibold py-3 px-6 rounded-lg text-base" aria-label="Sign in as Cashier">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In as Cashier
                        </span>
                        <span class="btn-loading hidden">
                            <span class="spinner inline-block mr-2"></span>
                            Signing in...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Delivery Boy Login Form -->
            <div id="delivery_boy-form" class="login-form">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl mb-4">
                        <i class="fas fa-motorcycle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Delivery Boy Login</h3>
                    <p class="text-slate-400">Manage order deliveries</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6" onsubmit="return handleSubmit(this)">
                    @csrf
                    <input type="hidden" name="login_type" value="delivery_boy">
                    
                    <div>
                        <label for="delivery_boy_email" class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email Address
                        </label>
                        <input id="delivery_boy_email" type="email" name="email" value="{{ old('email') }}" required
                               autocomplete="email"
                               class="form-input w-full px-4 py-3 rounded-lg text-base"
                               placeholder="delivery@example.com"
                               aria-label="Delivery boy email">
                    </div>

                    <div class="relative">
                        <label for="delivery_boy_password" class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input id="delivery_boy_password" type="password" name="password" required
                               autocomplete="current-password"
                               class="form-input w-full px-4 py-3 pr-12 rounded-lg text-base"
                               placeholder="Enter your password"
                               aria-label="Delivery boy password">
                        <button type="button" class="password-toggle" onclick="togglePassword('delivery_boy_password')" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-purple-500 focus:ring-purple-500 focus:ring-offset-slate-800">
                            <span class="ml-2 text-slate-300">Remember me</span>
                        </label>
                        <a href="#" class="text-purple-400 hover:text-purple-300 transition-colors">Need help?</a>
                    </div>

                    <button type="submit" class="btn-login w-full text-white font-semibold py-3 px-6 rounded-lg text-base" aria-label="Sign in as Delivery Boy">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In as Delivery Boy
                        </span>
                        <span class="btn-loading hidden">
                            <span class="spinner inline-block mr-2"></span>
                            Signing in...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Back Button -->
            <div class="mt-6 text-center">
                <button onclick="resetRoleSelection()" class="btn-secondary text-slate-300 px-6 py-2 rounded-lg text-sm hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to role selection
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-slate-500">
            <p>&copy; {{ date('Y') }} FoodCo. All rights reserved.</p>
            <p class="mt-2">Secure Login • 24/7 Support</p>
        </div>
    </div>

    <!-- Background Effects -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
    </div>

    <script>
        function selectRole(role) {
            // Remove active class from all cards
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('active');
            });
            
            // Hide all forms and message
            document.querySelectorAll('.login-form').forEach(form => {
                form.classList.remove('active');
            });
            document.getElementById('select-role-message').style.display = 'none';
            
            // Show selected form and activate card
            document.getElementById(role + '-form').classList.add('active');
            event.target.closest('.role-card').classList.add('active');
            
            // Focus on email input
            setTimeout(() => {
                const emailInput = document.querySelector(`#${role}_email, #${role === 'delivery_boy' ? 'delivery_boy_email' : role + '_email'}, #${role}-form input[type="email"]`);
                if (emailInput) emailInput.focus();
            }, 300);
        }

        function resetRoleSelection() {
            // Remove active class from all cards
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('active');
            });
            
            // Hide all forms
            document.querySelectorAll('.login-form').forEach(form => {
                form.classList.remove('active');
            });
            
            // Show selection message
            document.getElementById('select-role-message').style.display = 'block';
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function handleSubmit(form) {
            const button = form.querySelector('button[type="submit"]');
            const btnText = button.querySelector('.btn-text');
            const btnLoading = button.querySelector('.btn-loading');
            
            // Show loading state
            button.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            
            return true;
        }
        
        // Keyboard navigation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.role-card').forEach((card, index) => {
                card.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        card.click();
                    }
                });
            });
            
            // Add Escape key to reset
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const activeForm = document.querySelector('.login-form.active');
                    if (activeForm) {
                        resetRoleSelection();
                    }
                }
            });
        });
    </script>
</body>
</html>
