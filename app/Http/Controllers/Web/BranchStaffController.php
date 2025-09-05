<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class BranchStaffController extends Controller
{
    /**
     * Display a listing of branch staff.
     */
    public function index()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account.');
        }

        $staff = $branch->users()->with('role')->paginate(20);
        $cashierRole = Role::where('name', 'cashier')->first();
        $deliveryRole = Role::where('name', 'delivery_boy')->first();

        return view('branch.staff.index', compact('staff', 'branch', 'cashierRole', 'deliveryRole'));
    }

    /**
     * Show the form for creating new staff.
     */
    public function create()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account.');
        }

        $roles = Role::whereIn('name', ['cashier', 'delivery_boy'])->get();

        return view('branch.staff.create', compact('branch', 'roles'));
    }

    /**
     * Store newly created staff.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Ensure only cashier and delivery_boy roles can be created
        $role = Role::findOrFail($validated['role_id']);
        if (!in_array($role->name, ['cashier', 'delivery_boy'])) {
            return back()->with('error', 'You can only create cashier and delivery staff.');
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['branch_id'] = $branch->id;
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('branch.staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    /**
     * Display the specified staff member.
     */
    public function show(User $staff)
    {
        $user = auth()->user();
        $branch = $user->branch;

        // Ensure the staff belongs to the manager's branch
        if ($staff->branch_id !== $branch->id) {
            return redirect()->route('branch.staff.index')
                ->with('error', 'Staff member not found in your branch.');
        }

        $staff->load(['role', 'posSessions', 'orders']);

        $stats = [
            'total_sales' => $staff->orders()->where('status', 'completed')->sum('total_amount'),
            'monthly_sales' => $staff->orders()
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),
            'total_orders' => $staff->orders()->count(),
            'active_sessions' => $staff->posSessions()->where('status', 'active')->count(),
        ];

        return view('branch.staff.show', compact('staff', 'branch', 'stats'));
    }

    /**
     * Show the form for editing staff.
     */
    public function edit(User $staff)
    {
        $user = auth()->user();
        $branch = $user->branch;

        // Ensure the staff belongs to the manager's branch
        if ($staff->branch_id !== $branch->id) {
            return redirect()->route('branch.staff.index')
                ->with('error', 'Staff member not found in your branch.');
        }

        $roles = Role::whereIn('name', ['cashier', 'delivery_boy'])->get();

        return view('branch.staff.edit', compact('staff', 'branch', 'roles'));
    }

    /**
     * Update the specified staff.
     */
    public function update(Request $request, User $staff)
    {
        $user = auth()->user();
        $branch = $user->branch;

        // Ensure the staff belongs to the manager's branch
        if ($staff->branch_id !== $branch->id) {
            return redirect()->route('branch.staff.index')
                ->with('error', 'Staff member not found in your branch.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        // Ensure only cashier and delivery_boy roles can be assigned
        $role = Role::findOrFail($validated['role_id']);
        if (!in_array($role->name, ['cashier', 'delivery_boy'])) {
            return back()->with('error', 'You can only assign cashier and delivery staff roles.');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $staff->update($validated);

        return redirect()->route('branch.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified staff.
     */
    public function destroy(User $staff)
    {
        $user = auth()->user();
        $branch = $user->branch;

        // Ensure the staff belongs to the manager's branch
        if ($staff->branch_id !== $branch->id) {
            return redirect()->route('branch.staff.index')
                ->with('error', 'Staff member not found in your branch.');
        }

        // Check if staff has active POS sessions
        if ($staff->posSessions()->where('status', 'active')->exists()) {
            return redirect()->route('branch.staff.index')
                ->with('error', 'Cannot delete staff with active POS sessions.');
        }

        $staff->delete();

        return redirect()->route('branch.staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }

    /**
     * Toggle staff active status.
     */
    public function toggleStatus(User $staff)
    {
        $user = auth()->user();
        $branch = $user->branch;

        // Ensure the staff belongs to the manager's branch
        if ($staff->branch_id !== $branch->id) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member not found in your branch.'
            ]);
        }

        $staff->update(['is_active' => !$staff->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Staff status updated successfully.',
            'is_active' => $staff->is_active
        ]);
    }
}