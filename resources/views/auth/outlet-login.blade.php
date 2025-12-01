<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $outlet->name }} - Staff Login</title>

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
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(5deg); }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
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
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-open {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .status-closed {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .info-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Outlet Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl mb-4 shadow-2xl float-animation">
                <i class="fas fa-store text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">{{ $outlet->name }}</h1>
            <p class="text-slate-400 mb-3">{{ $outlet->address }}</p>
            <div class="flex items-center justify-center space-x-3 text-sm">
                <span class="text-slate-500">
                    <i class="fas fa-hashtag mr-1"></i>
                    {{ $outlet->code }}
                </span>
                <span class="status-badge {{ $outlet->isOpen() ? 'status-open' : 'status-closed' }}">
                    <i class="fas fa-{{ $outlet->isOpen() ? 'check-circle' : 'times-circle' }} mr-1"></i>
                    {{ $outlet->isOpen() ? 'Open Now' : 'Closed' }}
                </span>
            </div>
        </div>

        <!-- Login Form -->
        <div class="login-container rounded-2xl p-8 shadow-2xl">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-400 mt-0.5 mr-3"></i>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-400 mb-1">Login Failed</h3>
                            <ul class="text-sm text-red-300 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Closed Warning -->
            @if (!$outlet->isOpen())
                <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-400 mt-0.5 mr-3"></i>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-yellow-400 mb-1">Outlet Closed</h3>
                            <p class="text-sm text-yellow-300">
                                This outlet is currently closed. You can still log in for administrative tasks.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-white mb-2">Staff Login</h3>
                <p class="text-slate-400 text-sm">Sign in to access POS system</p>
            </div>

            <form method="POST" action="{{ route('outlet.login.process', $outlet->code) }}" class="space-y-6" onsubmit="return handleSubmit(this)">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           autocomplete="email"
                           class="form-input w-full px-4 py-3 rounded-lg text-base"
                           placeholder="your.email@example.com"
                           aria-label="Staff email address">
                </div>

                <div class="relative">
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input id="password" type="password" name="password" required
                           autocomplete="current-password"
                           class="form-input w-full px-4 py-3 pr-12 rounded-lg text-base"
                           placeholder="Enter your password"
                           aria-label="Staff password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="Toggle password visibility">
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

                <button type="submit" class="btn-login w-full text-white font-semibold py-3 px-6 rounded-lg text-base" aria-label="Sign in to outlet">
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In to {{ $outlet->name }}
                    </span>
                    <span class="btn-loading hidden">
                        <span class="spinner inline-block mr-2"></span>
                        Signing in...
                    </span>
                </button>
            </form>

            <!-- Outlet Info -->
            <div class="mt-8 pt-6 border-t border-slate-700">
                <h4 class="text-sm font-semibold text-white mb-3">
                    <i class="fas fa-info-circle mr-2"></i>Outlet Information
                </h4>
                <div class="space-y-2">
                    <div class="info-card p-3 rounded-lg flex items-center text-sm">
                        <i class="fas fa-phone w-5 text-purple-400 mr-3"></i>
                        <span class="text-slate-300">{{ $outlet->phone }}</span>
                    </div>
                    <div class="info-card p-3 rounded-lg flex items-center text-sm">
                        <i class="fas fa-envelope w-5 text-purple-400 mr-3"></i>
                        <span class="text-slate-300">{{ $outlet->email }}</span>
                    </div>
                    @if ($outlet->pos_enabled)
                        <div class="info-card p-3 rounded-lg flex items-center text-sm">
                            <i class="fas fa-cash-register w-5 text-purple-400 mr-3"></i>
                            <span class="text-slate-300">Terminal: {{ $outlet->pos_terminal_id }}</span>
                        </div>
                    @endif
                    <div class="info-card p-3 rounded-lg flex items-center text-sm">
                        <i class="fas fa-tag w-5 text-purple-400 mr-3"></i>
                        <span class="text-slate-300">Type: {{ ucfirst(str_replace('_', ' ', $outlet->outlet_type)) }}</span>
                    </div>
                </div>
            </div>

            <!-- Operating Hours -->
            @if ($outlet->operating_hours)
                <div class="mt-6 pt-6 border-t border-slate-700">
                    <h4 class="text-sm font-semibold text-white mb-3">
                        <i class="fas fa-clock mr-2"></i>Operating Hours
                    </h4>
                    <div class="info-card p-3 rounded-lg">
                        <div class="grid grid-cols-1 gap-1 text-xs">
                            @foreach ($outlet->operating_hours as $day => $hours)
                                <div class="flex justify-between py-1 text-slate-300">
                                    <span class="capitalize font-medium">{{ $day }}:</span>
                                    <span>
                                        @if (isset($hours['open']) && isset($hours['close']))
                                            {{ $hours['open'] }} - {{ $hours['close'] }}
                                        @else
                                            <span class="text-slate-500">Closed</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Back Link -->
            <div class="mt-8 pt-6 border-t border-slate-700 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-slate-400 hover:text-purple-400 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Main Login
                </a>
                <p class="text-xs text-slate-600 mt-2">Need access to a different outlet? Contact your manager.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-slate-500">
            <p>&copy; {{ date('Y') }} FoodCo. All rights reserved.</p>
            <p class="mt-1">{{ $outlet->name }} • {{ $outlet->code }}</p>
        </div>
    </div>

    <!-- Background Effects -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
    </div>

    <script>
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
    </script>
</body>
</html>
