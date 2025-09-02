<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'order_item_id',
        'returned_quantity',
        'refund_amount',
        'condition_notes',
    ];

    protected $casts = [
        'returned_quantity' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    /**
     * Get the return for this item.
     */
    public function return(): BelongsTo
    {
        return $this->belongsTo(Return::class);
    }

    /**
     * Get the order item for this return item.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the product for this return item.
     */
    public function product(): BelongsTo
    {
        return $this->orderItem->product;
    }

    /**
     * Get the branch for this return item.
     */
    public function branch(): BelongsTo
    {
        return $this->orderItem->order->branch;
    }

    /**
     * Calculate the refund amount based on returned quantity.
     */
    public function calculateRefundAmount(): float
    {
        if (!$this->orderItem) {
            return 0;
        }

        $unitPrice = $this->orderItem->unit_price;
        return $this->returned_quantity * $unitPrice;
    }

    /**
     * Update the refund amount.
     */
    public function updateRefundAmount(): void
    {
        $this->refund_amount = $this->calculateRefundAmount();
        $this->save();
    }
}