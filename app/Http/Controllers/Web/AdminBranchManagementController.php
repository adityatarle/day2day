<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBranchManagementController extends Controller
{
    /**
     * Display a listing of branches.
     */
    public function index()
    {
        $branches = Branch::with(['city', 'users', 'manager'])
            ->withCount(['users', 'orders', 'products'])
            ->withSum('orders', 'total_amount')
            ->paginate(20);
        
        $cities = City::all();
        
        return view('admin.branches.index', compact('branches', 'cities'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create()
    {
        $cities = City::all();
        $managers = User::whereHas('role', function($q) {
            $q->where('name', 'branch_manager');
        })->whereNull('branch_id')->get();
        
        return view('admin.branches.create', compact('cities', 'managers'));
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:branches',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city_id' => 'required|exists:cities,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'outlet_type' => 'required|in:retail,wholesale,hybrid',
            'operating_hours' => 'nullable|array',
            'pos_enabled' => 'boolean',
            'pos_terminal_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $branch = Branch::create($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch)
    {
        $branch->load([
            'city', 
            'users.role', 
            'manager', 
            'cashiers', 
            'deliveryStaff',
            'orders' => function($q) {
                $q->latest()->take(10);
            }
        ]);
        
        $stats = [
            'total_orders' => $branch->orders()->count(),
            'total_sales' => $branch->orders()->where('status', 'completed')->sum('total_amount'),
            'monthly_sales' => $branch->orders()
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),
            'today_sales' => $branch->todaySales(),
            'active_sessions' => $branch->activePosSessionsCount(),
            'total_products' => $branch->products()->count(),
        ];
        
        return view('admin.branches.show', compact('branch', 'stats'));
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Branch $branch)
    {
        $cities = City::all();
        $managers = User::whereHas('role', function($q) {
            $q->where('name', 'branch_manager');
        })->where(function($q) use ($branch) {
            $q->whereNull('branch_id')->orWhere('branch_id', $branch->id);
        })->get();
        
        return view('admin.branches.edit', compact('branch', 'cities', 'managers'));
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('branches')->ignore($branch->id)],
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city_id' => 'required|exists:cities,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'outlet_type' => 'required|in:retail,wholesale,hybrid',
            'operating_hours' => 'nullable|array',
            'pos_enabled' => 'boolean',
            'pos_terminal_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $branch->update($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Branch $branch)
    {
        // Check if branch has users
        if ($branch->users()->count() > 0) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Cannot delete branch with active users. Please reassign users first.');
        }

        // Check if branch has orders
        if ($branch->orders()->count() > 0) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Cannot delete branch with order history.');
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    /**
     * Toggle branch active status.
     */
    public function toggleStatus(Branch $branch)
    {
        $branch->update(['is_active' => !$branch->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Branch status updated successfully.',
            'is_active' => $branch->is_active
        ]);
    }

    /**
     * Assign manager to branch.
     */
    public function assignManager(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'manager_id' => 'required|exists:users,id'
        ]);

        $manager = User::findOrFail($validated['manager_id']);
        
        // Verify the user is a branch manager
        if (!$manager->isBranchManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Selected user is not a branch manager.'
            ]);
        }

        // Remove current manager from branch
        $currentManager = $branch->manager();
        if ($currentManager) {
            $currentManager->update(['branch_id' => null]);
        }

        // Assign new manager
        $manager->update(['branch_id' => $branch->id]);

        return response()->json([
            'success' => true,
            'message' => 'Manager assigned successfully.'
        ]);
    }
}