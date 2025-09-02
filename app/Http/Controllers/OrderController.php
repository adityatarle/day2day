<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
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
        $query = Order::with(['customer', 'branch', 'user', 'orderItems.product']);

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by order type
        if ($request->has('order_type')) {
            $query->byType($request->order_type);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->byPaymentStatus($request->payment_status);
        }

        // Filter by branch
        if ($request->has('branch_id')) {
            $query->byBranch($request->branch_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_without:customer_id|string|max:255',
            'customer_phone' => 'required_without:customer_id|string|max:20',
            'customer_email' => 'nullable|email',
            'customer_address' => 'nullable|string',
            'order_type' => 'required|in:online,on_shop,wholesale',
            'payment_method' => 'required|in:cash,upi,card,cod,credit',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.actual_weight' => 'nullable|numeric|min:0',
            'items.*.billed_weight' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $branch = $user->branch;

            if (!$branch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User must be assigned to a branch'
                ], 400);
            }

            // Create or find customer
            $customer = null;
            if ($request->has('customer_id')) {
                $customer = Customer::find($request->customer_id);
            } else {
                $customer = Customer::firstOrCreate(
                    ['phone' => $request->customer_phone],
                    [
                        'name' => $request->customer_name,
                        'email' => $request->customer_email,
                        'address' => $request->customer_address,
                        'type' => $request->order_type === 'wholesale' ? 'wholesale' : 'retail'
                    ]
                );
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
                
                // Check stock availability
                $currentStock = $product->getCurrentStock($branch);
                if ($currentStock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Insufficient stock for {$product->name}. Available: {$currentStock} {$product->weight_unit}"
                    ], 400);
                }

                $actualWeight = $item['actual_weight'] ?? $item['quantity'];
                $billedWeight = $item['billed_weight'] ?? $actualWeight;
                $totalPrice = $billedWeight * $item['unit_price'];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                    'actual_weight' => $actualWeight,
                    'billed_weight' => $billedWeight,
                    'adjustment_weight' => $actualWeight - $billedWeight,
                ]);

                $subtotal += $totalPrice;

                // Update stock
                $product->branches()->updateExistingPivot($branch->id, [
                    'current_stock' => $currentStock - $item['quantity']
                ]);

                // Record stock movement
                // Note: You'll need to create a StockMovement model and record this
            }

            // Update order totals
            $order->subtotal = $subtotal;
            $order->tax_amount = $order->calculateTaxAmount();
            $order->total_amount = $subtotal + $order->tax_amount - $order->discount_amount;
            $order->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => $order->load(['customer', 'branch', 'user', 'orderItems.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'branch', 'user', 'orderItems.product', 'delivery', 'returns']);

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,confirmed,processing,ready,delivered,cancelled,returned',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string',
            'discount_amount' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update($request->only([
            'status', 'payment_status', 'notes', 'discount_amount'
        ]));

        // Update totals if discount changed
        if ($request->has('discount_amount')) {
            $order->updateTotals();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully',
            'data' => $order->load(['customer', 'branch', 'user', 'orderItems.product'])
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order)
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order cannot be cancelled in current status'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $order->update(['status' => 'cancelled']);

            // Restore stock
            foreach ($order->orderItems as $item) {
                $product = $item->product;
                $branch = $order->branch;
                $currentStock = $product->getCurrentStock($branch);
                
                $product->branches()->updateExistingPivot($branch->id, [
                    'current_stock' => $currentStock + $item->quantity
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order statistics.
     */
    public function getStatistics(Request $request)
    {
        $user = auth()->user();
        $branchId = $request->get('branch_id', $user->branch_id);

        $query = Order::query();
        if ($branchId) {
            $query->byBranch($branchId);
        }

        // Date range filter
        if ($request->has('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $statistics = [
            'total_orders' => $query->count(),
            'total_sales' => $query->sum('total_amount'),
            'pending_orders' => $query->byStatus('pending')->count(),
            'completed_orders' => $query->byStatus('delivered')->count(),
            'cancelled_orders' => $query->byStatus('cancelled')->count(),
            'online_orders' => $query->byType('online')->count(),
            'on_shop_orders' => $query->byType('on_shop')->count(),
            'wholesale_orders' => $query->byType('wholesale')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $statistics
        ]);
    }

    /**
     * Generate invoice for order.
     */
    public function generateInvoice(Order $order)
    {
        $order->load(['customer', 'branch', 'user', 'orderItems.product']);

        // Generate invoice data
        $invoice = [
            'invoice_number' => 'INV-' . $order->order_number,
            'order' => $order,
            'generated_at' => now(),
            'due_date' => now()->addDays(30),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $invoice
        ]);
    }
}