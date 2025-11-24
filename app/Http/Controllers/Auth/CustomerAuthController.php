<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Customer/Dealer login with mobile number
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|regex:/^[0-9]{10}$/',
            'password' => 'required|string|min:6',
        ], [
            'mobile.required' => 'Mobile number is required',
            'mobile.regex' => 'Mobile number must be 10 digits',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find customer by mobile number (phone field)
        $customer = Customer::where('phone', $request->mobile)
            ->where('is_active', true)
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid mobile number or account not found'
            ], 401);
        }

        // Check if customer has a password set
        if (!$customer->password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not activated. Please set your password first.'
            ], 401);
        }

        // Verify password
        if (!Hash::check($request->password, $customer->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid password'
            ], 401);
        }

        // Update last login info
        $customer->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        // Generate token
        $token = $customer->createToken('customer-auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'customer_type' => $customer->customer_type,
                    'customer_type_display' => $customer->getCustomerTypeDisplayName(),
                    'is_dealer' => in_array($customer->customer_type, ['distributor', 'retailer']),
                    'credit_limit' => (float) $customer->credit_limit,
                    'credit_days' => $customer->credit_days,
                    'credit_balance' => $customer->getCreditBalance(),
                    'remaining_credit_limit' => $customer->getRemainingCreditLimit(),
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * Customer/Dealer registration
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|regex:/^[0-9]{10}$/|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'nullable|string|max:1000',
            'customer_type' => 'nullable|in:walk_in,regular,regular_wholesale,premium_wholesale,distributor,retailer',
        ], [
            'mobile.required' => 'Mobile number is required',
            'mobile.regex' => 'Mobile number must be 10 digits',
            'mobile.unique' => 'This mobile number is already registered',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create customer
        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->mobile,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'customer_type' => $request->customer_type ?? 'regular',
            'type' => in_array($request->customer_type, ['distributor', 'retailer', 'regular_wholesale', 'premium_wholesale']) ? 'wholesale' : 'retail',
            'is_active' => true,
        ]);

        // Generate token
        $token = $customer->createToken('customer-auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'customer_type' => $customer->customer_type,
                    'customer_type_display' => $customer->getCustomerTypeDisplayName(),
                    'is_dealer' => in_array($customer->customer_type, ['distributor', 'retailer']),
                ],
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Customer/Dealer logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current customer/dealer profile
     */
    public function profile(Request $request): JsonResponse
    {
        $customer = $request->user();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'customer_type' => $customer->customer_type,
                    'customer_type_display' => $customer->getCustomerTypeDisplayName(),
                    'is_dealer' => in_array($customer->customer_type, ['distributor', 'retailer']),
                    'credit_limit' => (float) $customer->credit_limit,
                    'credit_days' => $customer->credit_days,
                    'credit_balance' => $customer->getCreditBalance(),
                    'remaining_credit_limit' => $customer->getRemainingCreditLimit(),
                    'total_purchase_amount' => $customer->getTotalPurchaseAmount(),
                ]
            ]
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = $request->user();

        if (!Hash::check($request->current_password, $customer->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $customer->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Request password reset (OTP-based)
     */
    public function requestPasswordReset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('phone', $request->mobile)
            ->where('is_active', true)
            ->first();

        if (!$customer) {
            // Don't reveal if customer exists or not for security
            return response()->json([
                'status' => 'success',
                'message' => 'If the mobile number exists, a password reset OTP will be sent.'
            ]);
        }

        // TODO: Implement OTP generation and SMS sending
        // For now, return success message
        return response()->json([
            'status' => 'success',
            'message' => 'Password reset OTP sent to your mobile number',
            'data' => [
                'otp_sent' => true,
                // In production, don't send OTP in response
                // 'otp' => $otp, // Only for development
            ]
        ]);
    }

    /**
     * Reset password with OTP
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|regex:/^[0-9]{10}$/',
            'otp' => 'required|string|size:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('phone', $request->mobile)
            ->where('is_active', true)
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid mobile number'
            ], 404);
        }

        // TODO: Verify OTP
        // For now, accept any 6-digit OTP (implement proper OTP verification)
        
        $customer->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully'
        ]);
    }
}


