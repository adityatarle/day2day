<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Customer::withCount('orders');

        // Branch managers should only see customers who have orders in their branch
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $branchId = $user->branch_id;
            $query->whereHas('orders', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })->withCount(['orders' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }]);
        }

        // Search by name, email, or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * Display customers for the authenticated manager's branch.
     */
    public function branchIndex(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'No branch assigned to your account.');
        }

        $query = Customer::query()
            ->whereHas('orders', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })
            ->withCount(['orders' => function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            }]);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'address' => 'nullable|string|max:1000',
            'customer_type' => 'required|in:walk_in,regular,regular_wholesale,premium_wholesale,distributor,retailer',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Set defaults
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['credit_days'] = $validated['credit_days'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true; // Default to active

        $customer = Customer::create($validated);

        // Redirect based on user role
        $user = auth()->user();
        if ($user->hasRole('cashier')) {
            // For cashiers, redirect back to customer search with the new customer's name in search
            $redirectTo = request()->get('redirect_to', route('cashier.customers.search'));
            // Add search parameter to show the newly created customer
            $redirectTo .= '?search=' . urlencode($customer->name);
            return redirect($redirectTo)
                ->with('success', 'Customer "' . $customer->name . '" created successfully!');
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully!');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $user = auth()->user();

        // Route protection: branch managers can only view customers with orders in their branch
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $belongsToBranch = $customer->orders()->where('branch_id', $user->branch_id)->exists();
            if (!$belongsToBranch) {
                abort(403, 'Unauthorized');
            }
        }

        $customer->load(['orders' => function ($query) use ($user) {
            if ($user->hasRole('branch_manager') && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
            $query->latest()->take(10);
        }]);

        $totalSpent = $customer->orders->sum('total_amount');
        $orderCount = $customer->orders->count();

        return view('customers.show', compact('customer', 'totalSpent', 'orderCount'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        $user = auth()->user();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $belongsToBranch = $customer->orders()->where('branch_id', $user->branch_id)->exists();
            if (!$belongsToBranch) {
                abort(403, 'Unauthorized');
            }
        }

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $user = auth()->user();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $belongsToBranch = $customer->orders()->where('branch_id', $user->branch_id)->exists();
            if (!$belongsToBranch) {
                abort(403, 'Unauthorized');
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string|max:1000',
            'customer_type' => 'required|in:walk_in,regular,regular_wholesale,premium_wholesale,distributor,retailer',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Set defaults
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['credit_days'] = $validated['credit_days'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        $user = auth()->user();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $belongsToBranch = $customer->orders()->where('branch_id', $user->branch_id)->exists();
            if (!$belongsToBranch) {
                abort(403, 'Unauthorized');
            }
        }

        // Check if customer has any orders
        if ($customer->orders()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with existing orders.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully!');
    }

    /**
     * Display customer purchase history.
     */
    public function purchaseHistory(Customer $customer)
    {
        $user = auth()->user();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $belongsToBranch = $customer->orders()->where('branch_id', $user->branch_id)->exists();
            if (!$belongsToBranch) {
                abort(403, 'Unauthorized');
            }
        }

        $ordersQuery = $customer->orders()->with(['products', 'branch'])->latest();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $ordersQuery->where('branch_id', $user->branch_id);
        }

        $orders = $ordersQuery->paginate(20);

        return view('customers.purchase-history', compact('customer', 'orders'));
    }

    /**
     * Search customers for cashier interface.
     */
    public function cashierSearch(Request $request)
    {
        $user = auth()->user();
        $query = Customer::query();

        // Cashiers can see all customers, but branch managers see only their branch customers
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $branchId = $user->branch_id;
            $query->whereHas('orders', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        // Cashiers see all customers - no restriction

        // Search by name, email, or phone
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
            // Order search results by name
            $customers = $query->orderBy('name')->paginate(20);
        } else {
            // If no search query, show recent customers (ordered by most recent first)
            $customers = $query->orderBy('created_at', 'desc')->paginate(20);
        }

        return view('cashier.customers.search', compact('customers'));
    }
}