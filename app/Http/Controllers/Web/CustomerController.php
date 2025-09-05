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
        $query = Customer::withCount('orders');

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
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $customer->load(['orders' => function ($query) {
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
        return view('customers.edit', compact('customer'));
    }

    /**
     * Display customer purchase history.
     */
    public function purchaseHistory(Customer $customer)
    {
        $orders = $customer->orders()
            ->with(['products', 'branch'])
            ->latest()
            ->paginate(20);

        return view('customers.purchase-history', compact('customer', 'orders'));
    }
}