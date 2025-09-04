<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class OutletController extends Controller
{
    /**
     * Display a listing of outlets/branches.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Branch::with(['city', 'users' => function($q) {
            $q->where('is_active', true);
        }]);

        // Filter by city if provided
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by outlet type if provided
        if ($request->has('outlet_type')) {
            $query->where('outlet_type', $request->outlet_type);
        }

        // Filter by POS enabled status
        if ($request->has('pos_enabled')) {
            $query->where('pos_enabled', $request->boolean('pos_enabled'));
        }

        $outlets = $query->active()->get();

        return response()->json([
            'success' => true,
            'data' => $outlets,
            'message' => 'Outlets retrieved successfully'
        ]);
    }

    /**
     * Store a newly created outlet.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:branches,email',
            'city_id' => 'required|exists:cities,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'outlet_type' => 'required|in:retail,wholesale,kiosk',
            'operating_hours' => 'nullable|array',
            'pos_enabled' => 'boolean',
            'pos_terminal_id' => 'nullable|string|unique:branches,pos_terminal_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $outlet = Branch::create($request->all());
        $outlet->load('city');

        return response()->json([
            'success' => true,
            'data' => $outlet,
            'message' => 'Outlet created successfully'
        ], 201);
    }

    /**
     * Display the specified outlet.
     */
    public function show($id): JsonResponse
    {
        $outlet = Branch::with(['city', 'users', 'products', 'posSessions' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $outlet,
            'message' => 'Outlet retrieved successfully'
        ]);
    }

    /**
     * Update the specified outlet.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $outlet = Branch::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:branches,code,' . $outlet->id,
            'address' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:branches,email,' . $outlet->id,
            'city_id' => 'sometimes|required|exists:cities,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'outlet_type' => 'sometimes|required|in:retail,wholesale,kiosk',
            'operating_hours' => 'nullable|array',
            'pos_enabled' => 'boolean',
            'pos_terminal_id' => 'nullable|string|unique:branches,pos_terminal_id,' . $outlet->id,
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $outlet->update($request->all());
        $outlet->load('city');

        return response()->json([
            'success' => true,
            'data' => $outlet,
            'message' => 'Outlet updated successfully'
        ]);
    }

    /**
     * Remove the specified outlet.
     */
    public function destroy($id): JsonResponse
    {
        $outlet = Branch::findOrFail($id);
        
        // Check if outlet has active sessions
        if ($outlet->posSessions()->active()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete outlet with active POS sessions'
            ], 400);
        }

        $outlet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Outlet deleted successfully'
        ]);
    }

    /**
     * Get outlets by city.
     */
    public function getByCity($cityId): JsonResponse
    {
        $outlets = Branch::with('city')
            ->where('city_id', $cityId)
            ->active()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $outlets,
            'message' => 'City outlets retrieved successfully'
        ]);
    }

    /**
     * Create outlet staff/user.
     */
    public function createStaff(Request $request, $outletId): JsonResponse
    {
        $outlet = Branch::findOrFail($outletId);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'branch_id' => $outletId,
            'is_active' => true,
        ]);

        $user->load(['role', 'branch']);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Outlet staff created successfully'
        ], 201);
    }

    /**
     * Get outlet performance metrics.
     */
    public function getPerformanceMetrics($outletId): JsonResponse
    {
        $outlet = Branch::findOrFail($outletId);

        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $metrics = [
            'daily_sales' => $outlet->orders()->whereDate('created_at', $today)->sum('total_amount'),
            'monthly_sales' => $outlet->orders()->whereDate('created_at', '>=', $thisMonth)->sum('total_amount'),
            'daily_orders' => $outlet->orders()->whereDate('created_at', $today)->count(),
            'monthly_orders' => $outlet->orders()->whereDate('created_at', '>=', $thisMonth)->count(),
            'active_staff' => $outlet->users()->active()->count(),
            'current_pos_session' => $outlet->currentPosSession(),
            'is_open' => $outlet->isOpen(),
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'message' => 'Performance metrics retrieved successfully'
        ]);
    }
}
