<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Events\BranchSaleProcessed;
use App\Events\BranchStockUpdated;
use App\Notifications\OrderCreated;

class PosWebController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index()
    {
        $user = auth()->user();
        $branch = $user->branch;
        
        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not assigned to any branch');
        }

        // Debug: Log branch status
        \Log::info('POS Index - Branch: ' . $branch->id . ', POS Enabled: ' . ($branch->pos_enabled ? 'Yes' : 'No'));
        
        if (!$branch->pos_enabled) {
            return redirect()->route('dashboard')
                ->with('error', 'POS is not enabled for your branch');
        }

        $currentSession = PosSession::where('user_id', $user->id)->active()->first();
        
        if (!$currentSession) {
            return redirect()->route('pos.start-session')
                ->with('error', 'Please start a POS session first');
        }
        
        $customers = Customer::active()->get();
        
        // Get available products for this branch with proper unit information
        $products = Product::whereHas('branches', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('is_active', true)
        ->with(['branches' => function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        }])
        ->get()
        ->map(function($product) use ($branch) {
            $branchProduct = $product->branches->first();
            $stock = $branchProduct?->pivot?->current_stock ?? 0;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category ?? 'Uncategorized',
                'selling_price' => (float)($branchProduct?->pivot?->selling_price ?? $product->selling_price),
                'current_stock' => (float)$stock,
                'weight_unit' => $product->weight_unit ?? 'kg',
                'bill_by' => $product->bill_by ?? 'weight',
                'selectedUnit' => $product->weight_unit ?? 'kg',
                'quantity' => 0,
            ];
        });
        
        // Debug: Log session status
        \Log::info('POS Index - User: ' . $user->id . ', Session: ' . ($currentSession ? $currentSession->id : 'None'));
        
        // Use unified POS view
        return view('pos.unified', compact('branch', 'currentSession', 'customers', 'products'));
    }

    /**
     * Display the POS session manager.
     */
    public function sessionManager()
    {
        $user = auth()->user();
        $branch = $user->branch;
        
        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not assigned to any branch');
        }

        $currentSession = $user->currentPosSession();
        
        // Get today's statistics
        $todayStats = [
            'total_orders' => Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->count(),
            'total_sales' => Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total_amount'),
            'active_sessions' => PosSession::where('branch_id', $branch->id)
                ->where('status', 'active')
                ->count(),
            'avg_order_value' => Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->avg('total_amount') ?? 0,
        ];

        // Get recent sessions
        $recentSessions = PosSession::where('user_id', $user->id)
            ->with(['user', 'branch'])
            ->latest()
            ->limit(5)
            ->get();

        return view('pos.session-manager', compact('currentSession', 'todayStats', 'recentSessions'));
    }

    /**
     * Show session start form.
     */
    public function startSession()
    {
        $user = auth()->user();
        $branch = $user->branch;
        
        if (!$branch || !$branch->pos_enabled) {
            return redirect()->route('dashboard')
                ->with('error', 'POS is not available');
        }

        // Check for existing active session
        $existingSession = PosSession::where('user_id', $user->id)->active()->first();
        
        if ($existingSession) {
            return redirect()->route('pos.index')
                ->with('info', 'You already have an active POS session');
        }

        // Prefill opening cash with previous day's closing cash if available
        $previousClosingCash = PosSession::where('user_id', $user->id)
            ->where('status', 'closed')
            ->orderBy('ended_at', 'desc')
            ->value('closing_cash');

        return view('pos.start-session', [
            'branch' => $branch,
            'previousClosingCash' => $previousClosingCash,
        ]);
    }

    /**
     * Process session start.
     */
    public function processStartSession(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|string',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $branch = $user->branch;

        // Check for existing session on this terminal
        $existingSession = PosSession::where('terminal_id', $request->terminal_id)
            ->where('branch_id', $branch->id)
            ->active()
            ->first();

        if ($existingSession) {
            return back()->withErrors(['terminal_id' => 'This terminal already has an active session']);
        }

        // Get handled_by from session or request
        $handledBy = $request->session()->get('handled_by') ?? $request->handled_by ?? $user->name;

        $session = PosSession::create([
            'user_id' => $user->id,
            'handled_by' => $handledBy,
            'branch_id' => $branch->id,
            'terminal_id' => $request->terminal_id,
            'opening_cash' => $request->opening_cash,
            'started_at' => now(),
            'status' => 'active',
        ]);

        // Clear the handled_by from session after creating the POS session
        $request->session()->forget('handled_by');

        return redirect()->route('pos.index')
            ->with('success', 'POS session started successfully');
    }

    /**
     * Show session close form.
     */
    public function closeSession()
    {
        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return redirect()->route('pos.index')
                ->with('error', 'No active session found');
        }

        $expectedCash = $session->calculateExpectedCash();
        
        // Get cash ledger breakdown
        $cashSales = $session->orders()
            ->where('payment_method', 'cash')
            ->sum('total_amount');
        
        $cashTakes = $session->cashLedgerEntries()
            ->where('entry_type', 'take')
            ->sum('amount');
        
        $cashGives = $session->cashLedgerEntries()
            ->where('entry_type', 'give')
            ->sum('amount');
        
        // Get recent cash ledger entries for display
        $cashLedgerEntries = $session->cashLedgerEntries()
            ->orderBy('entry_date', 'desc')
            ->get();
        
        $breakdown = [
            'opening_cash' => $session->opening_cash,
            'cash_sales' => $cashSales,
            'cash_takes' => $cashTakes,
            'cash_gives' => $cashGives,
            'expected_cash' => $expectedCash,
        ];
        
        return view('pos.close-session', compact('session', 'expectedCash', 'breakdown', 'cashLedgerEntries'));
    }

    /**
     * Process session close.
     */
    public function processCloseSession(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'cash_breakdown' => 'nullable',
        ]);

        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return redirect()->route('pos.index')
                ->with('error', 'No active session found');
        }

        $notes = $request->notes ? [$request->notes] : [];
        $session->closeSession(
            $request->closing_cash,
            $notes,
            PosSession::normalizeCashBreakdown($request->input('cash_breakdown'))
        );

        return redirect()->route('pos.index')
            ->with('success', 'POS session closed successfully');
    }

    /**
     * Show sales interface.
     */
    public function sales()
    {
        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return redirect()->route('pos.start-session')
                ->with('error', 'Please start a POS session first');
        }

        $cityId = $session->branch->city_id;
        $products = Product::with(['cityPricing' => function($q) use ($cityId) {
            $q->where('city_id', $cityId)->available()->effectiveOn();
        }])->active()->get();

        // Add city-specific pricing
        $products->transform(function($product) use ($cityId) {
            $product->city_price = $product->getCityPrice($cityId);
            $product->is_available_in_city = $product->isAvailableInCity($cityId);
            return $product;
        });

        $customers = Customer::active()->get();
        
        return view('pos.sales', compact('session', 'products', 'customers'));
    }

    /**
     * Show session history.
     */
    public function sessionHistory()
    {
        $sessions = PosSession::with(['user', 'branch.city'])
            ->where('user_id', auth()->id())
            ->orderBy('started_at', 'desc')
            ->paginate(15);

        return view('pos.history', compact('sessions'));
    }

    /**
     * Show new sale interface.
     */
    public function sale()
    {
        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return redirect()->route('pos.start-session')
                ->with('error', 'Please start a POS session first');
        }

        $user = auth()->user();
        $branch = $user->branch;
        $customers = Customer::active()->get();
        
        // Get available products for this branch
        $products = Product::whereHas('branches', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('is_active', true)
        ->with(['branches' => function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        }])
        ->get()
        ->map(function($product) use ($branch) {
            $branchProduct = $product->branches->first();
            $stock = $branchProduct?->pivot?->current_stock ?? 0;
            
            // Convert stock to kg for display
            $stockInKg = $stock;
            if ($product->weight_unit === 'gm') {
                $stockInKg = $stock / 1000;
            } elseif ($product->weight_unit === 'pcs') {
                // For pieces, keep as is but show as "pcs"
                $stockInKg = $stock;
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category ?? 'Uncategorized',
                'selling_price' => $branchProduct?->pivot?->selling_price ?? $product->selling_price,
                'current_stock' => $stock,
                'current_stock_kg' => $stockInKg,
                'weight_unit' => $product->weight_unit,
                'city_price' => $branchProduct?->pivot?->selling_price ?? $product->selling_price,
                'is_available_in_city' => true,
            ];
        });

        return view('pos.sale', compact('session', 'products', 'customers', 'branch'));
    }

    /**
     * Get products for POS (API endpoint).
     */
    public function getProducts()
    {
        $user = auth()->user();
        $branch = $user->branch;
        
        if (!$branch) {
            return response()->json(['success' => false, 'message' => 'No branch assigned']);
        }

        $products = Product::whereHas('branches', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('is_active', true)
        ->with(['branches' => function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        }])
        ->get()
        ->map(function($product) use ($branch) {
            $branchProduct = $product->branches->first();
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category ?? 'Uncategorized',
                'selling_price' => $branchProduct?->pivot?->selling_price ?? $product->selling_price,
                'current_stock' => $branchProduct?->pivot?->current_stock ?? 0,
                'city_price' => $branchProduct?->pivot?->selling_price ?? $product->selling_price,
                'is_available_in_city' => true,
            ];
        });

        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * Prepare order and store in session for payment.
     */
    public function prepareOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'nullable|numeric|min:0',
                'items.*.price' => 'nullable|numeric|min:0',
                'items.*.total_price' => 'nullable|numeric|min:0',
                'items.*.unit' => 'nullable|string',
                'subtotal' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0.01',
                'amount_received' => 'nullable|numeric|min:0',
                'return_amount' => 'nullable|numeric|min:0',
                'customer_id' => 'nullable|exists:customers,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Generate order token
        $orderToken = \Str::random(32);
        
        // Store order data in cache (more reliable than session)
        $orderData = [
            'order_data' => $validated,
            'created_at' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
        ];
        
        // Store in cache for 30 minutes (1800 seconds)
        Cache::put('pending_order_' . $orderToken, $orderData, now()->addMinutes(30));
        
        \Log::info('Order session created in cache', [
            'token' => $orderToken,
            'user_id' => auth()->id(),
            'total' => $validated['total'],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'order_token' => $orderToken,
                'total' => $validated['total']
            ]
        ]);
    }

    /**
     * Show payment page.
     */
    public function payment(Request $request)
    {
        $orderToken = $request->query('order_token');
        
        if (!$orderToken) {
            return redirect()->route('pos.index')
                ->with('error', 'Invalid payment link');
        }

        // Get order data from cache
        $orderData = Cache::get('pending_order_' . $orderToken);
        
        \Log::info('Payment page access', [
            'token' => $orderToken,
            'user_id' => auth()->id(),
            'cache_exists' => $orderData !== null,
            'cache_user_id' => $orderData['user_id'] ?? null,
        ]);
        
        if (!$orderData || $orderData['user_id'] !== auth()->id()) {
            \Log::warning('Order session invalid on payment page', [
                'token' => $orderToken,
                'user_id' => auth()->id(),
                'cache_exists' => $orderData !== null,
                'cache_user_id' => $orderData['user_id'] ?? null,
            ]);
            
            return redirect()->route('pos.index')
                ->with('error', 'Order session expired or invalid. Please create a new order from the POS page.');
        }
        
        // Refresh cache to extend its lifetime when accessing payment page
        Cache::put('pending_order_' . $orderToken, $orderData, now()->addMinutes(30));

        $user = auth()->user();
        $branch = $user->branch;
        $customer = $orderData['order_data']['customer_id'] 
            ? \App\Models\Customer::find($orderData['order_data']['customer_id'])
            : null;

        return view('pos.payment', [
            'orderToken' => $orderToken,
            'orderData' => $orderData['order_data'],
            'customer' => $customer,
            'branch' => $branch,
            'currentSession' => $user->currentPosSession(),
        ]);
    }

    /**
     * Process payment and create order.
     */
    public function processPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_token' => 'required|string',
                'payment_method' => 'required|in:cash,card,upi,credit',
                'amount_received' => 'required|numeric|min:0',
                'upi_id' => 'required_if:payment_method,upi|nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Validation failed: ';
            foreach ($errors as $field => $messages) {
                $errorMessage .= $field . ': ' . implode(', ', $messages) . ' ';
            }
            return response()->json([
                'success' => false,
                'message' => trim($errorMessage),
                'errors' => $errors
            ], 422);
        }

        $orderToken = $validated['order_token'];
        $sessionData = Cache::get('pending_order_' . $orderToken);
        
        if (!$sessionData || $sessionData['user_id'] !== auth()->id()) {
            // Log for debugging
            \Log::warning('Payment processing - Order session invalid', [
                'token' => $orderToken,
                'user_id' => auth()->id(),
                'cache_exists' => $sessionData !== null,
                'cache_user_id' => $sessionData['user_id'] ?? null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Order session expired or invalid. Please try again from the POS page.'
            ], 400);
        }

        // Enforce customer for credit payments
        $storedOrderData = $sessionData['order_data'];
        if ($validated['payment_method'] === 'credit' && empty($storedOrderData['customer_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Customer is required for credit payments.'
            ], 422);
        }

        // Merge payment data with order data
        $orderData = array_merge($storedOrderData, [
            'payment_method' => $validated['payment_method'],
            'amount_received' => $validated['amount_received'],
            'return_amount' => max($validated['amount_received'] - $storedOrderData['total'], 0),
            'upi_id' => $validated['upi_id'] ?? null,
        ]);

        try {
            $user = auth()->user();
            $session = PosSession::where('user_id', $user->id)->active()->first();
            
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'No active POS session'], 400);
            }

            DB::beginTransaction();

            $subtotal = $orderData['subtotal'] ?? collect($orderData['items'])->sum(function($item) {
                return $item['total_price'] ?? ($item['quantity'] * ($item['unit_price'] ?? $item['price'] ?? 0));
            });
            
            $discountAmount = $orderData['discount'] ?? 0;
            $taxAmount = $orderData['tax'] ?? 0;
            $totalAmount = $orderData['total'] ?? ($subtotal - $discountAmount + $taxAmount);

            // Create order
            $order = Order::create([
                'order_number' => 'POS-' . time() . '-' . rand(1000, 9999),
                'customer_id' => $orderData['customer_id'] ?? null,
                'branch_id' => $user->branch_id,
                'pos_session_id' => $session->id,
                'user_id' => $user->id,
                'created_by' => $user->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $orderData['payment_method'] ?? 'cash',
                'payment_status' => ($orderData['payment_method'] ?? 'cash') === 'credit' ? 'pending' : 'paid',
                'amount_received' => ($orderData['payment_method'] ?? 'cash') === 'credit' 
                    ? ($orderData['amount_received'] ?? 0) 
                    : ($orderData['amount_received'] ?? $totalAmount),
                'change_amount' => $orderData['return_amount'] ?? 0,
                'status' => 'completed',
                'order_date' => now(),
            ]);

            // Create order items and update stock (simplified vs API)
            foreach ($orderData['items'] as $item) {
                $quantity = $item['quantity'] ?? 0;
                $unit = $item['unit'] ?? 'kg';
                $unitPrice = $item['unit_price'] ?? $item['price'] ?? 0;
                $totalPrice = $item['total_price'] ?? ($quantity * $unitPrice);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $stockDecrement = $quantity;
                    if ($product->bill_by === 'weight') {
                        if ($unit === 'gram') {
                            $stockDecrement = $quantity / 1000;
                        }
                    } else {
                        if ($unit === 'dozen') {
                            $stockDecrement = $quantity * 12;
                        }
                    }
                    
                    DB::table('product_branches')
                        ->where('product_id', $item['product_id'])
                        ->where('branch_id', $user->branch_id)
                        ->decrement('current_stock', $stockDecrement);
                }
            }

            // Create payment record if amount received > 0 or method is credit
            $amountReceived = $orderData['amount_received'] ?? 0;
            $customerId = $orderData['customer_id'] ?? null;
            $payment = null;

            if ($amountReceived > 0 || ($orderData['payment_method'] ?? 'cash') === 'credit') {
                $payment = Payment::create([
                    'type' => 'customer_payment',
                    'payable_id' => $order->id,
                    'payable_type' => Order::class,
                    'order_id' => $order->id,
                    'customer_id' => $customerId,
                    'branch_id' => $session->branch_id,
                    'amount' => min($amountReceived, $totalAmount),
                    'payment_method' => $orderData['payment_method'],
                    'payment_type' => 'order_payment',
                    'status' => ($orderData['payment_method'] ?? 'cash') === 'credit' ? 'pending' : 'completed',
                    'reference_number' => 'POS-' . Str::upper(Str::random(8)),
                    'upi_qr_code' => null,
                    'cash_denominations' => null,
                    'notes' => 'POS sale payment',
                    'user_id' => auth()->id(),
                    'payment_date' => now(),
                ]);
            }

            // Update session stats
            $session->increment('total_transactions');
            $session->increment('total_sales', $totalAmount);

            // Prepare stock change payload
            $stockChanges = [
                'type' => 'decrement',
                'items' => array_map(function ($i) use ($user) {
                    return [
                        'product_id' => $i['product_id'],
                        'quantity' => $i['quantity'],
                        'branch_id' => $user->branch_id,
                    ];
                }, $orderData['items']),
            ];

            DB::commit();

            // Clear cache
            Cache::forget('pending_order_' . $orderToken);

            // Broadcast events and notifications (same style as API)
            $freshSession = $session->fresh();
            event(new BranchSaleProcessed($order, $freshSession->branch_id, [
                'total_sales' => $freshSession->total_sales,
                'total_transactions' => $freshSession->total_transactions,
            ]));
            event(new BranchStockUpdated($freshSession->branch_id, $stockChanges));

            $recipients = \App\Models\User::where('branch_id', $freshSession->branch_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('name', ['branch_manager', 'cashier']);
                })
                ->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new OrderCreated($order));
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $totalAmount,
                    'payment_method' => $orderData['payment_method'] ?? 'cash',
                    'amount_received' => $orderData['amount_received'] ?? $totalAmount,
                    'return_amount' => $orderData['return_amount'] ?? 0,
                    'invoice_url' => route('orders.invoice', $order->id)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a sale (API endpoint).
     */
    public function processSale(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'nullable|numeric|min:0',
                'items.*.price' => 'nullable|numeric|min:0',
                'items.*.total_price' => 'nullable|numeric|min:0',
                'items.*.unit' => 'nullable|string',
                'payment_method' => 'required|in:cash,card,upi,credit',
                'discount' => 'nullable|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'subtotal' => 'nullable|numeric|min:0',
                'total' => 'nullable|numeric|min:0',
                'amount_received' => 'nullable|numeric|min:0',
                'return_amount' => 'nullable|numeric|min:0',
                'customer_id' => 'nullable|exists:customers,id',
                'reference_number' => 'nullable|string|max:100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $user = auth()->user();
        $session = PosSession::where('user_id', $user->id)->active()->first();
        
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active POS session']);
        }

        try {
            DB::beginTransaction();

            // Use provided totals or calculate from items
            $subtotal = $request->input('subtotal') ?? collect($request->items)->sum(function($item) {
                return $item['total_price'] ?? ($item['quantity'] * ($item['unit_price'] ?? $item['price'] ?? 0));
            });
            
            // Handle discount (can be fixed amount or percentage)
            $discountAmount = 0;
            if ($request->has('discount')) {
                $discountAmount = is_numeric($request->discount) ? $request->discount : 0;
            } elseif ($request->has('discount_amount')) {
                $discountAmount = $request->discount_amount ?? 0;
            }
            
            // Handle tax (can be fixed amount or percentage)
            $taxAmount = 0;
            if ($request->has('tax')) {
                $taxAmount = is_numeric($request->tax) ? $request->tax : 0;
            } elseif ($request->has('tax_amount')) {
                $taxAmount = $request->tax_amount ?? 0;
            }
            
            $totalAmount = $request->input('total') ?? ($subtotal - $discountAmount + $taxAmount);

            // Create order
            $order = Order::create([
                'order_number' => 'POS-' . time() . '-' . rand(1000, 9999),
                'customer_id' => $request->customer_id,
                'branch_id' => $user->branch_id,
                'pos_session_id' => $session->id,
                'user_id' => $user->id,
                'created_by' => $user->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method ?? 'cash',
                'payment_status' => ($request->payment_method ?? 'cash') === 'credit' ? 'pending' : 'paid',
                'amount_received' => $request->amount_received ?? $totalAmount,
                'change_amount' => $request->return_amount ?? max(($request->amount_received ?? $totalAmount) - $totalAmount, 0),
                'reference_number' => $request->reference_number,
                'status' => 'completed',
                'order_date' => now(),
            ]);

            // Create order items and update stock
            foreach ($request->items as $item) {
                $quantity = $item['quantity'] ?? 0;
                $unit = $item['unit'] ?? 'kg';
                $unitPrice = $item['unit_price'] ?? $item['price'] ?? 0;
                $totalPrice = $item['total_price'] ?? ($quantity * $unitPrice);
                
                // For weight-based items, use quantity as weight
                $actualWeight = $item['actual_weight'] ?? $quantity;
                $billedWeight = $item['billed_weight'] ?? $quantity;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'actual_weight' => $actualWeight,
                    'billed_weight' => $billedWeight,
                    'adjustment_weight' => $actualWeight - $billedWeight,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $productBranch = DB::table('product_branches')
                        ->where('product_id', $item['product_id'])
                        ->where('branch_id', $user->branch_id)
                        ->first();

                    if ($productBranch) {
                        // Convert quantity to base unit for stock decrement
                        $quantity = $item['quantity'] ?? 0;
                        $unit = $item['unit'] ?? $product->weight_unit;
                        
                        // Convert to base unit (kg for weight, piece for count)
                        $stockDecrement = $quantity;
                        if ($product->bill_by === 'weight') {
                            // Convert to kg
                            if ($unit === 'gram') {
                                $stockDecrement = $quantity / 1000;
                            } elseif ($unit === 'dozen') {
                                // For weight products, dozen might not apply, but handle it
                                $stockDecrement = $quantity;
                            }
                        } else {
                            // Count-based: convert to pieces
                            if ($unit === 'dozen') {
                                $stockDecrement = $quantity * 12;
                            } elseif ($unit === 'packet' || $unit === 'box') {
                                // Assume 1 packet/box = 1 piece for now (can be configured)
                                $stockDecrement = $quantity;
                            }
                        }
                        
                        DB::table('product_branches')
                            ->where('product_id', $item['product_id'])
                            ->where('branch_id', $user->branch_id)
                            ->decrement('current_stock', $stockDecrement);
                    }
                }
            }

            // Update session stats
            $session->increment('total_transactions');
            $session->increment('total_sales', $totalAmount);

            $stockChanges = [
                'type' => 'decrement',
                'items' => array_map(function ($i) use ($user) {
                    return [
                        'product_id' => $i['product_id'],
                        'quantity' => $i['quantity'],
                        'branch_id' => $user->branch_id,
                    ];
                }, $request->items),
            ];

            DB::commit();

            // Broadcast events to branch listeners
            $freshSession = $session->fresh();
            event(new BranchSaleProcessed($order, $freshSession->branch_id, [
                'total_sales' => $freshSession->total_sales,
                'total_transactions' => $freshSession->total_transactions,
            ]));
            event(new BranchStockUpdated($freshSession->branch_id, $stockChanges));

            // Notify branch managers and cashiers of new order
            $recipients = \App\Models\User::where('branch_id', $freshSession->branch_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('name', ['branch_manager', 'cashier']);
                })
                ->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new OrderCreated($order));
            }

            return response()->json([
                'success' => true, 
                'message' => 'Sale processed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $totalAmount,
                    'session' => [
                        'total_sales' => $session->fresh()->total_sales,
                        'total_transactions' => $session->fresh()->total_transactions,
                    ],
                    'invoice_url' => route('orders.invoice', $order->id)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false, 
                'message' => 'Error processing sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show session handler form (for cashiers after login)
     */
    public function sessionHandler()
    {
        $user = auth()->user();
        $branch = $user->branch;
        
        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not assigned to any branch');
        }

        // Check if user already has an active session
        $currentSession = $user->currentPosSession();
        if ($currentSession) {
            return redirect()->route('pos.index')
                ->with('info', 'You already have an active session');
        }

        return view('pos.session-handler', compact('branch'));
    }

    /**
     * Process session handler form
     */
    public function processSessionHandler(Request $request)
    {
        $request->validate([
            'handled_by' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $branch = $user->branch;

        // Check if user already has an active session
        $currentSession = $user->currentPosSession();
        if ($currentSession) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active session'
            ]);
        }

        // Store handled_by in session for later use
        $request->session()->put('handled_by', $request->handled_by);

        // If this is an AJAX request, return JSON response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Session handler set successfully',
                'redirect_url' => route('pos.start-session')
            ]);
        }

        // For regular form submission, redirect to start session
        return redirect()->route('pos.start-session')
            ->with('success', 'Session handler set successfully');
    }
}
