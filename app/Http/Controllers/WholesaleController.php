<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\WholesalePricing;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WholesaleController extends Controller
{
    /**
     * Get wholesale pricing tiers.
     */
    public function getPricingTiers(Request $request)
    {
        $query = WholesalePricing::with(['product', 'customer']);

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by customer type
        if ($request->has('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        $pricingTiers = $query->active()->orderBy('min_quantity')->get();

        return response()->json([
            'status' => 'success',
            'data' => $pricingTiers
        ]);
    }

    /**
     * Create wholesale pricing tier.
     */
    public function createPricingTier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_type' => 'required|in:regular_wholesale,premium_wholesale,distributor,retailer',
            'min_quantity' => 'required|numeric|min:1',
            'max_quantity' => 'nullable|numeric|gt:min_quantity',
            'wholesale_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pricingTier = WholesalePricing::create([
            'product_id' => $request->product_id,
            'customer_id' => $request->customer_id,
            'customer_type' => $request->customer_type,
            'min_quantity' => $request->min_quantity,
            'max_quantity' => $request->max_quantity,
            'wholesale_price' => $request->wholesale_price,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Wholesale pricing tier created successfully',
            'data' => $pricingTier->load(['product', 'customer'])
        ], 201);
    }

    /**
     * Calculate wholesale pricing for order.
     */
    public function calculateWholesalePricing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::find($request->customer_id);
        $calculatedItems = [];
        $totalAmount = 0;
        $totalSavings = 0;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $quantity = $item['quantity'];

            // Find applicable wholesale pricing
            $wholesalePricing = $this->findApplicableWholesalePricing($product, $customer, $quantity);
            
            $regularPrice = $product->selling_price * $quantity;
            $wholesalePrice = $wholesalePricing ? 
                $wholesalePricing->wholesale_price * $quantity : 
                $regularPrice;

            // Apply additional discount if applicable
            $finalPrice = $wholesalePrice;
            if ($wholesalePricing && $wholesalePricing->discount_percentage > 0) {
                $discount = ($wholesalePrice * $wholesalePricing->discount_percentage) / 100;
                $finalPrice = $wholesalePrice - $discount;
            }

            $savings = $regularPrice - $finalPrice;
            $totalSavings += $savings;

            $calculatedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->selling_price,
                'wholesale_unit_price' => $wholesalePricing ? $wholesalePricing->wholesale_price : $product->selling_price,
                'regular_total' => $regularPrice,
                'wholesale_total' => $wholesalePrice,
                'discount_percentage' => $wholesalePricing ? $wholesalePricing->discount_percentage : 0,
                'final_total' => $finalPrice,
                'savings' => $savings,
                'pricing_tier' => $wholesalePricing ? [
                    'id' => $wholesalePricing->id,
                    'customer_type' => $wholesalePricing->customer_type,
                    'min_quantity' => $wholesalePricing->min_quantity,
                    'max_quantity' => $wholesalePricing->max_quantity,
                ] : null,
            ];

            $totalAmount += $finalPrice;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $calculatedItems,
                'summary' => [
                    'total_regular_amount' => array_sum(array_column($calculatedItems, 'regular_total')),
                    'total_wholesale_amount' => $totalAmount,
                    'total_savings' => $totalSavings,
                    'discount_percentage' => array_sum(array_column($calculatedItems, 'regular_total')) > 0 ? 
                        round(($totalSavings / array_sum(array_column($calculatedItems, 'regular_total'))) * 100, 2) : 0,
                ],
            ]
        ]);
    }

    /**
     * Create wholesale order.
     */
    public function createWholesaleOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,upi,card,credit,bank_transfer',
            'payment_terms' => 'nullable|string',
            'credit_days' => 'nullable|integer|min:0',
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
            $customer = Customer::find($request->customer_id);
            $branch = Branch::find($request->branch_id);
            $inventoryService = new InventoryService();

            // Generate order number
            $orderNumber = 'WS-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create wholesale order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'order_type' => 'wholesale',
                'status' => 'confirmed',
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'credit' ? 'pending' : 'paid',
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount' => 0,
                'order_date' => now(),
                'payment_terms' => $request->payment_terms,
                'credit_days' => $request->credit_days,
                'notes' => $request->notes,
            ]);

            $subtotal = 0;

            // Process order items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                // Check stock availability
                $currentStock = $product->getCurrentStock($branch->id);
                if ($currentStock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Insufficient stock for {$product->name}. Available: {$currentStock} {$product->weight_unit}"
                    ], 400);
                }

                $totalPrice = $item['quantity'] * $item['unit_price'];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                    'actual_weight' => $item['quantity'],
                    'billed_weight' => $item['quantity'],
                    'adjustment_weight' => 0,
                ]);

                $subtotal += $totalPrice;

                // Auto-update stock using InventoryService
                $stockUpdated = $inventoryService->updateStockAfterSale($orderItem);
                
                if (!$stockUpdated) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Failed to update stock for {$product->name}"
                    ], 500);
                }
            }

            // Update order totals
            $order->subtotal = $subtotal;
            $order->tax_amount = $order->calculateTaxAmount();
            $order->total_amount = $subtotal + $order->tax_amount - $order->discount_amount;
            $order->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Wholesale order created successfully',
                'data' => $order->load(['customer', 'branch', 'user', 'orderItems.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create wholesale order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wholesale order history.
     */
    public function getWholesaleOrders(Request $request)
    {
        $query = Order::with(['customer', 'branch', 'user', 'orderItems.product'])
                     ->where('order_type', 'wholesale');

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by branch
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
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
     * Get wholesale customer analysis.
     */
    public function getCustomerAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Order::with(['customer', 'orderItems.product'])
                     ->where('order_type', 'wholesale')
                     ->whereBetween('order_date', [$request->start_date, $request->end_date]);

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        $orders = $query->get();

        $customerAnalysis = $orders->groupBy('customer_id')->map(function ($customerOrders, $customerId) {
            $customer = $customerOrders->first()->customer;
            $totalOrders = $customerOrders->count();
            $totalAmount = $customerOrders->sum('total_amount');
            $averageOrderValue = $totalAmount / $totalOrders;
            
            $productsPurchased = $customerOrders->flatMap(function ($order) {
                return $order->orderItems;
            })->groupBy('product_id')->map(function ($items, $productId) {
                return [
                    'product_id' => $productId,
                    'product_name' => $items->first()->product->name,
                    'total_quantity' => $items->sum('quantity'),
                    'total_amount' => $items->sum('total_price'),
                    'order_count' => $items->count(),
                ];
            })->values();

            return [
                'customer_id' => $customerId,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_type' => $customer->customer_type,
                'total_orders' => $totalOrders,
                'total_amount' => $totalAmount,
                'average_order_value' => $averageOrderValue,
                'products_purchased' => $productsPurchased,
                'last_order_date' => $customerOrders->max('order_date'),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'customer_analysis' => $customerAnalysis,
                'summary' => [
                    'total_customers' => $customerAnalysis->count(),
                    'total_orders' => $orders->count(),
                    'total_revenue' => $orders->sum('total_amount'),
                    'average_order_value' => $orders->avg('total_amount'),
                ],
            ]
        ]);
    }

    /**
     * Generate wholesale invoice.
     */
    public function generateInvoice(Order $order)
    {
        if ($order->order_type !== 'wholesale') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order is not a wholesale order'
            ], 422);
        }

        $order->load(['customer', 'branch', 'user', 'orderItems.product', 'payments']);

        // Calculate detailed pricing breakdown
        $itemsBreakdown = $order->orderItems->map(function ($item) {
            $product = $item->product;
            return [
                'product_name' => $product->name,
                'product_code' => $product->code,
                'quantity' => $item->quantity,
                'unit' => $product->weight_unit,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'hsn_code' => $product->hsn_code ?? '',
            ];
        });

        $invoiceData = [
            'order' => $order,
            'items' => $itemsBreakdown,
            'totals' => [
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'discount_amount' => $order->discount_amount,
                'total_amount' => $order->total_amount,
                'paid_amount' => $order->payments->where('payment_type', 'order_payment')->sum('amount'),
                'balance_amount' => $order->total_amount - $order->payments->where('payment_type', 'order_payment')->sum('amount'),
            ],
            'payment_terms' => $order->payment_terms,
            'credit_days' => $order->credit_days,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $invoiceData
        ]);
    }

    /**
     * Update wholesale pricing tier.
     */
    public function updatePricingTier(Request $request, WholesalePricing $pricingTier)
    {
        $validator = Validator::make($request->all(), [
            'min_quantity' => 'required|numeric|min:1',
            'max_quantity' => 'nullable|numeric|gt:min_quantity',
            'wholesale_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pricingTier->update([
            'min_quantity' => $request->min_quantity,
            'max_quantity' => $request->max_quantity,
            'wholesale_price' => $request->wholesale_price,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Wholesale pricing tier updated successfully',
            'data' => $pricingTier->load(['product', 'customer'])
        ]);
    }

    /**
     * Delete wholesale pricing tier.
     */
    public function deletePricingTier(WholesalePricing $pricingTier)
    {
        $pricingTier->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Wholesale pricing tier deleted successfully'
        ]);
    }

    /**
     * Find applicable wholesale pricing for customer and quantity.
     */
    private function findApplicableWholesalePricing(Product $product, Customer $customer, float $quantity): ?WholesalePricing
    {
        // First, try to find customer-specific pricing
        $customerSpecific = WholesalePricing::where('product_id', $product->id)
                                          ->where('customer_id', $customer->id)
                                          ->where('min_quantity', '<=', $quantity)
                                          ->where(function ($query) use ($quantity) {
                                              $query->whereNull('max_quantity')
                                                    ->orWhere('max_quantity', '>=', $quantity);
                                          })
                                          ->where('is_active', true)
                                          ->orderBy('min_quantity', 'desc')
                                          ->first();

        if ($customerSpecific) {
            return $customerSpecific;
        }

        // If no customer-specific pricing, find by customer type
        return WholesalePricing::where('product_id', $product->id)
                              ->whereNull('customer_id')
                              ->where('customer_type', $customer->customer_type)
                              ->where('min_quantity', '<=', $quantity)
                              ->where(function ($query) use ($quantity) {
                                  $query->whereNull('max_quantity')
                                        ->orWhere('max_quantity', '>=', $quantity);
                              })
                              ->where('is_active', true)
                              ->orderBy('min_quantity', 'desc')
                              ->first();
    }

    /**
     * Get wholesale performance metrics.
     */
    public function getPerformanceMetrics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Order::where('order_type', 'wholesale')
                     ->whereBetween('order_date', [$request->start_date, $request->end_date]);

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $orders = $query->with(['orderItems.product'])->get();

        $metrics = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'average_order_value' => $orders->avg('total_amount'),
            'total_quantity_sold' => $orders->flatMap->orderItems->sum('quantity'),
            'unique_customers' => $orders->pluck('customer_id')->unique()->count(),
            'top_selling_products' => $orders->flatMap->orderItems
                ->groupBy('product_id')
                ->map(function ($items, $productId) {
                    return [
                        'product_id' => $productId,
                        'product_name' => $items->first()->product->name,
                        'total_quantity' => $items->sum('quantity'),
                        'total_revenue' => $items->sum('total_price'),
                        'order_count' => $items->count(),
                    ];
                })
                ->sortByDesc('total_revenue')
                ->take(10)
                ->values(),
            'payment_status_breakdown' => $orders->groupBy('payment_status')->map->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $metrics
        ]);
    }
}