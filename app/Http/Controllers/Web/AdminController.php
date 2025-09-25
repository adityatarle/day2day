<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\PurchaseEntry;
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

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
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

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting the current user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
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

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully!');
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

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully!');
    }

    /**
     * Delete branch
     */
    public function deleteBranch(Branch $branch)
    {
        // Check if branch has users or orders
        if ($branch->users()->count() > 0 || $branch->orders()->count() > 0) {
            return redirect()->route('admin.branches.index')->with('error', 'Cannot delete branch with existing users or orders!');
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted successfully!');
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

    /**
     * Display all purchase entries across all branches
     */
    public function purchaseEntries()
    {
        $purchaseEntries = PurchaseEntry::with(['branch', 'purchaseOrder', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.purchase-entries.index', compact('purchaseEntries'));
    }

    /**
     * Display system settings
     */
    public function settings()
    {
        $branches = Branch::all();
        $roles = Role::all();
        $users = User::with(['role', 'branch'])->get();
        
        // Get system statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_branches' => Branch::count(),
            'active_branches' => Branch::where('is_active', true)->count(),
            'total_products' => \App\Models\Product::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_vendors' => \App\Models\Vendor::count(),
        ];

        // Load existing settings if they exist
        $existingSettings = $this->loadSettings();

        return view('admin.settings.index', compact('branches', 'roles', 'users', 'stats', 'existingSettings'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
            'default_currency' => 'required|string|max:3',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'auto_approve_orders' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
        ]);

        // Store settings in config or database
        // For now, we'll use Laravel's config system
        $settings = [
            'company_name' => $request->company_name,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
            'company_address' => $request->company_address,
            'default_currency' => $request->default_currency,
            'tax_rate' => $request->tax_rate,
            'low_stock_threshold' => $request->low_stock_threshold,
            'auto_approve_orders' => $request->boolean('auto_approve_orders'),
            'email_notifications' => $request->boolean('email_notifications'),
            'sms_notifications' => $request->boolean('sms_notifications'),
        ];

        // Update config file or store in database
        // For this implementation, we'll store in a JSON file
        file_put_contents(
            storage_path('app/settings.json'),
            json_encode($settings, JSON_PRETTY_PRINT)
        );

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully!');
    }

    /**
     * Display security settings
     */
    public function security()
    {
        $users = User::with(['role', 'branch'])->get();
        $roles = Role::all();
        
        return view('admin.settings.security', compact('users', 'roles'));
    }

    /**
     * Display analytics dashboard
     */
    public function analytics()
    {
        $stats = [
            'total_sales' => \App\Models\Order::sum('total_amount'),
            'total_orders' => \App\Models\Order::count(),
            'total_customers' => \App\Models\Customer::count(),
            'total_products' => \App\Models\Product::count(),
            'total_vendors' => \App\Models\Vendor::count(),
            'total_branches' => Branch::count(),
        ];

        // Get recent activity
        $recentOrders = \App\Models\Order::with(['customer', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentUsers = User::with(['role', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.settings.analytics', compact('stats', 'recentOrders', 'recentUsers'));
    }

    /**
     * Load existing settings from storage
     */
    private function loadSettings()
    {
        $settingsPath = storage_path('app/settings.json');
        
        if (file_exists($settingsPath)) {
            $settings = json_decode(file_get_contents($settingsPath), true);
            return $settings ?: [];
        }
        
        // Return default settings
        return [
            'company_name' => 'Day2Day Business',
            'company_email' => 'admin@day2day.com',
            'company_phone' => '+1-234-567-8900',
            'company_address' => '123 Business Street, City, State 12345',
            'default_currency' => 'USD',
            'tax_rate' => '8.5',
            'low_stock_threshold' => '10',
            'auto_approve_orders' => false,
            'email_notifications' => true,
            'sms_notifications' => false,
        ];
    }
}