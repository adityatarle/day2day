<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Food Company') }} - Login</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
    
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
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #f97316, #ea580c);
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #ea580c, #dc2626);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
        }

        .role-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .role-card:hover {
            border-color: #f97316;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.2);
        }

        .role-card.active {
            border-color: #f97316;
            background: rgba(249, 115, 22, 0.1);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        .login-form {
            display: none;
        }

        .login-form.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-orange-50 to-red-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-4xl">
        <!-- Logo/Brand Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-orange-500 to-red-500 rounded-full mb-4 shadow-lg float-animation">
                <i class="fas fa-utensils text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Welcome to FoodCo</h1>
            <p class="text-gray-600 text-lg">Choose your login type to access your management system</p>
        </div>

        <!-- Role Selection Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <!-- Admin Login -->
            <div class="role-card rounded-2xl p-6 cursor-pointer text-center" onclick="selectRole('admin')">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-full mb-4">
                    <i class="fas fa-crown text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Admin Login</h3>
                <p class="text-gray-600 text-sm">Full system access and management</p>
                <div class="mt-4 text-red-600 font-medium">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Super Admin Access
                </div>
            </div>

            <!-- Branch Manager Login -->
            <div class="role-card rounded-2xl p-6 cursor-pointer text-center" onclick="selectRole('branch')">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full mb-4">
                    <i class="fas fa-building text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Branch Manager</h3>
                <p class="text-gray-600 text-sm">Manage branch operations and staff</p>
                <div class="mt-4 text-blue-600 font-medium">
                    <i class="fas fa-users mr-2"></i>
                    Branch Management
                </div>
            </div>

            <!-- Outlet Staff Login -->
            <div class="role-card rounded-2xl p-6 cursor-pointer text-center" onclick="selectRole('outlet')">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full mb-4">
                    <i class="fas fa-store text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Outlet Staff</h3>
                <p class="text-gray-600 text-sm">POS system and daily operations</p>
                <div class="mt-4 text-green-600 font-medium">
                    <i class="fas fa-cash-register mr-2"></i>
                    POS & Operations
                </div>
            </div>
        </div>

        <!-- Login Forms Container -->
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
                            <h3 class="text-sm font-medium text-red-800">There were some errors with your submission</h3>
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

            <!-- Default message -->
            <div id="select-role-message" class="text-center py-12">
                <i class="fas fa-hand-pointer text-4xl text-orange-500 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Select Your Login Type</h3>
                <p class="text-gray-600">Please choose your role above to proceed with login</p>
            </div>

            <!-- Admin Login Form -->
            <div id="admin-form" class="login-form">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-crown text-red-500 mr-2"></i>
                        Admin Login
                    </h3>
                    <p class="text-gray-600">Enter your administrator credentials</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="login_type" value="admin">
                    
                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>
                        <input id="admin_email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                               placeholder="admin@foodcompany.com">
                    </div>

                    <div>
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Admin Password</label>
                        <input id="admin_password" type="password" name="password" required
                               class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                               placeholder="Enter admin password">
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="admin_remember" name="remember" type="checkbox" 
                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                            <label for="admin_remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>
                        
                        <a href="#" class="text-sm text-red-600 hover:text-red-500 transition-colors duration-200">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-crown mr-2"></i>
                        Sign In as Admin
                    </button>
                </form>
            </div>

            <!-- Branch Manager Login Form -->
            <div id="branch-form" class="login-form">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-building text-blue-500 mr-2"></i>
                        Branch Manager Login
                    </h3>
                    <p class="text-gray-600">Access your branch management system</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="login_type" value="branch">
                    
                    <div>
                        <label for="branch_email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input id="branch_email" type="email" name="email" value="{{ old('email') }}" required
                               class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                               placeholder="manager@foodcompany.com">
                    </div>

                    <div>
                        <label for="branch_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input id="branch_password" type="password" name="password" required
                               class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                               placeholder="Enter your password">
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="branch_remember" name="remember" type="checkbox" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="branch_remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>
                        
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-500 transition-colors duration-200">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-building mr-2"></i>
                        Sign In as Branch Manager
                    </button>
                </form>
            </div>

            <!-- Outlet Staff Login Form -->
            <div id="outlet-form" class="login-form">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-store text-green-500 mr-2"></i>
                        Outlet Staff Login
                    </h3>
                    <p class="text-gray-600">Access your outlet POS system</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="login_type" value="outlet">
                    
                    <div>
                        <label for="outlet_code" class="block text-sm font-medium text-gray-700 mb-2">Outlet Code</label>
                        <input id="outlet_code" type="text" name="outlet_code" required
                               class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                               placeholder="Enter outlet code (e.g., FDC001)">
                    </div>
                    
                    <div>
                        <label for="outlet_email" class="block text-sm font-medium text-gray-700 mb-2">Staff Email</label>
                        <input id="outlet_email" type="email" name="email" value="{{ old('email') }}" required
                               class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                               placeholder="staff@foodcompany.com">
                    </div>

                    <div>
                        <label for="outlet_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input id="outlet_password" type="password" name="password" required
                               class="form-input w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition-all duration-200"
                               placeholder="Enter your password">
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="outlet_remember" name="remember" type="checkbox" 
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <label for="outlet_remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>
                        
                        <a href="#" class="text-sm text-green-600 hover:text-green-500 transition-colors duration-200">
                            Need help?
                        </a>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-store mr-2"></i>
                        Sign In to Outlet
                    </button>
                </form>
            </div>

            <!-- Back to role selection -->
            <div class="mt-6 text-center">
                <button onclick="resetRoleSelection()" class="text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to role selection
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Food Company') }}. All rights reserved.</p>
            <p class="mt-2">For technical support, contact IT department</p>
        </div>
    </div>

    <!-- Background decoration -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-orange-200 to-red-200 rounded-full opacity-20 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-tr from-red-200 to-orange-200 rounded-full opacity-20 blur-3xl"></div>
    </div>

    <!-- JavaScript for role selection -->
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
    </script>
</body>
</html>