<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerOrderApiController extends Controller
{
    /**
     * Create a customer order (for mobile and web)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_without:customer_id|string|max:255',
            'customer_phone' => 'required_without:customer_id|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'delivery_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,upi,card,cod',
            'notes' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $branch = Branch::findOrFail($request->branch_id);

            // Create or find customer
            $customer = null;
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
            } else {
                $customer = Customer::firstOrCreate(
                    ['phone' => $request->customer_phone],
                    [
                        'name' => $request->customer_name,
                        'email' => $request->customer_email,
                        'address' => $request->customer_address,
                        'type' => 'retail',
                    ]
                );
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                // Check stock availability
                $branchProduct = $product->branches()->where('branch_id', $branch->id)->first();
                $currentStock = $branchProduct?->pivot?->current_stock ?? 0;
                
                if ($currentStock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product->name}. Available: {$currentStock} {$product->weight_unit}"
                    ], 400);
                }

                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discountAmount = 0;
            $taxAmount = 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'user_id' => null, // Customer orders don't have a user
                'order_type' => 'online',
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cod' ? 'pending' : 'paid',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'delivery_address' => $request->delivery_address,
                'delivery_phone' => $request->delivery_phone,
                'delivery_instructions' => $request->delivery_instructions,
                'order_date' => now(),
            ]);

            // Create order items and update stock
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit' => 'kg',
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);

                // Update stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    DB::table('product_branches')
                        ->where('product_id', $item['product_id'])
                        ->where('branch_id', $branch->id)
                        ->decrement('current_stock', $item['quantity']);
                }
            }

            DB::commit();

            $order->load(['customer', 'branch', 'orderItems.product']);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $totalAmount,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer orders
     */
    public function index(Request $request)
    {
        $customerId = $request->get('customer_id');
        $phone = $request->get('phone');

        if (!$customerId && !$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Customer ID or phone number is required'
            ], 400);
        }

        $query = Order::with(['branch', 'orderItems.product'])
            ->where('order_type', 'online');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } elseif ($phone) {
            $customer = Customer::where('phone', $phone)->first();
            if ($customer) {
                $query->where('customer_id', $customer->id);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'branch' => [
                        'id' => $order->branch->id,
                        'name' => $order->branch->name,
                        'address' => $order->branch->address,
                    ],
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'order_date' => $order->order_date?->toISOString(),
                    'delivery_address' => $order->delivery_address,
                ];
            }),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get order details
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'branch', 'orderItems.product']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => [
                    'id' => $order->customer->id,
                    'name' => $order->customer->name,
                    'phone' => $order->customer->phone,
                ],
                'branch' => [
                    'id' => $order->branch->id,
                    'name' => $order->branch->name,
                    'address' => $order->branch->address,
                    'phone' => $order->branch->phone,
                ],
                'items' => $order->orderItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'code' => $item->product->code,
                        ],
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total_price' => (float) $item->total_price,
                    ];
                }),
                'subtotal' => (float) $order->subtotal,
                'discount_amount' => (float) $order->discount_amount,
                'tax_amount' => (float) $order->tax_amount,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'delivery_address' => $order->delivery_address,
                'delivery_phone' => $order->delivery_phone,
                'delivery_instructions' => $order->delivery_instructions,
                'order_date' => $order->order_date?->toISOString(),
            ],
        ]);
    }
}
