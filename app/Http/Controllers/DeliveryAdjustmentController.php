<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Return;
use App\Models\ReturnItem;
use App\Models\Delivery;
use App\Models\Payment;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DeliveryAdjustmentController extends Controller
{
    /**
     * Get delivery orders for delivery boy.
     */
    public function getDeliveryOrders(Request $request)
    {
        $user = auth()->user();
        
        // Only delivery boys can access this
        if (!$user->hasRole('delivery_boy')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $orders = Order::with(['customer', 'branch', 'orderItems.product', 'delivery'])
                      ->where('order_type', 'online')
                      ->whereIn('status', ['confirmed', 'out_for_delivery'])
                      ->whereHas('delivery', function ($query) use ($user) {
                          $query->where('delivery_boy_id', $user->id);
                      })
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * Start delivery (mark as out for delivery).
     */
    public function startDelivery(Request $request, Order $order)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('delivery_boy')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $delivery = $order->delivery;
        
        if (!$delivery || $delivery->delivery_boy_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not assigned to this delivery'
            ], 403);
        }

        $delivery->update([
            'status' => 'out_for_delivery',
            'pickup_time' => now(),
        ]);

        $order->update(['status' => 'out_for_delivery']);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery started successfully',
            'data' => $order->load(['delivery', 'orderItems.product'])
        ]);
    }

    /**
     * Process delivery with customer adjustments.
     */
    public function processDelivery(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'delivery_status' => 'required|in:delivered,partially_delivered,returned',
            'customer_adjustments' => 'nullable|array',
            'customer_adjustments.*.order_item_id' => 'required|exists:order_items,id',
            'customer_adjustments.*.action' => 'required|in:return,reduce_quantity,increase_quantity',
            'customer_adjustments.*.quantity' => 'required|numeric|min:0',
            'customer_adjustments.*.reason' => 'nullable|string|max:255',
            'payment_adjustments' => 'nullable|array',
            'payment_adjustments.method' => 'nullable|in:cash,upi,card',
            'payment_adjustments.amount' => 'nullable|numeric|min:0',
            'delivery_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        if (!$user->hasRole('delivery_boy')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $delivery = $order->delivery;
        
        if (!$delivery || $delivery->delivery_boy_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not assigned to this delivery'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $inventoryService = new InventoryService();
            $totalRefundAmount = 0;
            $originalAmount = $order->total_amount;

            // Process customer adjustments
            if ($request->customer_adjustments) {
                foreach ($request->customer_adjustments as $adjustment) {
                    $orderItem = OrderItem::find($adjustment['order_item_id']);
                    
                    if ($orderItem->order_id !== $order->id) {
                        throw new \Exception('Order item does not belong to this order');
                    }

                    $refundAmount = $this->processItemAdjustment(
                        $orderItem, 
                        $adjustment, 
                        $inventoryService
                    );
                    
                    $totalRefundAmount += $refundAmount;
                }
            }

            // Update delivery status
            $delivery->update([
                'status' => $request->delivery_status,
                'delivery_time' => now(),
                'delivery_notes' => $request->delivery_notes,
                'customer_adjustments' => $request->customer_adjustments,
            ]);

            // Update order status and amount
            $newOrderAmount = $originalAmount - $totalRefundAmount;
            $order->update([
                'status' => $request->delivery_status,
                'total_amount' => $newOrderAmount,
                'adjustment_amount' => $totalRefundAmount,
            ]);

            // Process payment adjustments if any refund is needed
            if ($totalRefundAmount > 0) {
                $this->processPaymentAdjustment($order, $totalRefundAmount, $request->payment_adjustments);
            }

            // Generate new invoice if there were adjustments
            if ($totalRefundAmount > 0) {
                $this->generateAdjustedInvoice($order);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery processed successfully',
                'data' => [
                    'order' => $order->load(['orderItems.product', 'delivery', 'customer']),
                    'original_amount' => $originalAmount,
                    'adjusted_amount' => $newOrderAmount,
                    'refund_amount' => $totalRefundAmount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process individual item adjustment.
     */
    private function processItemAdjustment(OrderItem $orderItem, array $adjustment, InventoryService $inventoryService): float
    {
        $refundAmount = 0;
        $product = $orderItem->product;
        $branch = $orderItem->order->branch;

        switch ($adjustment['action']) {
            case 'return':
                // Create return record
                $return = Return::create([
                    'order_id' => $orderItem->order_id,
                    'customer_id' => $orderItem->order->customer_id,
                    'branch_id' => $branch->id,
                    'user_id' => auth()->id(),
                    'return_type' => 'customer_return',
                    'status' => 'approved',
                    'reason' => $adjustment['reason'] ?? 'Customer return during delivery',
                    'total_amount' => $orderItem->total_price,
                ]);

                ReturnItem::create([
                    'return_id' => $return->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity' => $adjustment['quantity'],
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $adjustment['quantity'] * $orderItem->unit_price,
                    'reason' => $adjustment['reason'] ?? 'Customer return',
                ]);

                // Add stock back
                $currentStock = $product->getCurrentStock($branch->id);
                $product->updateStock($branch->id, $currentStock + $adjustment['quantity']);

                $refundAmount = $adjustment['quantity'] * $orderItem->unit_price;
                break;

            case 'reduce_quantity':
                // Reduce order item quantity
                $reducedQuantity = $orderItem->quantity - $adjustment['quantity'];
                $refundQuantity = $orderItem->quantity - $reducedQuantity;
                
                $orderItem->update([
                    'quantity' => $reducedQuantity,
                    'total_price' => $reducedQuantity * $orderItem->unit_price,
                ]);

                // Add unused stock back
                $currentStock = $product->getCurrentStock($branch->id);
                $product->updateStock($branch->id, $currentStock + $refundQuantity);

                $refundAmount = $refundQuantity * $orderItem->unit_price;
                break;

            case 'increase_quantity':
                // Check if additional stock is available
                $currentStock = $product->getCurrentStock($branch->id);
                $additionalQuantity = $adjustment['quantity'] - $orderItem->quantity;
                
                if ($currentStock < $additionalQuantity) {
                    throw new \Exception("Insufficient stock for additional quantity of {$product->name}");
                }

                // Update order item
                $orderItem->update([
                    'quantity' => $adjustment['quantity'],
                    'total_price' => $adjustment['quantity'] * $orderItem->unit_price,
                ]);

                // Reduce stock for additional quantity
                $product->updateStock($branch->id, $currentStock - $additionalQuantity);

                // Additional amount to be collected (negative refund)
                $refundAmount = -($additionalQuantity * $orderItem->unit_price);
                break;
        }

        return $refundAmount;
    }

    /**
     * Process payment adjustment.
     */
    private function processPaymentAdjustment(Order $order, float $refundAmount, array $paymentAdjustments = null): void
    {
        if ($refundAmount > 0) {
            // Create refund payment record
            Payment::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'branch_id' => $order->branch_id,
                'user_id' => auth()->id(),
                'amount' => $refundAmount,
                'payment_method' => $paymentAdjustments['method'] ?? 'cash',
                'payment_type' => 'refund',
                'payment_date' => now(),
                'status' => 'completed',
                'reference_number' => 'REF-' . strtoupper(Str::random(8)),
                'notes' => 'Delivery adjustment refund',
            ]);
        } elseif ($refundAmount < 0) {
            // Additional payment needed
            $additionalAmount = abs($refundAmount);
            
            Payment::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'branch_id' => $order->branch_id,
                'user_id' => auth()->id(),
                'amount' => $additionalAmount,
                'payment_method' => $paymentAdjustments['method'] ?? 'cash',
                'payment_type' => 'additional',
                'payment_date' => now(),
                'status' => 'completed',
                'reference_number' => 'ADD-' . strtoupper(Str::random(8)),
                'notes' => 'Additional payment for delivery adjustment',
            ]);
        }
    }

    /**
     * Generate adjusted invoice.
     */
    private function generateAdjustedInvoice(Order $order): void
    {
        // Update order with adjusted invoice number
        $adjustedInvoiceNumber = $order->order_number . '-ADJ-' . now()->format('His');
        
        $order->update([
            'adjusted_invoice_number' => $adjustedInvoiceNumber,
            'adjustment_date' => now(),
        ]);

        // Log the invoice adjustment
        \Log::info("Adjusted invoice generated for order {$order->order_number}: {$adjustedInvoiceNumber}");
    }

    /**
     * Get delivery history for delivery boy.
     */
    public function getDeliveryHistory(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('delivery_boy')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = Order::with(['customer', 'branch', 'orderItems.product', 'delivery', 'returns'])
                     ->where('order_type', 'online')
                     ->whereHas('delivery', function ($q) use ($user) {
                         $q->where('delivery_boy_id', $user->id);
                     });

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
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
     * Get delivery statistics for delivery boy.
     */
    public function getDeliveryStats(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('delivery_boy')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now()->endOfMonth();

        $deliveries = Delivery::where('delivery_boy_id', $user->id)
                             ->whereBetween('created_at', [$startDate, $endDate])
                             ->get();

        $orders = Order::whereHas('delivery', function ($query) use ($user) {
                        $query->where('delivery_boy_id', $user->id);
                    })
                    ->whereBetween('order_date', [$startDate, $endDate])
                    ->get();

        $stats = [
            'total_deliveries' => $deliveries->count(),
            'successful_deliveries' => $deliveries->where('status', 'delivered')->count(),
            'returned_deliveries' => $deliveries->where('status', 'returned')->count(),
            'pending_deliveries' => $deliveries->whereIn('status', ['assigned', 'out_for_delivery'])->count(),
            'total_order_value' => $orders->sum('total_amount'),
            'total_adjustments' => $orders->sum('adjustment_amount'),
            'total_returns' => $orders->whereHas('returns')->count(),
            'average_delivery_time' => $deliveries->where('status', 'delivered')->avg(function ($delivery) {
                return $delivery->pickup_time && $delivery->delivery_time ? 
                    $delivery->pickup_time->diffInMinutes($delivery->delivery_time) : 0;
            }),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Quick return items during delivery.
     */
    public function quickReturn(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'required|string|max:255',
            'customer_signature' => 'nullable|string',
            'return_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        if (!$user->hasRole('delivery_boy')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $inventoryService = new InventoryService();
            $totalRefundAmount = 0;

            // Create return record
            $return = Return::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'branch_id' => $order->branch_id,
                'user_id' => $user->id,
                'return_type' => 'delivery_return',
                'status' => 'approved',
                'reason' => 'Customer return during delivery',
                'return_date' => now(),
                'notes' => $request->return_notes,
            ]);

            foreach ($request->items as $item) {
                $orderItem = OrderItem::find($item['order_item_id']);
                
                if ($orderItem->order_id !== $order->id) {
                    throw new \Exception('Order item does not belong to this order');
                }

                // Create return item
                $returnItem = ReturnItem::create([
                    'return_id' => $return->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $item['quantity'] * $orderItem->unit_price,
                    'reason' => $item['reason'],
                ]);

                // Add stock back to inventory
                $product = $orderItem->product;
                $currentStock = $product->getCurrentStock($order->branch_id);
                $product->updateStock($order->branch_id, $currentStock + $item['quantity']);

                // Update order item quantity
                $newQuantity = $orderItem->quantity - $item['quantity'];
                $orderItem->update([
                    'quantity' => $newQuantity,
                    'total_price' => $newQuantity * $orderItem->unit_price,
                ]);

                $totalRefundAmount += $returnItem->total_price;
            }

            // Update return total
            $return->update(['total_amount' => $totalRefundAmount]);

            // Create refund payment
            if ($totalRefundAmount > 0) {
                Payment::create([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'branch_id' => $order->branch_id,
                    'user_id' => $user->id,
                    'amount' => $totalRefundAmount,
                    'payment_method' => 'cash', // Default for delivery returns
                    'payment_type' => 'refund',
                    'payment_date' => now(),
                    'status' => 'completed',
                    'reference_number' => 'RET-' . strtoupper(Str::random(8)),
                    'notes' => 'Refund for returned items during delivery',
                ]);
            }

            // Update order total
            $order->update([
                'total_amount' => $order->total_amount - $totalRefundAmount,
                'adjustment_amount' => $totalRefundAmount,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Items returned successfully',
                'data' => [
                    'return' => $return->load(['returnItems.product']),
                    'refund_amount' => $totalRefundAmount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update delivery location (GPS tracking).
     */
    public function updateLocation(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        if (!$user->hasRole('delivery_boy')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $delivery = $order->delivery;
        
        if (!$delivery || $delivery->delivery_boy_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not assigned to this delivery'
            ], 403);
        }

        $delivery->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
            'last_location_update' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated successfully'
        ]);
    }

    /**
     * Get delivery route optimization.
     */
    public function getOptimizedRoute(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('delivery_boy')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $orders = Order::with(['customer', 'delivery'])
                      ->where('status', 'confirmed')
                      ->whereHas('delivery', function ($query) use ($user) {
                          $query->where('delivery_boy_id', $user->id);
                      })
                      ->get();

        // Simple route optimization based on customer addresses
        // In a production system, you'd integrate with Google Maps API or similar
        $optimizedRoute = $orders->sortBy(function ($order) {
            // Simple sorting by customer address - you can enhance this with actual GPS coordinates
            return $order->customer->address ?? '';
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'optimized_route' => $optimizedRoute,
                'total_orders' => $orders->count(),
                'estimated_time' => $orders->count() * 15, // 15 minutes per delivery
            ]
        ]);
    }
}