<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        
        return view('login');
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        // Validate based on login type
        $loginType = $request->input('login_type');
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
        
        // Add outlet code validation for branch managers and cashiers
        if (in_array($loginType, ['branch', 'cashier'])) {
            $rules['outlet_code'] = 'required|string';
        }
        
        $validated = $request->validate($rules);
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Verify outlet code for branch managers and cashiers
            if (in_array($loginType, ['branch', 'cashier'])) {
                $outletCode = strtoupper($request->input('outlet_code'));
                
                // Check if user has an assigned branch
                if (!$user->branch) {
                    Auth::logout();
                    return back()->withErrors([
                        'outlet_code' => 'Your account is not assigned to any outlet. Please contact your administrator.',
                    ])->withInput($request->only('email', 'outlet_code'));
                }
                
                // Verify the outlet code matches the user's assigned branch
                if (strtoupper($user->branch->code) !== $outletCode) {
                    Auth::logout();
                    return back()->withErrors([
                        'outlet_code' => 'Invalid outlet code. You are assigned to outlet: ' . $user->branch->code,
                    ])->withInput($request->only('email', 'outlet_code'));
                }
                
                // Verify user has the correct role
                if ($loginType === 'branch' && !$user->hasRole('branch_manager')) {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'You do not have Branch Manager privileges. Please use the correct login type.',
                    ])->withInput($request->only('email', 'outlet_code'));
                }
                
                if ($loginType === 'cashier' && !$user->hasRole('cashier')) {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'You do not have Cashier privileges. Please use the correct login type.',
                    ])->withInput($request->only('email', 'outlet_code'));
                }
            }
            
            // Verify admin/super_admin role if login_type is admin
            if ($loginType === 'admin') {
                if (!$user->hasRole('super_admin') && !$user->hasRole('admin')) {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'You do not have administrative privileges.',
                    ])->withInput($request->only('email'));
                }
            }
            
            // Check if user is active
            if (property_exists($user, 'is_active') && !$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ])->withInput($request->only('email'));
            }
            
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'outlet_code'));
    }

    /**
     * Handle the logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}