<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'orderItems.product', 'branch']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Search by order number or customer name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(20);
        $branches = Branch::all();
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        return view('orders.index', compact('orders', 'branches', 'statuses'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $products = Product::with(['branches'])->active()->get();
        $customers = Customer::all();
        $branches = Branch::all();

        return view('orders.create', compact('products', 'customers', 'branches'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'orderItems.product', 'branch']);
        
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        $products = Product::with(['branches'])->active()->get();
        $customers = Customer::all();
        $branches = Branch::all();

        return view('orders.edit', compact('order', 'products', 'customers', 'branches'));
    }

    /**
     * Display the order invoice.
     */
    public function invoice(Order $order)
    {
        $order->load(['customer', 'orderItems.product', 'branch']);
        
        return view('orders.invoice', compact('order'));
    }

    /**
     * Show the quick sale form.
     */
    public function quickSaleForm()
    {
        $products = Product::with(['branches'])->active()->get();
        $branches = Branch::all();

        return view('billing.quick-sale', compact('products', 'branches'));
    }

    /**
     * Show the wholesale form.
     */
    public function wholesaleForm()
    {
        $products = Product::with(['branches'])->active()->get();
        $branches = Branch::all();

        return view('billing.wholesale', compact('products', 'branches'));
    }

    /**
     * Display orders for the authenticated manager's branch.
     */
    public function branchIndex(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'No branch assigned to your account.');
        }

        $query = Order::with(['customer', 'orderItems.product', 'branch'])
            ->where('branch_id', $branch->id);

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Search by order number or customer name
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(20);

        // Limit branches dropdown to current branch for branch manager
        $branches = collect([$branch]);
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        return view('orders.index', compact('orders', 'branches', 'statuses'));
    }
}