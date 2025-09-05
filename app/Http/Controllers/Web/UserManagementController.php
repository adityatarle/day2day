<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display user management page based on user role.
     */
    public function index()
    {
        $user = auth()->user();
        $users = $user->getManageableUsers();
        $branches = $user->getManageableBranches();
        
        // Get available roles based on user permissions
        $availableRoles = $this->getAvailableRoles($user);
        
        return view('users.index', compact('users', 'branches', 'availableRoles'));
    }

    /**
     * Show form to create new user.
     */
    public function create()
    {
        $user = auth()->user();
        $branches = $user->getManageableBranches();
        $availableRoles = $this->getAvailableRoles($user);
        
        return view('users.create', compact('branches', 'availableRoles'));
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate role assignment permissions
        $role = Role::findOrFail($request->role_id);
        if (!$this->canAssignRole($user, $role)) {
            return back()->withErrors(['role_id' => 'You do not have permission to assign this role.'])->withInput();
        }

        // Validate branch assignment
        if ($request->branch_id) {
            $branch = Branch::findOrFail($request->branch_id);
            if (!$user->canManageBranch($branch)) {
                return back()->withErrors(['branch_id' => 'You do not have permission to assign users to this branch.'])->withInput();
            }
        }

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'branch_id' => $request->branch_id,
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show user details.
     */
    public function show(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->canManageUser($user)) {
            abort(403, 'You do not have permission to view this user.');
        }

        $user->load(['role', 'branch', 'posSessions' => function($query) {
            $query->latest()->take(5);
        }]);

        return view('users.show', compact('user'));
    }

    /**
     * Show form to edit user.
     */
    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->canManageUser($user)) {
            abort(403, 'You do not have permission to edit this user.');
        }

        $branches = $currentUser->getManageableBranches();
        $availableRoles = $this->getAvailableRoles($currentUser);
        
        return view('users.edit', compact('user', 'branches', 'availableRoles'));
    }

    /**
     * Update user information.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->canManageUser($user)) {
            abort(403, 'You do not have permission to edit this user.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate role assignment permissions
        $role = Role::findOrFail($request->role_id);
        if (!$this->canAssignRole($currentUser, $role)) {
            return back()->withErrors(['role_id' => 'You do not have permission to assign this role.'])->withInput();
        }

        // Validate branch assignment
        if ($request->branch_id) {
            $branch = Branch::findOrFail($request->branch_id);
            if (!$currentUser->canManageBranch($branch)) {
                return back()->withErrors(['branch_id' => 'You do not have permission to assign users to this branch.'])->withInput();
            }
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'role_id' => $request->role_id,
            'branch_id' => $request->branch_id,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->canManageUser($user)) {
            return response()->json(['error' => 'You do not have permission to modify this user.'], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'status' => $user->is_active,
            'message' => $user->is_active ? 'User activated successfully.' : 'User deactivated successfully.'
        ]);
    }

    /**
     * Delete user (soft delete by deactivating).
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->canManageUser($user)) {
            abort(403, 'You do not have permission to delete this user.');
        }

        // Don't allow deletion if user has active POS sessions
        if ($user->currentPosSession()) {
            return back()->withErrors(['error' => 'Cannot delete user with active POS session.']);
        }

        $user->update(['is_active' => false]);

        return redirect()->route('users.index')->with('success', 'User deactivated successfully.');
    }

    /**
     * Get available roles based on current user permissions.
     */
    private function getAvailableRoles(User $user)
    {
        if ($user->isSuperAdmin()) {
            return Role::active()->get();
        }

        if ($user->isBranchManager()) {
            return Role::active()->whereIn('name', ['cashier', 'delivery_boy'])->get();
        }

        return collect();
    }

    /**
     * Check if current user can assign a specific role.
     */
    private function canAssignRole(User $user, Role $role)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isBranchManager()) {
            return in_array($role->name, ['cashier', 'delivery_boy']);
        }

        return false;
    }

    /**
     * Get user statistics for dashboard.
     */
    public function getUserStats()
    {
        $user = auth()->user();
        
        $stats = [];
        
        if ($user->isSuperAdmin()) {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::active()->count(),
                'super_admins' => User::byRole('super_admin')->count(),
                'branch_managers' => User::byRole('branch_manager')->count(),
                'cashiers' => User::byRole('cashier')->count(),
                'delivery_staff' => User::byRole('delivery_boy')->count(),
                'recent_logins' => User::whereNotNull('last_login_at')
                    ->where('last_login_at', '>=', now()->subDays(7))
                    ->count(),
            ];
        } elseif ($user->isBranchManager()) {
            $stats = [
                'branch_users' => User::byBranch($user->branch_id)->count(),
                'active_users' => User::byBranch($user->branch_id)->active()->count(),
                'cashiers' => User::byBranch($user->branch_id)->byRole('cashier')->count(),
                'delivery_staff' => User::byBranch($user->branch_id)->byRole('delivery_boy')->count(),
            ];
        }
        
        return response()->json($stats);
    }
}