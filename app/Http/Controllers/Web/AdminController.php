<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        return redirect()->route('dashboard');
    }

    /**
     * Display all users
     */
    public function users()
    {
        $users = User::with(['role', 'branch'])->paginate(15);
        $roles = Role::all();
        $branches = Branch::all();
        
        return view('admin.users.index', compact('users', 'roles', 'branches'));
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        $roles = Role::all();
        $branches = Branch::all();
        
        return view('admin.users.create', compact('roles', 'branches'));
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'branch_id' => $request->branch_id,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    /**
     * Show edit user form
     */
    public function editUser(User $user)
    {
        $roles = Role::all();
        $branches = Branch::all();
        
        return view('admin.users.edit', compact('user', 'roles', 'branches'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'branch_id' => $request->branch_id,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting the current user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }

    /**
     * Display all branches
     */
    public function branches()
    {
        $branches = Branch::withCount(['users', 'orders', 'products'])
            ->withSum('orders', 'total_amount')
            ->paginate(15);
        
        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Show create branch form
     */
    public function createBranch()
    {
        return view('admin.branches.create');
    }

    /**
     * Store new branch
     */
    public function storeBranch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Branch::create($request->all());

        return redirect()->route('admin.branches')->with('success', 'Branch created successfully!');
    }

    /**
     * Show edit branch form
     */
    public function editBranch(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    /**
     * Update branch
     */
    public function updateBranch(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('branches')->ignore($branch->id)],
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $branch->update($request->all());

        return redirect()->route('admin.branches')->with('success', 'Branch updated successfully!');
    }

    /**
     * Delete branch
     */
    public function deleteBranch(Branch $branch)
    {
        // Check if branch has users or orders
        if ($branch->users()->count() > 0 || $branch->orders()->count() > 0) {
            return redirect()->route('admin.branches')->with('error', 'Cannot delete branch with existing users or orders!');
        }

        $branch->delete();

        return redirect()->route('admin.branches')->with('success', 'Branch deleted successfully!');
    }

    /**
     * Display roles and permissions
     */
    public function roles()
    {
        $roles = Role::withCount('users')->get();
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show branch performance analytics
     */
    public function branchPerformance()
    {
        $branches = Branch::withCount(['users', 'orders', 'products'])
            ->withSum('orders', 'total_amount')
            ->withAvg('orders', 'total_amount')
            ->get();

        return view('admin.branches.performance', compact('branches'));
    }
}