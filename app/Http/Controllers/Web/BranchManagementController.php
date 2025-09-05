<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BranchManagementController extends Controller
{
    /**
     * Display branch management page.
     */
    public function index()
    {
        $user = auth()->user();
        $branches = $user->getManageableBranches();
        
        // Add additional data for each branch
        $branches = $branches->map(function($branch) {
            $branch->manager_name = $branch->manager()->name ?? 'No Manager';
            $branch->total_staff = $branch->users()->count();
            $branch->today_sales = $branch->todaySales();
            $branch->active_pos_sessions = $branch->activePosSessionsCount();
            $branch->total_products = $branch->products()->count();
            return $branch;
        });
        
        return view('branches.index', compact('branches'));
    }

    /**
     * Show form to create new branch.
     */
    public function create()
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only super admin can create branches.');
        }
        
        $cities = City::active()->get();
        
        return view('branches.create', compact('cities'));
    }

    /**
     * Store a new branch.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only super admin can create branches.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city_id' => 'required|exists:cities,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'outlet_type' => 'required|in:restaurant,takeaway,delivery_only,hybrid',
            'operating_hours' => 'nullable|array',
            'pos_enabled' => 'boolean',
            'pos_terminal_id' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $branch = Branch::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'city_id' => $request->city_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'outlet_type' => $request->outlet_type,
            'operating_hours' => $request->operating_hours,
            'pos_enabled' => $request->boolean('pos_enabled', true),
            'pos_terminal_id' => $request->pos_terminal_id,
            'is_active' => true,
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Show branch details.
     */
    public function show(Branch $branch)
    {
        $user = auth()->user();
        
        if (!$user->canManageBranch($branch)) {
            abort(403, 'You do not have permission to view this branch.');
        }

        $branch->load([
            'city',
            'users.role',
            'orders' => function($query) {
                $query->latest()->take(10);
            }
        ]);

        $branchStats = [
            'total_staff' => $branch->users()->count(),
            'manager' => $branch->manager(),
            'cashiers_count' => $branch->cashiers()->count(),
            'delivery_staff_count' => $branch->deliveryStaff()->count(),
            'today_sales' => $branch->todaySales(),
            'month_sales' => $branch->orders()
                ->whereMonth('created_at', now()->month)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'total_orders' => $branch->orders()->count(),
            'active_pos_sessions' => $branch->activePosSessionsCount(),
            'inventory_items' => $branch->products()->count(),
            'low_stock_items' => $branch->products()
                ->wherePivot('current_stock', '<', 10)
                ->count(),
        ];

        return view('branches.show', compact('branch', 'branchStats'));
    }

    /**
     * Show form to edit branch.
     */
    public function edit(Branch $branch)
    {
        $user = auth()->user();
        
        if (!$user->canManageBranch($branch)) {
            abort(403, 'You do not have permission to edit this branch.');
        }

        $cities = City::active()->get();
        
        return view('branches.edit', compact('branch', 'cities'));
    }

    /**
     * Update branch information.
     */
    public function update(Request $request, Branch $branch)
    {
        $user = auth()->user();
        
        if (!$user->canManageBranch($branch)) {
            abort(403, 'You do not have permission to edit this branch.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:10', Rule::unique('branches')->ignore($branch->id)],
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city_id' => 'required|exists:cities,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'outlet_type' => 'required|in:restaurant,takeaway,delivery_only,hybrid',
            'operating_hours' => 'nullable|array',
            'pos_enabled' => 'boolean',
            'pos_terminal_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $branch->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'city_id' => $request->city_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'outlet_type' => $request->outlet_type,
            'operating_hours' => $request->operating_hours,
            'pos_enabled' => $request->boolean('pos_enabled'),
            'pos_terminal_id' => $request->pos_terminal_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Toggle branch active status.
     */
    public function toggleStatus(Branch $branch)
    {
        $user = auth()->user();
        
        if (!$user->canManageBranch($branch)) {
            return response()->json(['error' => 'You do not have permission to modify this branch.'], 403);
        }

        $branch->update(['is_active' => !$branch->is_active]);

        return response()->json([
            'success' => true,
            'status' => $branch->is_active,
            'message' => $branch->is_active ? 'Branch activated successfully.' : 'Branch deactivated successfully.'
        ]);
    }

    /**
     * Get branch inventory.
     */
    public function inventory(Branch $branch)
    {
        $user = auth()->user();
        
        if (!$user->canManageBranch($branch)) {
            abort(403, 'You do not have permission to view this branch inventory.');
        }

        $inventory = $branch->products()
            ->withPivot(['current_stock', 'selling_price', 'is_available_online'])
            ->orderBy('name')
            ->get();

        return view('branches.inventory', compact('branch', 'inventory'));
    }

    /**
     * Update branch inventory item.
     */
    public function updateInventoryItem(Request $request, Branch $branch, Product $product)
    {
        $user = auth()->user();
        
        if (!$user->canManageBranch($branch)) {
            return response()->json(['error' => 'You do not have permission to modify this branch inventory.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'current_stock' => 'required|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
            'is_available_online' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $branch->products()->updateExistingPivot($product->id, [
            'current_stock' => $request->current_stock,
            'selling_price' => $request->selling_price,
            'is_available_online' => $request->boolean('is_available_online'),
        ]);

        return response()->json(['success' => true, 'message' => 'Inventory updated successfully.']);
    }

    /**
     * Assign manager to branch.
     */
    public function assignManager(Request $request, Branch $branch)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            return response()->json(['error' => 'Only super admin can assign branch managers.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newManager = User::findOrFail($request->user_id);
        
        // Check if user is a branch manager
        if (!$newManager->isBranchManager()) {
            return response()->json(['error' => 'Selected user is not a branch manager.'], 422);
        }

        // Remove current manager if exists
        $currentManager = $branch->manager();
        if ($currentManager) {
            $currentManager->update(['branch_id' => null]);
        }

        // Assign new manager
        $newManager->update(['branch_id' => $branch->id]);

        return response()->json(['success' => true, 'message' => 'Branch manager assigned successfully.']);
    }

    /**
     * Get branch performance data.
     */
    public function getPerformanceData(Branch $branch)
    {
        $user = auth()->user();
        
        if (!$user->canManageBranch($branch)) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $data = [
            'daily_sales' => $branch->orders()
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as sales')
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'hourly_sales_today' => $branch->orders()
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders, SUM(total_amount) as sales')
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
            'top_products' => $branch->orders()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->selectRaw('products.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as total_sales')
                ->where('orders.status', 'completed')
                ->where('orders.created_at', '>=', now()->subDays(30))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sales')
                ->limit(10)
                ->get(),
        ];

        return response()->json($data);
    }
}