<?php

namespace App\Http\Controllers;

use App\Models\PosSession;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
            'payment_method' => 'required|in:cash,card,upi,credit',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
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

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }

            $discountAmount = $request->discount_amount ?? 0;
            $taxAmount = $request->tax_amount ?? ($subtotal * 0.18); // Default 18% GST
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

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
                'status' => 'completed',
                'order_type' => 'pos',
                'created_by' => auth()->id(),
            ]);

            // Create order items
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                ]);

                // Update stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    // Update branch-specific stock
                    $product->branches()->updateExistingPivot($session->branch_id, [
                        'current_stock' => DB::raw('current_stock - ' . $item['quantity'])
                    ]);
                }
            }

            // Update session statistics
            $session->increment('total_transactions');
            $session->increment('total_sales', $totalAmount);

            DB::commit();

            $order->load(['customer', 'items.product']);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'session' => $session->fresh()
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
        $session->closeSession($request->closing_cash, $notes);

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
