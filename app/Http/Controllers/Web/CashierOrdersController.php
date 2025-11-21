<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashierOrdersController extends Controller
{
    /**
     * Display cashier's orders.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account.');
        }

        $query = Order::where('created_by', $user->id)
            ->where('branch_id', $branch->id)
            ->with(['customer', 'orderItems.product']);

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        // Statistics
        $stats = [
            'today_orders' => Order::where('created_by', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'today_sales' => Order::where('created_by', $user->id)
                ->where('status', 'completed')
                ->whereDate('created_at', Carbon::today())
                ->sum('total_amount'),
            'total_orders' => Order::where('created_by', $user->id)->count(),
            'total_sales' => Order::where('created_by', $user->id)
                ->where('status', 'completed')
                ->sum('total_amount'),
        ];

        return view('cashier.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $user = auth()->user();

        // Ensure the order belongs to this cashier
        if ($order->created_by !== $user->id) {
            return redirect()->route('cashier.orders.index')
                ->with('error', 'Order not found.');
        }

        $order->load(['customer', 'orderItems.product', 'payments', 'returns']);

        return view('cashier.orders.show', compact('order'));
    }

    /**
     * Display cashier's returns and refunds.
     */
    public function returns(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account.');
        }

        $query = OrderReturn::whereHas('order', function($q) use ($user) {
            $q->where('created_by', $user->id);
        })->with(['order.customer', 'returnItems.product']);

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $returns = $query->latest()->paginate(20);

        $todayReturnsQuery = OrderReturn::whereHas('order', function($q) use ($user) {
            $q->where('created_by', $user->id);
        })->whereDate('created_at', Carbon::today());
        
        $allReturnsQuery = OrderReturn::whereHas('order', function($q) use ($user) {
            $q->where('created_by', $user->id);
        });

        $stats = [
            'today_returns' => $todayReturnsQuery->count(),
            'today_refunds' => $todayReturnsQuery->sum('total_amount'),
            'today_cash_refunds' => $todayReturnsQuery->sum('cash_refund_amount'),
            'today_upi_refunds' => $todayReturnsQuery->sum('upi_refund_amount'),
            'total_returns' => $allReturnsQuery->count(),
            'total_refunds' => $allReturnsQuery->sum('total_amount'),
            'total_cash_refunds' => $allReturnsQuery->sum('cash_refund_amount'),
            'total_upi_refunds' => $allReturnsQuery->sum('upi_refund_amount'),
        ];

        return view('cashier.returns.index', compact('returns', 'stats'));
    }

    /**
     * Show form to create a return.
     */
    public function createReturn(Order $order)
    {
        $user = auth()->user();

        // Ensure the order belongs to this cashier
        if ($order->created_by !== $user->id) {
            return redirect()->route('cashier.orders.index')
                ->with('error', 'Order not found.');
        }

        // Ensure order is completed
        if ($order->status !== 'completed') {
            return redirect()->route('cashier.orders.show', $order)
                ->with('error', 'Can only create returns for completed orders.');
        }

        $order->load(['orderItems.product', 'payments']);

        // Calculate payment breakdown from original order
        $totalPaid = $order->payments()->where('payment_type', 'order_payment')->sum('amount');
        $cashPaid = $order->payments()->where('payment_type', 'order_payment')
            ->where('payment_method', 'cash')->sum('amount');
        $upiPaid = $order->payments()->where('payment_type', 'order_payment')
            ->where('payment_method', 'upi')->sum('amount');
        
        $paymentBreakdown = [
            'total' => $totalPaid,
            'cash' => $cashPaid,
            'upi' => $upiPaid,
            'cash_percentage' => $totalPaid > 0 ? ($cashPaid / $totalPaid) * 100 : 0,
            'upi_percentage' => $totalPaid > 0 ? ($upiPaid / $totalPaid) * 100 : 0,
        ];

        return view('cashier.returns.create', compact('order', 'paymentBreakdown'));
    }

    /**
     * Store a return.
     */
    public function storeReturn(Request $request, Order $order)
    {
        $user = auth()->user();

        // Ensure the order belongs to this cashier
        if ($order->created_by !== $user->id) {
            return redirect()->route('cashier.orders.index')
                ->with('error', 'Order not found.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.reason' => 'nullable|string|max:255',
        ]);

        // Create return
        $return = OrderReturn::create([
            'order_id' => $order->id,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $totalAmount = 0;

        // Create return items
        foreach ($validated['items'] as $item) {
            $orderItem = $order->orderItems()
                ->where('product_id', $item['product_id'])
                ->first();

            if ($orderItem && $item['quantity'] <= $orderItem->quantity) {
                $subtotal = $orderItem->unit_price * $item['quantity'];
                
                $returnItem = $return->returnItems()->create([
                    'order_item_id' => $orderItem->id,
                    'returned_quantity' => $item['quantity'],
                    'refund_amount' => $subtotal,
                    'condition_notes' => $item['reason'] ?? null,
                ]);

                $totalAmount += $subtotal;
            }
        }

        // Calculate cash and UPI refund amounts based on original payment breakdown
        $order->load('payments');
        $totalPaid = $order->payments()->where('payment_type', 'order_payment')->sum('amount');
        $cashPaid = $order->payments()->where('payment_type', 'order_payment')
            ->where('payment_method', 'cash')->sum('amount');
        $upiPaid = $order->payments()->where('payment_type', 'order_payment')
            ->where('payment_method', 'upi')->sum('amount');

        $cashRefundAmount = 0;
        $upiRefundAmount = 0;

        if ($totalPaid > 0) {
            // Calculate proportional refund amounts
            $cashRefundAmount = ($cashPaid / $totalPaid) * $totalAmount;
            $upiRefundAmount = ($upiPaid / $totalPaid) * $totalAmount;
        } else {
            // If no payments found, use order payment_method as fallback
            if ($order->payment_method === 'cash') {
                $cashRefundAmount = $totalAmount;
            } elseif ($order->payment_method === 'upi') {
                $upiRefundAmount = $totalAmount;
            }
        }

        $return->update([
            'total_amount' => $totalAmount,
            'cash_refund_amount' => round($cashRefundAmount, 2),
            'upi_refund_amount' => round($upiRefundAmount, 2),
        ]);

        return redirect()->route('cashier.returns.index')
            ->with('success', 'Return created successfully.');
    }
}