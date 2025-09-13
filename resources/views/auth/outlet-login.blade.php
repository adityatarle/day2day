<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $outlet->name }} - Staff Login</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .form-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #10b981, #059669);
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        .outlet-status {
            @apply inline-flex items-center px-3 py-1 rounded-full text-sm font-medium;
        }

        .status-open {
            @apply bg-green-100 text-green-800;
        }

        .status-closed {
            @apply bg-red-100 text-red-800;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Outlet Info Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full mb-4 shadow-lg float-animation">
                <i class="fas fa-store text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $outlet->name }}</h1>
            <p class="text-gray-600 mb-2">{{ $outlet->address }}</p>
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-500">
                <span>
                    <i class="fas fa-code mr-1"></i>
                    {{ $outlet->code }}
                </span>
                <span class="outlet-status {{ $outlet->isOpen() ? 'status-open' : 'status-closed' }}">
                    <i class="fas fa-{{ $outlet->isOpen() ? 'unlock' : 'lock' }} mr-1"></i>
                    {{ $outlet->isOpen() ? 'Open' : 'Closed' }}
                </span>
            </div>
        </div>

        <!-- Login Form -->
        <div class="login-card rounded-2xl p-8">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Login Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Outlet Closed Warning -->
            @if (!$outlet->isOpen())
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Outlet Currently Closed</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                This outlet is outside of operating hours. You can still log in for administrative tasks.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="text-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Staff Login</h3>
                <p class="text-gray-600">Enter your credentials to access the system</p>
            </div>

            <form method="POST" action="{{ route('outlet.login.process', $outlet->code) }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Staff Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                           placeholder="your.email@foodcompany.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input id="password" type="password" name="password" required
                           class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                           placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>
                    
                    <a href="#" class="text-sm text-green-600 hover:text-green-500 transition-colors duration-200">
                        Need help?
                    </a>
                </div>

                <button type="submit" class="btn-login w-full text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In to {{ $outlet->name }}
                </button>
            </form>

            <!-- Outlet Info -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Outlet Information</h4>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-phone w-4 mr-2"></i>
                        {{ $outlet->phone }}
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope w-4 mr-2"></i>
                        {{ $outlet->email }}
                    </div>
                    @if ($outlet->pos_enabled)
                        <div class="flex items-center">
                            <i class="fas fa-cash-register w-4 mr-2"></i>
                            POS Terminal: {{ $outlet->pos_terminal_id }}
                        </div>
                    @endif
                    <div class="flex items-center">
                        <i class="fas fa-tag w-4 mr-2"></i>
                        Type: {{ ucfirst(str_replace('_', ' ', $outlet->outlet_type)) }}
                    </div>
                </div>
            </div>

            <!-- Operating Hours -->
            @if ($outlet->operating_hours)
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Operating Hours</h4>
                    <div class="grid grid-cols-1 gap-1 text-xs text-gray-600">
                        @foreach ($outlet->operating_hours as $day => $hours)
                            <div class="flex justify-between">
                                <span class="capitalize">{{ $day }}:</span>
                                <span>
                                    @if (isset($hours['open']) && isset($hours['close']))
                                        {{ $hours['open'] }} - {{ $hours['close'] }}
                                    @else
                                        Closed
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Navigation Links -->
            <div class="mt-8 pt-6 border-t border-gray-200 text-center space-y-2">
                <div>
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Main Login
                    </a>
                </div>
                <div class="text-xs text-gray-500">
                    Need a different outlet? Contact your manager.
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Food Company') }}. All rights reserved.</p>
            <p class="mt-1">{{ $outlet->name }} - {{ $outlet->code }}</p>
        </div>
    </div>

    <!-- Background decoration -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-200 to-emerald-200 rounded-full opacity-20 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-tr from-emerald-200 to-green-200 rounded-full opacity-20 blur-3xl"></div>
    </div>
</body>
</html>