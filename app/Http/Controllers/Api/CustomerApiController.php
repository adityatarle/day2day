<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerApiController extends Controller
{
    /**
     * Get list of customers
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

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by customer_type
        if ($request->has('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $customers = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => CustomerResource::collection($customers),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    /**
     * Get customer by ID
     */
    public function show(Customer $customer)
    {
        $user = auth()->user();

        // Route protection: branch managers can only view customers with orders in their branch
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $belongsToBranch = $customer->orders()->where('branch_id', $user->branch_id)->exists();
            if (!$belongsToBranch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this customer'
                ], 403);
            }
        }

        $customer->load(['orders' => function ($query) use ($user) {
            if ($user->hasRole('branch_manager') && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
            $query->latest()->take(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => new CustomerResource($customer),
        ]);
    }

    /**
     * Create a new customer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'address' => 'nullable|string|max:1000',
            'customer_type' => 'required|in:walk_in,regular,regular_wholesale,premium_wholesale,distributor,retailer',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'customer_type' => $request->customer_type,
            'credit_limit' => $request->credit_limit ?? 0,
            'credit_days' => $request->credit_days ?? 0,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => new CustomerResource($customer)
        ], 201);
    }

    /**
     * Update customer
     */
    public function update(Request $request, Customer $customer)
    {
        $user = auth()->user();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $belongsToBranch = $customer->orders()->where('branch_id', $user->branch_id)->exists();
            if (!$belongsToBranch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this customer'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string|max:1000',
            'customer_type' => 'sometimes|required|in:walk_in,regular,regular_wholesale,premium_wholesale,distributor,retailer',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer->update($request->only([
            'name', 'phone', 'email', 'address', 'customer_type',
            'credit_limit', 'credit_days', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => new CustomerResource($customer->fresh())
        ]);
    }

    /**
     * Get customer purchase history
     */
    public function purchaseHistory(Request $request, Customer $customer)
    {
        $user = auth()->user();
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $belongsToBranch = $customer->orders()->where('branch_id', $user->branch_id)->exists();
            if (!$belongsToBranch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this customer'
                ], 403);
            }
        }

        $ordersQuery = $customer->orders()->with(['orderItems.product', 'branch'])->latest();
        
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $ordersQuery->where('branch_id', $user->branch_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $ordersQuery->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $ordersQuery->whereDate('order_date', '<=', $request->end_date);
        }

        $orders = $ordersQuery->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => new CustomerResource($customer),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total_amount' => (float) $order->total_amount,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'order_date' => $order->order_date?->toISOString(),
                        'items_count' => $order->orderItems->count(),
                    ];
                }),
                'summary' => [
                    'total_orders' => $orders->total(),
                    'total_spent' => (float) $customer->orders()->sum('total_amount'),
                    'average_order_value' => (float) $customer->orders()->avg('total_amount'),
                ],
            ],
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Search customers (for quick lookup)
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $query = Customer::query();

        // Branch managers can only see customers with orders in their branch
        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $branchId = $user->branch_id;
            $query->whereHas('orders', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $search = $request->query;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });

        $customers = $query->orderBy('name')->limit(20)->get();

        return response()->json([
            'success' => true,
            'data' => CustomerResource::collection($customers),
        ]);
    }
}
