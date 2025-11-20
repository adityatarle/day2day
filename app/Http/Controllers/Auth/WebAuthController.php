<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WebAuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Handle a login request to the application
     */
    public function login(Request $request)
    {
        $loginType = $request->input('login_type', 'general');
        
        // Base validation rules
        $rules = [
            'email' => 'required|email',
            'password' => 'required|string',
        ];

        // Add outlet code validation for outlet login
        if ($loginType === 'outlet') {
            $rules['outlet_code'] = 'required|string';
        }

        $request->validate($rules);

        // Handle different login types
        switch ($loginType) {
            case 'admin':
                return $this->handleAdminLogin($request);
            case 'branch':
                return $this->handleBranchLogin($request);
            case 'outlet':
                return $this->handleOutletLogin($request);
            default:
                return $this->handleGeneralLogin($request);
        }
    }

    /**
     * Handle admin login with enhanced security
     */
    protected function handleAdminLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Add admin role requirement
        $user = User::where('email', $request->email)
                   ->whereHas('role', function($query) {
                       $query->where('name', 'admin');
                   })
                   ->where('is_active', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid admin credentials or account is inactive.'],
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Log admin login for security
        \Log::info('Admin login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->intended('/admin/dashboard');
    }

    /**
     * Handle branch manager login
     */
    protected function handleBranchLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Find branch manager
        $user = User::where('email', $request->email)
                   ->whereHas('role', function($query) {
                       $query->where('name', 'branch_manager');
                   })
                   ->where('is_active', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid branch manager credentials or account is inactive.'],
            ]);
        }

        // Verify branch is active
        if ($user->branch && !$user->branch->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your branch is currently inactive. Please contact admin.'],
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Update last login
        $user->update(['last_login_at' => now()]);

        return redirect()->intended('/dashboard');
    }

    /**
     * Handle outlet staff login
     */
    protected function handleOutletLogin(Request $request)
    {
        $outletCode = $request->outlet_code;
        $email = $request->email;
        $password = $request->password;
        $remember = $request->boolean('remember');

        // Find the outlet by code
        $outlet = Branch::where('code', $outletCode)
                       ->where('is_active', true)
                       ->first();

        if (!$outlet) {
            throw ValidationException::withMessages([
                'outlet_code' => ['Invalid outlet code or outlet is inactive.'],
            ]);
        }

        // Check if outlet is open (optional check)
        if (!$outlet->isOpen()) {
            \Log::warning('Login attempt to closed outlet', [
                'outlet_code' => $outletCode,
                'email' => $email,
                'time' => now()
            ]);
        }

        // Find user assigned to this outlet
        $user = User::where('email', $email)
                   ->where('branch_id', $outlet->id)
                   ->where('is_active', true)
                   ->whereHas('role', function($query) {
                       $query->whereIn('name', ['cashier', 'branch_manager']);
                   })
                   ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials or user not assigned to this outlet.'],
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Store outlet context in session
        $request->session()->put('current_outlet', $outlet);

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Redirect based on role
        if ($user->hasRole('cashier')) {
            return redirect()->intended('/pos/session-handler');
        } else {
            return redirect()->intended('/dashboard');
        }
    }

    /**
     * Handle general login (fallback)
     */
    protected function handleGeneralLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Update last login
            $user->update(['last_login_at' => now()]);

            // Role-based redirect
            return $this->redirectBasedOnRole($user);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole($user)
    {
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard');
        } elseif ($user->isBranchManager()) {
            return redirect('/dashboard');
        } elseif ($user->isCashier()) {
            return redirect('/pos/session-handler');
        } elseif ($user->isDeliveryBoy()) {
            return redirect('/delivery/dashboard');
        }

        return redirect('/dashboard');
    }

    /**
     * Log the user out of the application
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Close any active POS sessions
        if ($user && $user->currentPosSession()) {
            $user->currentPosSession()->update(['status' => 'suspended']);
        }

        // Log logout for security (especially for admin users)
        if ($user && $user->isAdmin()) {
            \Log::info('Admin logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}