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
}
