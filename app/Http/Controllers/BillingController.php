<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\GstRate;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    /**
     * Generate invoice for order.
     */
    public function generateInvoice(Order $order)
    {
        $order->load(['customer', 'branch', 'user', 'orderItems.product.gstRates', 'payments']);

        // Calculate tax breakdown
        $taxBreakdown = $this->calculateTaxBreakdown($order);
        
        // Generate invoice data
        $invoiceData = [
            'invoice_number' => $order->adjusted_invoice_number ?? $order->order_number,
            'invoice_date' => $order->adjustment_date ?? $order->order_date,
            'original_invoice' => $order->order_number,
            'order' => $order,
            'customer' => $order->customer,
            'branch' => $order->branch,
            'items' => $order->orderItems->map(function ($item) {
                return [
                    'product_name' => $item->product->name,
                    'product_code' => $item->product->code,
                    'hsn_code' => $item->product->hsn_code ?? '',
                    'quantity' => $item->quantity,
                    'unit' => $item->product->weight_unit,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'actual_weight' => $item->actual_weight,
                    'billed_weight' => $item->billed_weight,
                    'adjustment_weight' => $item->adjustment_weight,
                    'gst_rate' => $item->product->gstRates->first()?->rate ?? 0,
                ];
            }),
            'totals' => [
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'discount_amount' => $order->discount_amount,
                'adjustment_amount' => $order->adjustment_amount ?? 0,
                'total_amount' => $order->total_amount,
            ],
            'tax_breakdown' => $taxBreakdown,
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'payment_method' => $payment->payment_method,
                    'amount' => $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'payment_date' => $payment->payment_date,
                    'reference_number' => $payment->reference_number,
                ];
            }),
            'payment_status' => $order->payment_status,
            'balance_amount' => $order->total_amount - $order->payments->where('payment_type', 'order_payment')->sum('amount'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $invoiceData
        ]);
    }

    /**
     * Quick billing for on-shop sales.
     */
    public function quickBilling(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.actual_weight' => 'nullable|numeric|min:0',
            'items.*.custom_price' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,upi,card',
            'discount_amount' => 'nullable|numeric|min:0',
            'print_invoice' => 'boolean',
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
                    'message' => 'User is not assigned to any branch'
                ], 400);
            }

            // Create or find customer
            $customer = null;
            if ($request->customer_name || $request->customer_phone) {
                $customer = Customer::firstOrCreate([
                    'phone' => $request->customer_phone
                ], [
                    'name' => $request->customer_name ?? 'Walk-in Customer',
                    'type' => 'walk_in',
                    'customer_type' => 'walk_in',
                    'is_active' => true,
                ]);
            }

            // Generate order number
            $orderNumber = 'OS-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer?->id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'order_type' => 'on_shop',
                'status' => 'completed',
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount' => 0,
                'order_date' => now(),
            ]);

            $subtotal = 0;
            $inventoryService = new InventoryService();

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

                // Use custom price if provided, otherwise branch-specific price
                $unitPrice = $item['custom_price'] ?? 
                           $product->branches()->where('branches.id', $branch->id)->first()?->pivot?->selling_price ?? 
                           $product->selling_price;

                $actualWeight = $item['actual_weight'] ?? $item['quantity'];
                $billedWeight = $item['billed_weight'] ?? $item['quantity'];
                $totalPrice = $billedWeight * $unitPrice;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'actual_weight' => $actualWeight,
                    'billed_weight' => $billedWeight,
                    'adjustment_weight' => $actualWeight - $billedWeight,
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

            // Update order totals (no GST)
            $order->subtotal = $subtotal;
            $order->tax_amount = 0; // No GST
            $order->total_amount = $subtotal - $order->discount_amount;
            $order->save();

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'customer_id' => $customer?->id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'amount' => $order->total_amount,
                'payment_method' => $request->payment_method,
                'payment_type' => 'order_payment',
                'payment_date' => now(),
                'status' => 'completed',
                'reference_number' => 'PAY-' . strtoupper(Str::random(8)),
            ]);

            DB::commit();

            $response = [
                'status' => 'success',
                'message' => 'Quick billing completed successfully',
                'data' => $order->load(['customer', 'branch', 'user', 'orderItems.product', 'payments'])
            ];

            // Add invoice data if print is requested
            if ($request->print_invoice) {
                $response['invoice_data'] = $this->generateInvoice($order)->getData()->data;
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete quick billing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process online order payment.
     */
    public function processOnlinePayment(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:upi,card,cod',
            'payment_reference' => 'nullable|string|max:255',
            'payment_amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($order->order_type !== 'online') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order is not an online order'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'branch_id' => $order->branch_id,
                'user_id' => auth()->id(),
                'amount' => $request->payment_amount,
                'payment_method' => $request->payment_method,
                'payment_type' => 'order_payment',
                'payment_date' => now(),
                'status' => $request->payment_method === 'cod' ? 'pending' : 'completed',
                'reference_number' => $request->payment_reference ?? 'PAY-' . strtoupper(Str::random(8)),
            ]);

            // Update order payment status
            $totalPaid = $order->payments()->where('payment_type', 'order_payment')->sum('amount') + $request->payment_amount;
            
            if ($totalPaid >= $order->total_amount) {
                $order->update(['payment_status' => 'paid']);
            } else {
                $order->update(['payment_status' => 'partial']);
            }

            // If payment is successful and not COD, confirm the order
            if ($request->payment_method !== 'cod') {
                $order->update(['status' => 'confirmed']);
                
                // Process stock reduction for online orders
                $inventoryService = new InventoryService();
                foreach ($order->orderItems as $orderItem) {
                    $inventoryService->updateStockAfterSale($orderItem);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment' => $payment,
                    'order' => $order->load(['payments']),
                    'balance_amount' => $order->total_amount - $totalPaid,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate bulk invoice for multiple orders.
     */
    public function generateBulkInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $orders = Order::with(['customer', 'branch', 'orderItems.product', 'payments'])
                      ->whereIn('id', $request->order_ids)
                      ->get();

        // Validate orders belong to same customer if customer_id is provided
        if ($request->customer_id) {
            $invalidOrders = $orders->where('customer_id', '!=', $request->customer_id);
            if ($invalidOrders->isNotEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'All orders must belong to the same customer'
                ], 422);
            }
        }

        $bulkInvoiceNumber = 'BULK-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        
        $bulkInvoiceData = [
            'bulk_invoice_number' => $bulkInvoiceNumber,
            'invoice_date' => now(),
            'customer' => $orders->first()->customer,
            'orders' => $orders->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'order_date' => $order->order_date,
                    'order_type' => $order->order_type,
                    'subtotal' => $order->subtotal,
                    'tax_amount' => $order->tax_amount,
                    'discount_amount' => $order->discount_amount,
                    'total_amount' => $order->total_amount,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                        ];
                    }),
                ];
            }),
            'summary' => [
                'total_orders' => $orders->count(),
                'total_subtotal' => $orders->sum('subtotal'),
                'total_tax' => $orders->sum('tax_amount'),
                'total_discount' => $orders->sum('discount_amount'),
                'grand_total' => $orders->sum('total_amount'),
                'total_paid' => $orders->flatMap->payments->where('payment_type', 'order_payment')->sum('amount'),
                'balance_amount' => $orders->sum('total_amount') - $orders->flatMap->payments->where('payment_type', 'order_payment')->sum('amount'),
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $bulkInvoiceData
        ]);
    }

    /**
     * Process partial payment.
     */
    public function processPartialPayment(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,upi,card,bank_transfer',
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
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

            $totalPaid = $order->payments()->where('payment_type', 'order_payment')->sum('amount');
            $remainingAmount = $order->total_amount - $totalPaid;

            if ($request->amount > $remainingAmount) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Payment amount exceeds remaining balance of ₹{$remainingAmount}"
                ], 422);
            }

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'branch_id' => $order->branch_id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_type' => 'order_payment',
                'payment_date' => now(),
                'status' => 'completed',
                'reference_number' => $request->reference_number ?? 'PAY-' . strtoupper(Str::random(8)),
                'notes' => $request->notes,
            ]);

            // Update order payment status
            $newTotalPaid = $totalPaid + $request->amount;
            
            if ($newTotalPaid >= $order->total_amount) {
                $order->update(['payment_status' => 'paid']);
            } else {
                $order->update(['payment_status' => 'partial']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Partial payment processed successfully',
                'data' => [
                    'payment' => $payment,
                    'remaining_balance' => $order->total_amount - $newTotalPaid,
                    'payment_status' => $order->payment_status,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process partial payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate tax breakdown for order.
     */
    private function calculateTaxBreakdown(Order $order): array
    {
        $breakdown = [];
        $totalTaxableAmount = 0;
        $totalTaxAmount = 0;

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $gstRate = $product->gstRates->first();
            
            if ($gstRate) {
                $taxableAmount = $item->total_price;
                $taxAmount = ($taxableAmount * $gstRate->rate) / 100;
                
                if (!isset($breakdown[$gstRate->rate])) {
                    $breakdown[$gstRate->rate] = [
                        'rate' => $gstRate->rate,
                        'taxable_amount' => 0,
                        'tax_amount' => 0,
                        'items' => [],
                    ];
                }
                
                $breakdown[$gstRate->rate]['taxable_amount'] += $taxableAmount;
                $breakdown[$gstRate->rate]['tax_amount'] += $taxAmount;
                $breakdown[$gstRate->rate]['items'][] = [
                    'product_name' => $product->name,
                    'amount' => $taxableAmount,
                ];

                $totalTaxableAmount += $taxableAmount;
                $totalTaxAmount += $taxAmount;
            }
        }

        return [
            'breakdown' => array_values($breakdown),
            'total_taxable_amount' => $totalTaxableAmount,
            'total_tax_amount' => $totalTaxAmount,
        ];
    }

    /**
     * Get billing summary for a period.
     */
    public function getBillingSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'order_type' => 'nullable|in:online,on_shop,wholesale',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Order::with(['orderItems.product', 'payments'])
                     ->whereBetween('order_date', [$request->start_date, $request->end_date]);

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->order_type) {
            $query->where('order_type', $request->order_type);
        }

        $orders = $query->get();

        $summary = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'total_tax_collected' => $orders->sum('tax_amount'),
            'total_discounts' => $orders->sum('discount_amount'),
            'total_adjustments' => $orders->sum('adjustment_amount'),
            'by_order_type' => $orders->groupBy('order_type')->map(function ($typeOrders, $type) {
                return [
                    'type' => $type,
                    'count' => $typeOrders->count(),
                    'revenue' => $typeOrders->sum('total_amount'),
                    'average_order_value' => $typeOrders->avg('total_amount'),
                ];
            }),
            'by_payment_method' => $orders->groupBy('payment_method')->map(function ($methodOrders, $method) {
                return [
                    'method' => $method,
                    'count' => $methodOrders->count(),
                    'amount' => $methodOrders->sum('total_amount'),
                ];
            }),
            'payment_status_breakdown' => $orders->groupBy('payment_status')->map->count(),
            'daily_revenue' => $orders->groupBy(function ($order) {
                return $order->order_date->format('Y-m-d');
            })->map(function ($dayOrders, $date) {
                return [
                    'date' => $date,
                    'orders' => $dayOrders->count(),
                    'revenue' => $dayOrders->sum('total_amount'),
                ];
            })->values(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    /**
     * Get pending payments report.
     */
    public function getPendingPayments(Request $request)
    {
        $query = Order::with(['customer', 'branch'])
                     ->where('payment_status', 'pending');

        // Filter by branch
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by customer type
        if ($request->has('customer_type')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_type', $request->customer_type);
            });
        }

        // Filter by overdue
        if ($request->has('overdue_only') && $request->overdue_only) {
            $query->where('created_at', '<', now()->subDays(30)); // 30 days overdue
        }

        $pendingOrders = $query->orderBy('order_date', 'desc')->get();

        $summary = [
            'total_pending_orders' => $pendingOrders->count(),
            'total_pending_amount' => $pendingOrders->sum('total_amount'),
            'overdue_orders' => $pendingOrders->where('created_at', '<', now()->subDays(30))->count(),
            'overdue_amount' => $pendingOrders->where('created_at', '<', now()->subDays(30))->sum('total_amount'),
            'by_customer_type' => $pendingOrders->groupBy('customer.customer_type')->map(function ($customerOrders, $type) {
                return [
                    'customer_type' => $type,
                    'count' => $customerOrders->count(),
                    'amount' => $customerOrders->sum('total_amount'),
                ];
            }),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'pending_orders' => $pendingOrders,
                'summary' => $summary,
            ]
        ]);
    }
}