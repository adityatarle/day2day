<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PurchaseEntryItem Model
 * 
 * Tracks individual items within a purchase entry with detailed quantity tracking.
 */
class PurchaseEntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_entry_id',
        'purchase_order_item_id',
        'product_id',
        'expected_quantity',
        'received_quantity',
        'spoiled_quantity',
        'damaged_quantity',
        'usable_quantity',
        'expected_weight',
        'actual_weight',
        'weight_difference',
        'unit_price',
        'total_price',
        'quality_notes',
        'discrepancy_notes',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'spoiled_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'usable_quantity' => 'decimal:2',
        'expected_weight' => 'decimal:3',
        'actual_weight' => 'decimal:3',
        'weight_difference' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the purchase entry for this item.
     */
    public function purchaseEntry(): BelongsTo
    {
        return $this->belongsTo(PurchaseEntry::class);
    }

    /**
     * Get the purchase order item for this entry item.
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
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
        return $this->received_quantity * $this->unit_price;
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
     * Calculate remaining quantity to be received.
     */
    public function getRemainingQuantity(): float
    {
        return max(0, $this->expected_quantity - $this->received_quantity);
    }

    /**
     * Calculate loss percentage for this item.
     */
    public function getLossPercentage(): float
    {
        if ($this->received_quantity == 0) {
            return 0;
        }
        
        $totalLoss = $this->spoiled_quantity + $this->damaged_quantity;
        return ($totalLoss / $this->received_quantity) * 100;
    }

    /**
     * Check if this item has discrepancies.
     */
    public function hasDiscrepancies(): bool
    {
        return $this->spoiled_quantity > 0 || 
               $this->damaged_quantity > 0 || 
               abs($this->weight_difference) > 0.1;
    }

    /**
     * Get discrepancy type.
     */
    public function getDiscrepancyType(): string
    {
        if ($this->spoiled_quantity > 0 && $this->damaged_quantity > 0) {
            return 'spoiled_and_damaged';
        } elseif ($this->spoiled_quantity > 0) {
            return 'spoiled';
        } elseif ($this->damaged_quantity > 0) {
            return 'damaged';
        } elseif (abs($this->weight_difference) > 0.1) {
            return 'weight_difference';
        }
        
        return 'none';
    }
}