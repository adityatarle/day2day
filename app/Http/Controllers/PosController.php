<?php

namespace App\Http\Controllers;

use App\Models\PosSession;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\CashDenominationBreakdown;
use App\Services\UpiQrService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\BranchSaleProcessed;
use App\Events\BranchStockUpdated;

class PosController extends Controller
{
    /**
     * Start a new POS session.
     */
    public function startSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'terminal_id' => 'required|string',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        // Check if there's already an active session for this terminal
        $existingSession = PosSession::where('terminal_id', $request->terminal_id)
            ->where('branch_id', $request->branch_id)
            ->active()
            ->first();

        if ($existingSession) {
            return response()->json([
                'success' => false,
                'message' => 'There is already an active session for this terminal'
            ], 400);
        }

        $session = PosSession::create([
            'user_id' => auth()->id(),
            'branch_id' => $request->branch_id,
            'terminal_id' => $request->terminal_id,
            'opening_cash' => $request->opening_cash,
            'started_at' => now(),
            'status' => 'active',
        ]);

        $session->load(['user', 'branch']);

        return response()->json([
            'success' => true,
            'data' => $session,
            'message' => 'POS session started successfully'
        ], 201);
    }

    /**
     * Get current active session for user.
     */
    public function getCurrentSession(): JsonResponse
    {
        $session = PosSession::with(['user', 'branch.city'])
            ->where('user_id', auth()->id())
            ->active()
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active POS session found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $session,
            'message' => 'Active session retrieved successfully'
        ]);
    }

    /**
     * Process a sale through POS.
     */
    public function processSale(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.actual_weight' => 'nullable|numeric|min:0',
            'items.*.billed_weight' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,upi,credit',
            'discount_amount' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'amount_received' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'cash_denominations' => 'nullable|array',
            'cash_denominations.denomination_2000' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_500' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_200' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_100' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_50' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_20' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_10' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_5' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_2' => 'nullable|numeric|min:0',
            'cash_denominations.denomination_1' => 'nullable|numeric|min:0',
            'cash_denominations.coins' => 'nullable|numeric|min:0',
            'upi_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        // Get current session
        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active POS session found'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create customer if needed
            $customer = null;
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
            } elseif ($request->customer_name || $request->customer_phone) {
                $customer = Customer::create([
                    'name' => $request->customer_name ?? 'Walk-in Customer',
                    'phone' => $request->customer_phone,
                    'email' => null,
                    'address' => null,
                ]);
            }

            // Calculate totals based on weight
            $subtotal = 0;
            foreach ($request->items as $item) {
                // Use billed_weight if provided, otherwise use quantity
                $weight = $item['billed_weight'] ?? $item['actual_weight'] ?? $item['quantity'];
                $subtotal += $weight * $item['price'];
            }

            $discountAmount = $request->discount_amount ?? 0;
            $taxAmount = 0; // No GST
            $totalAmount = $subtotal - $discountAmount;

            $amountReceived = $request->amount_received ?? $totalAmount;
            // Determine payment status
            if ($request->payment_method === 'credit') {
                $paymentStatus = 'pending';
                $amountReceived = 0; // No immediate cash/card/upi received for credit
            } elseif ($amountReceived >= $totalAmount) {
                $paymentStatus = 'paid';
            } elseif ($amountReceived > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            // Create order
            $order = Order::create([
                'customer_id' => $customer?->id,
                'branch_id' => $session->branch_id,
                'pos_session_id' => $session->id,
                'order_number' => 'POS-' . now()->format('YmdHis') . '-' . $session->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'order_type' => 'pos',
                'created_by' => auth()->id(),
                'user_id' => auth()->id(),
                'order_date' => now(),
            ]);

            // Create order items
            foreach ($request->items as $item) {
                $actualWeight = $item['actual_weight'] ?? $item['quantity'];
                $billedWeight = $item['billed_weight'] ?? $item['quantity'];
                $totalPrice = $billedWeight * $item['price'];
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $totalPrice,
                    'actual_weight' => $actualWeight,
                    'billed_weight' => $billedWeight,
                    'adjustment_weight' => $actualWeight - $billedWeight,
                ]);

                // Update stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    // For count-based products (pcs), decrement by quantity
                    // For weight-based products (kg/gm), decrement by billed_weight
                    $stockDecrement = ($product->weight_unit === 'pcs') 
                        ? $item['quantity'] 
                        : ($item['billed_weight'] ?? $item['quantity']);
                    
                    // Update branch-specific stock
                    $product->branches()->updateExistingPivot($session->branch_id, [
                        'current_stock' => DB::raw('current_stock - ' . $stockDecrement)
                    ]);
                }
            }

            // Create payment record if amount was received
            $payment = null;
            $upiQrData = null;
            
            if ($amountReceived > 0) {
                // Generate UPI QR code if payment method is UPI
                if ($request->payment_method === 'upi' && $request->upi_id) {
                    $upiQrService = new UpiQrService();
                    $upiQrData = $upiQrService->generateUpiQrData(
                        $totalAmount,
                        $request->upi_id,
                        'Day2Day',
                        'Order Payment - ' . $order->order_number
                    );
                }

                $payment = Payment::create([
                    'type' => 'customer_payment',
                    'payable_id' => $order->id,
                    'payable_type' => Order::class,
                    'order_id' => $order->id,
                    'customer_id' => $customer?->id,
                    'branch_id' => $session->branch_id,
                    'amount' => min($amountReceived, $totalAmount),
                    'payment_method' => $request->payment_method,
                    'payment_type' => 'order_payment',
                    'status' => 'completed',
                    'reference_number' => $request->reference_number ?? ('POS-' . Str::upper(Str::random(8))),
                    'upi_qr_code' => $upiQrData ? $upiQrData['qr_code_svg'] : null,
                    'cash_denominations' => $request->cash_denominations ?? null,
                    'notes' => 'POS sale payment',
                    'user_id' => auth()->id(),
                    'payment_date' => now(),
                ]);

                // Create cash denomination breakdown if payment method is cash
                if ($request->payment_method === 'cash' && $request->cash_denominations) {
                    $denominations = $request->cash_denominations;
                    $totalCash = ($denominations['denomination_2000'] ?? 0) * 2000 +
                                 ($denominations['denomination_500'] ?? 0) * 500 +
                                 ($denominations['denomination_200'] ?? 0) * 200 +
                                 ($denominations['denomination_100'] ?? 0) * 100 +
                                 ($denominations['denomination_50'] ?? 0) * 50 +
                                 ($denominations['denomination_20'] ?? 0) * 20 +
                                 ($denominations['denomination_10'] ?? 0) * 10 +
                                 ($denominations['denomination_5'] ?? 0) * 5 +
                                 ($denominations['denomination_2'] ?? 0) * 2 +
                                 ($denominations['denomination_1'] ?? 0) * 1 +
                                 ($denominations['coins'] ?? 0);

                    CashDenominationBreakdown::create([
                        'payment_id' => $payment->id,
                        'denomination_2000' => $denominations['denomination_2000'] ?? 0,
                        'denomination_500' => $denominations['denomination_500'] ?? 0,
                        'denomination_200' => $denominations['denomination_200'] ?? 0,
                        'denomination_100' => $denominations['denomination_100'] ?? 0,
                        'denomination_50' => $denominations['denomination_50'] ?? 0,
                        'denomination_20' => $denominations['denomination_20'] ?? 0,
                        'denomination_10' => $denominations['denomination_10'] ?? 0,
                        'denomination_5' => $denominations['denomination_5'] ?? 0,
                        'denomination_2' => $denominations['denomination_2'] ?? 0,
                        'denomination_1' => $denominations['denomination_1'] ?? 0,
                        'coins' => $denominations['coins'] ?? 0,
                        'total_cash' => $totalCash,
                    ]);
                }
            }

            // Update session statistics
            $session->increment('total_transactions');
            $session->increment('total_sales', $totalAmount);

            // Prepare stock change payload
            $stockChanges = [
                'type' => 'decrement',
                'items' => array_map(function ($i) use ($session) {
                    return [
                        'product_id' => $i['product_id'],
                        'quantity' => $i['quantity'],
                        'branch_id' => $session->branch_id,
                    ];
                }, $request->items),
            ];

            DB::commit();

            $order->load(['customer', 'orderItems.product', 'payments']);

            // Broadcast events
            $freshSession = $session->fresh();
            event(new BranchSaleProcessed($order, $freshSession->branch_id, [
                'total_sales' => $freshSession->total_sales,
                'total_transactions' => $freshSession->total_transactions,
            ]));
            event(new BranchStockUpdated($freshSession->branch_id, $stockChanges));

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'session' => $session->fresh(),
                    'payment' => $payment,
                    'upi_qr_code' => $upiQrData,
                    'invoice_url' => route('orders.invoice', $order),
                ],
                'message' => 'Sale processed successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close current POS session.
     */
    public function closeSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'cash_breakdown' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active POS session found'
            ], 400);
        }

        $notes = $request->notes ? [$request->notes] : [];
        $normalizedBreakdown = PosSession::normalizeCashBreakdown($request->input('cash_breakdown'));
        $session->closeSession($request->closing_cash, $notes, $normalizedBreakdown);

        return response()->json([
            'success' => true,
            'data' => $session->fresh(),
            'message' => 'POS session closed successfully'
        ]);
    }

    /**
     * Get products with city-specific pricing for POS.
     */
    public function getProducts(Request $request): JsonResponse
    {
        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active POS session found'
            ], 400);
        }

        $cityId = $session->branch->city_id;
        
        $query = Product::with(['cityPricing' => function($q) use ($cityId) {
            $q->where('city_id', $cityId)->available()->effectiveOn();
        }]);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->active()->get();

        // Add city-specific pricing to each product
        $products->transform(function($product) use ($cityId) {
            $cityPrice = $product->getCityPrice($cityId);
            $product->city_price = $cityPrice;
            $product->is_available_in_city = $product->isAvailableInCity($cityId);
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Products retrieved successfully'
        ]);
    }

    /**
     * Get POS session history.
     */
    public function getSessionHistory(Request $request): JsonResponse
    {
        $query = PosSession::with(['user', 'branch.city'])
            ->where('user_id', auth()->id())
            ->orderBy('started_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $sessions = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $sessions,
            'message' => 'Session history retrieved successfully'
        ]);
    }

    /**
     * Generate UPI QR code.
     */
    public function generateUpiQr(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'upi_id' => 'required|string|max:255',
            'merchant_name' => 'nullable|string|max:255',
            'transaction_note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        try {
            $upiQrService = new UpiQrService();
            $qrData = $upiQrService->generateUpiQrData(
                $request->amount,
                $request->upi_id,
                $request->merchant_name ?? 'Day2Day',
                $request->transaction_note ?? 'POS Payment'
            );

            return response()->json([
                'success' => true,
                'data' => $qrData,
                'message' => 'UPI QR code generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales summary for current session.
     */
    public function getSessionSummary(): JsonResponse
    {
        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active POS session found'
            ], 400);
        }

        $orders = $session->orders()->with(['items.product'])->get();
        
        $summary = [
            'session_info' => $session,
            'total_transactions' => $orders->count(),
            'total_sales' => $orders->sum('total_amount'),
            'cash_sales' => $orders->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $orders->where('payment_method', 'card')->sum('total_amount'),
            'upi_sales' => $orders->where('payment_method', 'upi')->sum('total_amount'),
            'credit_sales' => $orders->where('payment_method', 'credit')->sum('total_amount'),
            'recent_orders' => $orders->take(10),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
            'message' => 'Session summary retrieved successfully'
        ]);
    }
}
