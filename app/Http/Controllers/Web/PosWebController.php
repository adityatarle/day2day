<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $customers = Customer::active()->get();
        
        // Debug: Log session status
        \Log::info('POS Index - User: ' . $user->id . ', Session: ' . ($currentSession ? $currentSession->id : 'None'));
        
        return view('pos.index', compact('branch', 'currentSession', 'customers'));
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
        
        return view('pos.close-session', compact('session', 'expectedCash'));
    }

    /**
     * Process session close.
     */
    public function processCloseSession(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return redirect()->route('pos.index')
                ->with('error', 'No active session found');
        }

        $notes = $request->notes ? [$request->notes] : [];
        $session->closeSession($request->closing_cash, $notes);

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
     * Process a sale (API endpoint).
     */
    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,upi,credit',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'amount_received' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        $session = PosSession::where('user_id', $user->id)->active()->first();
        
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active POS session']);
        }

        try {
            DB::beginTransaction();

            // Calculate totals based on weight
            $subtotal = collect($request->items)->sum(function($item) {
                $weight = $item['billed_weight'] ?? $item['actual_weight'] ?? $item['quantity'];
                return $weight * $item['price'];
            });
            
            $discountAmount = $request->discount_amount ?? 0;
            $taxAmount = 0; // No GST
            $totalAmount = $subtotal - $discountAmount;

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
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'credit' ? 'pending' : 'paid',
                'amount_received' => $request->amount_received ?? $totalAmount,
                'change_amount' => max(($request->amount_received ?? $totalAmount) - $totalAmount, 0),
                'reference_number' => $request->reference_number,
                'status' => 'completed',
                'order_date' => now(),
            ]);

            // Create order items and update stock
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

                // Update product stock
                $productBranch = DB::table('product_branches')
                    ->where('product_id', $item['product_id'])
                    ->where('branch_id', $user->branch_id)
                    ->first();

                if ($productBranch) {
                    DB::table('product_branches')
                        ->where('product_id', $item['product_id'])
                        ->where('branch_id', $user->branch_id)
                        ->decrement('current_stock', $item['quantity']);
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
