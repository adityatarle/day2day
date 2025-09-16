<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderWorkflowLog;
use App\Models\User;
use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderWorkflowService
{
    /**
     * Order workflow states and transitions
     */
    const WORKFLOW_STATES = [
        'draft' => [
            'name' => 'Draft',
            'description' => 'Order is being created',
            'color' => 'gray',
            'icon' => 'edit',
            'allowed_transitions' => ['confirmed', 'cancelled']
        ],
        'pending' => [
            'name' => 'Pending',
            'description' => 'Order is waiting for confirmation',
            'color' => 'yellow',
            'icon' => 'clock',
            'allowed_transitions' => ['confirmed', 'cancelled']
        ],
        'confirmed' => [
            'name' => 'Confirmed',
            'description' => 'Order has been confirmed and is ready for processing',
            'color' => 'blue',
            'icon' => 'check-circle',
            'allowed_transitions' => ['processing', 'cancelled']
        ],
        'processing' => [
            'name' => 'Processing',
            'description' => 'Order is being prepared/processed',
            'color' => 'orange',
            'icon' => 'cog',
            'allowed_transitions' => ['ready', 'cancelled']
        ],
        'ready' => [
            'name' => 'Ready',
            'description' => 'Order is ready for pickup/delivery',
            'color' => 'green',
            'icon' => 'check',
            'allowed_transitions' => ['delivered', 'picked_up', 'cancelled']
        ],
        'picked_up' => [
            'name' => 'Picked Up',
            'description' => 'Order has been picked up by customer',
            'color' => 'indigo',
            'icon' => 'hand-raised',
            'allowed_transitions' => ['delivered']
        ],
        'delivered' => [
            'name' => 'Delivered',
            'description' => 'Order has been delivered successfully',
            'color' => 'green',
            'icon' => 'truck',
            'allowed_transitions' => ['returned']
        ],
        'returned' => [
            'name' => 'Returned',
            'description' => 'Order has been returned',
            'color' => 'red',
            'icon' => 'arrow-uturn-left',
            'allowed_transitions' => []
        ],
        'cancelled' => [
            'name' => 'Cancelled',
            'description' => 'Order has been cancelled',
            'color' => 'red',
            'icon' => 'x-circle',
            'allowed_transitions' => []
        ]
    ];

    /**
     * Payment workflow states
     */
    const PAYMENT_STATES = [
        'pending' => [
            'name' => 'Pending',
            'description' => 'Payment is pending',
            'color' => 'yellow',
            'icon' => 'clock'
        ],
        'paid' => [
            'name' => 'Paid',
            'description' => 'Payment has been received',
            'color' => 'green',
            'icon' => 'check-circle'
        ],
        'failed' => [
            'name' => 'Failed',
            'description' => 'Payment failed',
            'color' => 'red',
            'icon' => 'x-circle'
        ],
        'refunded' => [
            'name' => 'Refunded',
            'description' => 'Payment has been refunded',
            'color' => 'blue',
            'icon' => 'arrow-uturn-left'
        ],
        'partially_refunded' => [
            'name' => 'Partially Refunded',
            'description' => 'Partial refund has been processed',
            'color' => 'orange',
            'icon' => 'arrow-uturn-left'
        ]
    ];

    /**
     * Transition an order to a new status
     */
    public function transitionOrder(Order $order, string $newStatus, ?User $user = null, ?string $notes = null, array $metadata = []): bool
    {
        try {
            DB::beginTransaction();

            $currentStatus = $order->status;
            
            // Validate transition
            if (!$this->canTransition($currentStatus, $newStatus)) {
                throw new \Exception("Invalid transition from {$currentStatus} to {$newStatus}");
            }

            // Validate business rules
            $this->validateTransition($order, $newStatus, $user);

            // Update order status
            $order->status = $newStatus;
            $order->save();

            // Log the transition
            $this->logTransition($order, $currentStatus, $newStatus, $user, $notes, $metadata);

            // Trigger events
            event(new OrderStatusChanged($order, $currentStatus, $newStatus, $user));

            // Execute post-transition actions
            $this->executePostTransitionActions($order, $currentStatus, $newStatus, $user);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order transition failed', [
                'order_id' => $order->id,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check if a transition is allowed
     */
    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        if (!isset(self::WORKFLOW_STATES[$fromStatus]) || !isset(self::WORKFLOW_STATES[$toStatus])) {
            return false;
        }

        return in_array($toStatus, self::WORKFLOW_STATES[$fromStatus]['allowed_transitions']);
    }

    /**
     * Get all possible transitions for an order
     */
    public function getPossibleTransitions(Order $order): array
    {
        $currentStatus = $order->status;
        $transitions = [];

        if (isset(self::WORKFLOW_STATES[$currentStatus])) {
            foreach (self::WORKFLOW_STATES[$currentStatus]['allowed_transitions'] as $status) {
                $transitions[$status] = self::WORKFLOW_STATES[$status];
            }
        }

        return $transitions;
    }

    /**
     * Validate transition business rules
     */
    protected function validateTransition(Order $order, string $newStatus, ?User $user = null): void
    {
        switch ($newStatus) {
            case 'confirmed':
                $this->validateConfirmation($order);
                break;
            case 'processing':
                $this->validateProcessing($order);
                break;
            case 'ready':
                $this->validateReady($order);
                break;
            case 'delivered':
                $this->validateDelivery($order);
                break;
            case 'cancelled':
                $this->validateCancellation($order);
                break;
        }
    }

    /**
     * Validate order confirmation
     */
    protected function validateConfirmation(Order $order): void
    {
        if ($order->orderItems->isEmpty()) {
            throw new \Exception('Cannot confirm order without items');
        }

        if ($order->payment_method === 'cod' && $order->payment_status !== 'pending') {
            throw new \Exception('COD orders must have pending payment status');
        }
    }

    /**
     * Validate order processing
     */
    protected function validateProcessing(Order $order): void
    {
        if ($order->payment_method !== 'cod' && $order->payment_status !== 'paid') {
            throw new \Exception('Non-COD orders must be paid before processing');
        }

        // Check stock availability
        foreach ($order->orderItems as $item) {
            $currentStock = $item->product->getCurrentStock($order->branch);
            if ($currentStock < $item->quantity) {
                throw new \Exception("Insufficient stock for {$item->product->name}. Available: {$currentStock}");
            }
        }
    }

    /**
     * Validate order ready status
     */
    protected function validateReady(Order $order): void
    {
        // All items should be processed
        if ($order->orderItems->isEmpty()) {
            throw new \Exception('Cannot mark order as ready without items');
        }
    }

    /**
     * Validate order delivery
     */
    protected function validateDelivery(Order $order): void
    {
        if ($order->order_type === 'online' && !$order->delivery->where('status', 'delivered')->exists()) {
            throw new \Exception('Online orders must have delivery confirmation');
        }
    }

    /**
     * Validate order cancellation
     */
    protected function validateCancellation(Order $order): void
    {
        if (in_array($order->status, ['delivered', 'returned'])) {
            throw new \Exception('Cannot cancel delivered or returned orders');
        }
    }

    /**
     * Execute post-transition actions
     */
    protected function executePostTransitionActions(Order $order, string $fromStatus, string $toStatus, ?User $user = null): void
    {
        switch ($toStatus) {
            case 'confirmed':
                $this->handleOrderConfirmation($order);
                break;
            case 'processing':
                $this->handleOrderProcessing($order);
                break;
            case 'ready':
                $this->handleOrderReady($order);
                break;
            case 'delivered':
                $this->handleOrderDelivered($order);
                break;
            case 'cancelled':
                $this->handleOrderCancellation($order);
                break;
        }
    }

    /**
     * Handle order confirmation
     */
    protected function handleOrderConfirmation(Order $order): void
    {
        // Send confirmation notification
        // Update inventory reservations
        // Generate order confirmation document
    }

    /**
     * Handle order processing
     */
    protected function handleOrderProcessing(Order $order): void
    {
        // Reserve inventory
        // Assign to staff member
        // Start processing timer
    }

    /**
     * Handle order ready
     */
    protected function handleOrderReady(Order $order): void
    {
        // Notify customer (if online order)
        // Assign delivery boy (if delivery required)
        // Update ready timestamp
        $order->update(['ready_at' => now()]);
    }

    /**
     * Handle order delivered
     */
    protected function handleOrderDelivered(Order $order): void
    {
        // Update delivery timestamp
        $order->update(['delivered_at' => now()]);
        
        // Process payment if COD
        if ($order->payment_method === 'cod') {
            $order->update(['payment_status' => 'paid']);
        }
    }

    /**
     * Handle order cancellation
     */
    protected function handleOrderCancellation(Order $order): void
    {
        // Restore inventory
        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $branch = $order->branch;
            $currentStock = $product->getCurrentStock($branch);
            
            $product->branches()->updateExistingPivot($branch->id, [
                'current_stock' => $currentStock + $item->quantity
            ]);
        }

        // Process refund if needed
        if ($order->payment_status === 'paid') {
            $order->update(['payment_status' => 'refunded']);
        }
    }

    /**
     * Log workflow transition
     */
    protected function logTransition(Order $order, string $fromStatus, string $toStatus, ?User $user, ?string $notes, array $metadata): void
    {
        OrderWorkflowLog::create([
            'order_id' => $order->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'user_id' => $user?->id,
            'notes' => $notes,
            'metadata' => $metadata,
            'transitioned_at' => now()
        ]);
    }

    /**
     * Get workflow history for an order
     */
    public function getWorkflowHistory(Order $order): \Illuminate\Database\Eloquent\Collection
    {
        return OrderWorkflowLog::where('order_id', $order->id)
            ->with('user')
            ->orderBy('transitioned_at', 'desc')
            ->get();
    }

    /**
     * Get workflow statistics
     */
    public function getWorkflowStatistics(array $filters = []): array
    {
        $query = Order::query();

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        $orders = $query->get();
        
        $stats = [];
        foreach (self::WORKFLOW_STATES as $status => $config) {
            $stats[$status] = [
                'count' => $orders->where('status', $status)->count(),
                'percentage' => $orders->count() > 0 ? round(($orders->where('status', $status)->count() / $orders->count()) * 100, 2) : 0,
                'config' => $config
            ];
        }

        return $stats;
    }

    /**
     * Get average processing times
     */
    public function getAverageProcessingTimes(array $filters = []): array
    {
        $query = Order::query();

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        $orders = $query->whereNotNull('delivered_at')->get();

        $times = [
            'order_to_confirmed' => $orders->whereNotNull('confirmed_at')->avg(function ($order) {
                return $order->confirmed_at->diffInMinutes($order->created_at);
            }),
            'confirmed_to_processing' => $orders->whereNotNull('processing_at')->avg(function ($order) {
                return $order->processing_at->diffInMinutes($order->confirmed_at);
            }),
            'processing_to_ready' => $orders->whereNotNull('ready_at')->avg(function ($order) {
                return $order->ready_at->diffInMinutes($order->processing_at);
            }),
            'ready_to_delivered' => $orders->whereNotNull('delivered_at')->avg(function ($order) {
                return $order->delivered_at->diffInMinutes($order->ready_at);
            }),
            'total_processing_time' => $orders->avg(function ($order) {
                return $order->delivered_at->diffInMinutes($order->created_at);
            })
        ];

        return array_map(function ($time) {
            return $time ? round($time, 2) : 0;
        }, $times);
    }
}