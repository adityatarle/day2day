<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'received_quantity',
        'notes',
        'fulfilled_quantity',
        'actual_received_quantity',
        'actual_weight',
        'expected_weight',
        'weight_difference',
        'spoiled_quantity',
        'damaged_quantity',
        'usable_quantity',
        'quality_notes',
        'fulfillment_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'fulfilled_quantity' => 'decimal:2',
        'actual_received_quantity' => 'decimal:2',
        'actual_weight' => 'decimal:3',
        'expected_weight' => 'decimal:3',
        'weight_difference' => 'decimal:3',
        'spoiled_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'usable_quantity' => 'decimal:2',
    ];

    /**
     * Get the purchase order for this item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the total price for this item.
     */
    public function calculateTotalPrice(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Update the total price for this item.
     */
    public function updateTotalPrice(): void
    {
        $this->total_price = $this->calculateTotalPrice();
        $this->save();
    }
}