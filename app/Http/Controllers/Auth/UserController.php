<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get all users (Admin only)
     */
    public function index(Request $request)
    {
        $users = User::with(['role', 'branch'])
            ->when($request->role_id, function($query, $roleId) {
                return $query->where('role_id', $roleId);
            })
            ->when($request->branch_id, function($query, $branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($request->search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Create a new user (Admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'branch_id' => $request->branch_id,
            'is_active' => true,
        ]);

        $user->load(['role', 'branch']);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Get user details
     */
    public function show(User $user)
    {
        $user->load(['role', 'branch']);
        
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    /**
     * Update user (Admin only)
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'nullable|string|max:20',
            'role_id' => 'sometimes|required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'name', 'email', 'phone', 'role_id', 'branch_id', 'is_active'
        ]));

        $user->load(['role', 'branch']);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Delete user (Admin only)
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get available roles
     */
    public function getRoles()
    {
        $roles = Role::active()->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $roles
        ]);
    }

    /**
     * Get available branches
     */
    public function getBranches()
    {
        $branches = Branch::active()->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $branches
        ]);
    }
}