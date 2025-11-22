<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use App\Services\InventoryService;
use App\Services\OrderWorkflowService;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        // Check authentication
        if (!auth()->check()) {
            return redirect()->route('login')->withErrors(['error' => 'Please log in to create orders.']);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_without:customer_id|string|max:255',
            'customer_phone' => 'required_without:customer_id|string|max:20',
            'customer_email' => 'nullable|email',
            'customer_address' => 'nullable|string',
            'order_type' => 'required|in:online,on_shop,wholesale',
            'payment_method' => 'required|in:cash,upi,card,cod,credit',
            'branch_id' => 'nullable|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            
            if (!$user) {
                DB::rollBack();
                return redirect()->route('login')->withErrors(['error' => 'Please log in to create orders.']);
            }
            
            // Use branch from request or user's branch
            $branch = null;
            if ($request->has('branch_id') && $request->branch_id) {
                $branch = Branch::find($request->branch_id);
            } else {
                $branch = $user->branch;
            }

            if (!$branch) {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors(['error' => 'User must be assigned to a branch or select a branch.'])
                    ->withInput();
            }

            // Create or find customer
            $customer = null;
            if ($request->has('customer_id') && $request->customer_id) {
                $customer = Customer::find($request->customer_id);
                if (!$customer) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['customer_id' => 'Selected customer not found.'])
                        ->withInput();
                }
            } else {
                // For walk-in customers, create a default customer if no phone provided
                if (!$request->customer_phone || trim($request->customer_phone) === '') {
                    // Create a walk-in customer with a default phone
                    $customer = Customer::firstOrCreate(
                        ['phone' => '0000000000'],
                        [
                            'name' => $request->customer_name ?? 'Walk-in Customer',
                            'email' => $request->customer_email ?? null,
                            'address' => $request->customer_address ?? null,
                            'type' => $request->order_type === 'wholesale' ? 'wholesale' : 'retail'
                        ]
                    );
                } else {
                    $customer = Customer::firstOrCreate(
                        ['phone' => $request->customer_phone],
                        [
                            'name' => $request->customer_name ?? 'Walk-in Customer',
                            'email' => $request->customer_email ?? null,
                            'address' => $request->customer_address ?? null,
                            'type' => $request->order_type === 'wholesale' ? 'wholesale' : 'retail'
                        ]
                    );
                }
            }

            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'order_type' => $request->order_type,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cod' ? 'pending' : 'paid',
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount' => 0,
                'notes' => $request->notes,
                'order_date' => now(),
            ]);

            $subtotal = 0;

            // Create order items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['error' => "Product not found: {$item['product_id']}"])
                        ->withInput();
                }
                
                // Check stock availability
                $currentStock = $product->getCurrentStock($branch);
                if ($currentStock < $item['quantity']) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['error' => "Insufficient stock for {$product->name}. Available: {$currentStock} {$product->weight_unit}"])
                        ->withInput();
                }

                $totalPrice = $item['quantity'] * $item['unit_price'];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);

                $subtotal += $totalPrice;

                // Update stock directly (simpler approach for web orders)
                try {
                    $currentStock = $product->getCurrentStock($branch);
                    $newStock = max(0, $currentStock - $item['quantity']);
                    $product->updateBranchStock($branch->id, $newStock);
                    
                    // Create stock movement record if StockMovement model exists
                    if (class_exists(\App\Models\StockMovement::class)) {
                        try {
                            \App\Models\StockMovement::create([
                                'product_id' => $product->id,
                                'branch_id' => $branch->id,
                                'type' => 'sale',
                                'quantity' => -$item['quantity'],
                                'unit_price' => $item['unit_price'],
                                'notes' => "Order #{$orderNumber}",
                                'user_id' => $user->id,
                            ]);
                        } catch (\Exception $e) {
                            \Log::warning('Stock movement creation failed: ' . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Stock update failed: ' . $e->getMessage());
                    return redirect()->back()
                        ->withErrors(['error' => "Failed to update stock for {$product->name}: " . $e->getMessage()])
                        ->withInput();
                }
            }

            // Update order totals - reload orderItems relationship first
            $order->load('orderItems.product');
            $order->subtotal = $subtotal;
            
            // Calculate tax - handle if GST rates don't exist
            try {
                $order->tax_amount = $order->calculateTaxAmount();
            } catch (\Exception $e) {
                // If GST calculation fails, set tax to 0
                \Log::warning('Tax calculation failed: ' . $e->getMessage());
                $order->tax_amount = 0;
            }
            
            $order->total_amount = $subtotal + $order->tax_amount - $order->discount_amount;
            $order->save();

            // Transition to pending status if workflow service is available
            try {
                $workflowService = app(OrderWorkflowService::class);
                $workflowService->transitionOrder($order, 'pending', $user, 'Order created');
            } catch (\Exception $e) {
                \Log::warning('Order workflow transition failed: ' . $e->getMessage());
            }

            // Send notifications if service is available
            try {
                $notificationService = app(OrderNotificationService::class);
                $notificationService->notifyStatusChange($order, 'draft', 'pending', $user);
            } catch (\Exception $e) {
                \Log::warning('Order notification failed: ' . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order creation failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()])
                ->withInput();
        }
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
        $user = auth()->user();
        $branch = $user->branch;
        
        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'No branch assigned to your account.');
        }
        
        // Get products for the user's branch
        $products = Product::with(['branches' => function($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '>', 0);
            }])
            ->whereHas('branches', function($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '>', 0);
            })
            ->active()
            ->get();
        
        // Get customers - show all customers for cashiers
        $customers = Customer::active()
            ->orderBy('name')
            ->get();

        return view('billing.quick-sale', compact('products', 'customers', 'branch'));
    }
    
    /**
     * Store a quick sale order.
     */
    public function quickSaleStore(Request $request)
    {
        // Check authentication
        if (!auth()->check()) {
            return redirect()->route('login')->withErrors(['error' => 'Please log in to create orders.']);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|in:cash,card,upi',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $branch = $user->branch;

            if (!$branch) {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors(['error' => 'User must be assigned to a branch.'])
                    ->withInput();
            }

            // Create or find customer
            $customer = null;
            if ($request->has('customer_id') && $request->customer_id) {
                $customer = Customer::find($request->customer_id);
            } else {
                // Create walk-in customer
                $customer = Customer::firstOrCreate(
                    ['phone' => '0000000000'],
                    [
                        'name' => 'Walk-in Customer',
                        'type' => 'retail'
                    ]
                );
            }

            // Generate order number
            $orderNumber = 'QS-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }
            $taxAmount = 0; // No GST
            $totalAmount = $subtotal + $taxAmount;

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'order_type' => 'on_shop',
                'status' => 'completed',
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'order_date' => now(),
            ]);

            // Create order items and update stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['error' => "Product not found: {$item['product_id']}"])
                        ->withInput();
                }
                
                // Check stock availability
                $currentStock = $product->getCurrentStock($branch);
                if ($currentStock < $item['quantity']) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['error' => "Insufficient stock for {$product->name}. Available: {$currentStock} {$product->weight_unit}"])
                        ->withInput();
                }

                $totalPrice = $item['quantity'] * $item['unit_price'];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);

                // Update stock
                try {
                    $newStock = max(0, $currentStock - $item['quantity']);
                    $product->updateBranchStock($branch->id, $newStock);
                    
                    // Create stock movement record
                    if (class_exists(\App\Models\StockMovement::class)) {
                        try {
                            \App\Models\StockMovement::create([
                                'product_id' => $product->id,
                                'branch_id' => $branch->id,
                                'type' => 'sale',
                                'quantity' => -$item['quantity'],
                                'unit_price' => $item['unit_price'],
                                'notes' => "Quick Sale #{$orderNumber}",
                                'user_id' => $user->id,
                            ]);
                        } catch (\Exception $e) {
                            \Log::warning('Stock movement creation failed: ' . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Stock update failed: ' . $e->getMessage());
                    return redirect()->back()
                        ->withErrors(['error' => "Failed to update stock for {$product->name}"])
                        ->withInput();
                }
            }

            // Update order totals
            $order->load('orderItems.product');
            $order->subtotal = $subtotal;
            $order->tax_amount = $taxAmount;
            $order->total_amount = $totalAmount;
            $order->save();

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Quick sale completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quick sale creation failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to complete sale: ' . $e->getMessage()])
                ->withInput();
        }
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