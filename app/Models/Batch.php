<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'batch_number',
        'initial_quantity',
        'current_quantity',
        'expiry_date',
        'purchase_date',
        'purchase_price',
        'status',
    ];

    protected $casts = [
        'initial_quantity' => 'decimal:2',
        'current_quantity' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'expiry_date' => 'date',
        'purchase_date' => 'date',
    ];

    /**
     * Get the product for this batch.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this batch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the stock movements for this batch.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the loss tracking records for this batch.
     */
    public function lossTracking(): HasMany
    {
        return $this->hasMany(LossTracking::class);
    }

    /**
     * Get the order items for this batch.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to get only active batches.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get expired batches.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope to get batches by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if batch is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if batch is sold out.
     */
    public function isSoldOut(): bool
    {
        return $this->current_quantity <= 0;
    }

    /**
     * Get the remaining quantity percentage.
     */
    public function getRemainingPercentage(): float
    {
        if ($this->initial_quantity <= 0) {
            return 0;
        }
        return ($this->current_quantity / $this->initial_quantity) * 100;
    }

    /**
     * Update batch status based on current conditions.
     */
    public function updateStatus(): void
    {
        if ($this->isExpired()) {
            $this->update(['status' => 'expired']);
        } elseif ($this->isSoldOut()) {
            $this->update(['status' => 'sold_out']);
        } elseif ($this->status !== 'active') {
            $this->update(['status' => 'active']);
        }
    }
}