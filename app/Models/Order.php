<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'branch_id',
        'user_id',
        'pos_session_id',
        'order_type',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'adjustment_amount',
        'adjusted_invoice_number',
        'adjustment_date',
        'notes',
        'order_date',
        'delivery_date',
        'created_by',
        // Workflow fields
        'confirmed_at',
        'processing_at',
        'ready_at',
        'delivered_at',
        'cancelled_at',
        'workflow_metadata',
        'workflow_notes',
        'priority',
        'is_urgent',
        'delivery_address',
        'delivery_phone',
        'delivery_instructions',
        'expected_delivery_time',
        'quality_checked',
        'quality_checked_by',
        'quality_checked_at',
        'customer_notified',
        'last_notification_sent_at',
        'processing_time_minutes',
        'delivery_time_minutes',
        'total_cycle_time_minutes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'adjustment_date' => 'datetime',
        'order_date' => 'datetime',
        'delivery_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'processing_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'workflow_metadata' => 'array',
        'expected_delivery_time' => 'datetime',
        'quality_checked_at' => 'datetime',
        'last_notification_sent_at' => 'datetime',
        'is_urgent' => 'boolean',
        'quality_checked' => 'boolean',
        'customer_notified' => 'boolean',
    ];

    /**
     * Get the customer for this order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the branch for this order.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user (cashier/delivery boy) for this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for this order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the delivery for this order.
     */
    public function delivery(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Get the returns for this order.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    /**
     * Get the payments recorded for this order.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get the POS session for this order.
     */
    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }

    /**
     * Get the workflow logs for this order.
     */
    public function workflowLogs(): HasMany
    {
        return $this->hasMany(OrderWorkflowLog::class);
    }

    /**
     * Get the user who quality checked this order.
     */
    public function qualityCheckedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quality_checked_by');
    }

    /**
     * Scope to get orders by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get orders by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('order_type', $type);
    }

    /**
     * Scope to get orders by payment status.
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to get orders for a specific branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if order is online order.
     */
    public function isOnlineOrder(): bool
    {
        return $this->order_type === 'online';
    }

    /**
     * Check if order is on-shop order.
     */
    public function isOnShopOrder(): bool
    {
        return $this->order_type === 'on_shop';
    }

    /**
     * Check if order is wholesale order.
     */
    public function isWholesaleOrder(): bool
    {
        return $this->order_type === 'wholesale';
    }

    /**
     * Calculate tax amount based on GST rates.
     */
    public function calculateTaxAmount(): float
    {
        $taxAmount = 0;
        foreach ($this->orderItems as $item) {
            $product = $item->product;
            $gstRate = $product->gstRates->first();
            if ($gstRate) {
                $taxAmount += ($item->total_price * $gstRate->rate) / 100;
            }
        }
        return $taxAmount;
    }

    /**
     * Update order totals.
     */
    public function updateTotals(): void
    {
        $this->subtotal = $this->orderItems->sum('total_price');
        $this->tax_amount = $this->calculateTaxAmount();
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }

    /**
     * Transition order to new status using workflow service.
     */
    public function transitionTo(string $status, ?User $user = null, ?string $notes = null, array $metadata = []): bool
    {
        $workflowService = app(\App\Services\OrderWorkflowService::class);
        return $workflowService->transitionOrder($this, $status, $user, $notes, $metadata);
    }

    /**
     * Get possible workflow transitions for this order.
     */
    public function getPossibleTransitions(): array
    {
        $workflowService = app(\App\Services\OrderWorkflowService::class);
        return $workflowService->getPossibleTransitions($this);
    }

    /**
     * Check if order can transition to given status.
     */
    public function canTransitionTo(string $status): bool
    {
        $workflowService = app(\App\Services\OrderWorkflowService::class);
        return $workflowService->canTransition($this->status, $status);
    }

    /**
     * Get workflow history for this order.
     */
    public function getWorkflowHistory(): \Illuminate\Database\Eloquent\Collection
    {
        $workflowService = app(\App\Services\OrderWorkflowService::class);
        return $workflowService->getWorkflowHistory($this);
    }

    /**
     * Check if order is in draft state.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if order is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if order is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if order is ready.
     */
    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    /**
     * Check if order is picked up.
     */
    public function isPickedUp(): bool
    {
        return $this->status === 'picked_up';
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if order is returned.
     */
    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /**
     * Get processing time in minutes.
     */
    public function getProcessingTime(): ?int
    {
        if ($this->processing_at && $this->ready_at) {
            return $this->processing_at->diffInMinutes($this->ready_at);
        }
        return null;
    }

    /**
     * Get delivery time in minutes.
     */
    public function getDeliveryTime(): ?int
    {
        if ($this->ready_at && $this->delivered_at) {
            return $this->ready_at->diffInMinutes($this->delivered_at);
        }
        return null;
    }

    /**
     * Get total cycle time in minutes.
     */
    public function getTotalCycleTime(): ?int
    {
        if ($this->created_at && $this->delivered_at) {
            return $this->created_at->diffInMinutes($this->delivered_at);
        }
        return null;
    }

    /**
     * Update performance metrics.
     */
    public function updatePerformanceMetrics(): void
    {
        $this->processing_time_minutes = $this->getProcessingTime();
        $this->delivery_time_minutes = $this->getDeliveryTime();
        $this->total_cycle_time_minutes = $this->getTotalCycleTime();
        $this->save();
    }

    /**
     * Mark as quality checked.
     */
    public function markQualityChecked(User $user): void
    {
        $this->update([
            'quality_checked' => true,
            'quality_checked_by' => $user->id,
            'quality_checked_at' => now()
        ]);
    }

    /**
     * Mark customer as notified.
     */
    public function markCustomerNotified(): void
    {
        $this->update([
            'customer_notified' => true,
            'last_notification_sent_at' => now()
        ]);
    }

    /**
     * Get workflow status display information.
     */
    public function getWorkflowStatusInfo(): array
    {
        $workflowService = app(\App\Services\OrderWorkflowService::class);
        $states = $workflowService::WORKFLOW_STATES;
        
        return $states[$this->status] ?? [
            'name' => ucfirst($this->status),
            'description' => 'Unknown status',
            'color' => 'gray',
            'icon' => 'question-mark-circle'
        ];
    }
}
