<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class OutletAuthController extends Controller
{
    /**
     * Outlet-specific login (API)
     */
    public function outletLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'outlet_code' => 'required|string', // Branch code for outlet-specific login
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        // Find the outlet by code
        $outlet = Branch::where('code', $request->outlet_code)->first();
        
        if (!$outlet) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid outlet code'
            ], 401);
        }

        if (!$outlet->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Outlet is currently inactive'
            ], 401);
        }

        // Find user and verify they belong to this outlet
        $user = User::where('email', $request->email)
                   ->where('branch_id', $outlet->id)
                   ->where('is_active', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or user not assigned to this outlet'
            ], 401);
        }

        // Generate token
        $token = $user->createToken('outlet-auth', ['outlet:' . $outlet->code])->plainTextToken;

        // Update last login
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->load(['role', 'branch.city']),
                'outlet' => $outlet->load('city'),
                'token' => $token,
                'permissions' => $user->role->permissions->pluck('name'),
            ],
            'message' => 'Login successful'
        ]);
    }

    /**
     * Outlet staff login (Web interface)
     */
    public function showOutletLogin($outletCode)
    {
        $outlet = Branch::where('code', $outletCode)->active()->first();
        
        if (!$outlet) {
            abort(404, 'Outlet not found');
        }

        return view('auth.outlet-login', compact('outlet'));
    }

    /**
     * Process outlet staff login (Web)
     */
    public function processOutletLogin(Request $request, $outletCode)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $outlet = Branch::where('code', $outletCode)->active()->first();
        
        if (!$outlet) {
            return back()->withErrors(['outlet' => 'Invalid outlet']);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'branch_id' => $outlet->id,
            'is_active' => true,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Update last login
            auth()->user()->update(['last_login_at' => now()]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials or user not assigned to this outlet',
        ])->onlyInput('email');
    }

    /**
     * Get outlet information for login page
     */
    public function getOutletInfo($outletCode): JsonResponse
    {
        $outlet = Branch::with('city')
                       ->where('code', $outletCode)
                       ->active()
                       ->first();

        if (!$outlet) {
            return response()->json([
                'success' => false,
                'message' => 'Outlet not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'outlet' => $outlet,
                'is_open' => $outlet->isOpen(),
                'pos_enabled' => $outlet->pos_enabled,
            ],
            'message' => 'Outlet information retrieved'
        ]);
    }

    /**
     * Logout from outlet
     */
    public function outletLogout(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Close any active POS sessions
        $activeSessions = $user->posSessions()->active()->get();
        foreach ($activeSessions as $session) {
            $session->update(['status' => 'suspended']);
        }

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Change password for outlet staff
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
