<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'batch_id',
        'quantity',
        'unit_price',
        'total_price',
        'actual_weight',
        'billed_weight',
        'adjustment_weight',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'actual_weight' => 'decimal:2',
        'billed_weight' => 'decimal:2',
        'adjustment_weight' => 'decimal:2',
    ];

    /**
     * Get the order for this item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this item.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the return items for this order item.
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    /**
     * Calculate the total price for this item.
     */
    public function calculateTotalPrice(): float
    {
        $weight = $this->billed_weight ?? $this->actual_weight ?? $this->quantity;
        return $weight * $this->unit_price;
    }

    /**
     * Update the total price for this item.
     */
    public function updateTotalPrice(): void
    {
        $this->total_price = $this->calculateTotalPrice();
        $this->save();
    }

    /**
     * Get the adjustment weight (complimentary/adjustment).
     */
    public function getAdjustmentWeight(): float
    {
        if ($this->actual_weight && $this->billed_weight) {
            return $this->actual_weight - $this->billed_weight;
        }
        return $this->adjustment_weight ?? 0;
    }
}