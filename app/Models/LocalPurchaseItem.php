<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalPurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'local_purchase_id',
        'product_id',
        'quantity',
        'unit',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateAmounts();
        });
    }

    /**
     * Get the local purchase this item belongs to.
     */
    public function localPurchase(): BelongsTo
    {
        return $this->belongsTo(LocalPurchase::class);
    }

    /**
     * Get the product associated with this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate tax, discount, and total amounts.
     */
    public function calculateAmounts(): void
    {
        $baseAmount = $this->quantity * $this->unit_price;
        
        // Calculate tax amount
        if ($this->tax_rate > 0) {
            $this->tax_amount = round($baseAmount * ($this->tax_rate / 100), 2);
        } else {
            $this->tax_amount = 0;
        }
        
        // Calculate discount amount
        if ($this->discount_rate > 0) {
            $this->discount_amount = round($baseAmount * ($this->discount_rate / 100), 2);
        } else {
            $this->discount_amount = 0;
        }
        
        // Calculate total amount
        $this->total_amount = $baseAmount + $this->tax_amount - $this->discount_amount;
    }

    /**
     * Update stock for this item.
     */
    public function updateStock(): void
    {
        $localPurchase = $this->localPurchase;
        
        if (!$localPurchase->isApproved() && !$localPurchase->isCompleted()) {
            return; // Only update stock for approved/completed purchases
        }

        // Create stock movement record
        StockMovement::create([
            'product_id' => $this->product_id,
            'branch_id' => $localPurchase->branch_id,
            'type' => 'local_purchase',
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_price,
            'reference_type' => LocalPurchase::class,
            'reference_id' => $localPurchase->id,
            'notes' => 'Local purchase: ' . $localPurchase->purchase_number,
            'user_id' => $localPurchase->manager_id,
            'movement_date' => $localPurchase->purchase_date,
        ]);

        // Update branch product stock
        $branchProduct = $localPurchase->branch->products()
            ->where('product_id', $this->product_id)
            ->first();

        if ($branchProduct) {
            $currentStock = $branchProduct->pivot->current_stock ?? 0;
            $localPurchase->branch->products()->updateExistingPivot($this->product_id, [
                'current_stock' => $currentStock + $this->quantity,
            ]);
        } else {
            // Create branch product entry if it doesn't exist
            $localPurchase->branch->products()->attach($this->product_id, [
                'current_stock' => $this->quantity,
                'selling_price' => $this->product->selling_price ?? 0,
                'is_available_online' => true,
            ]);
        }

        // Update purchase order item if linked
        if ($localPurchase->purchase_order_id) {
            $this->updatePurchaseOrderItem();
        }
    }

    /**
     * Update the linked purchase order item.
     */
    private function updatePurchaseOrderItem(): void
    {
        $localPurchase = $this->localPurchase;
        $purchaseOrder = $localPurchase->purchaseOrder;

        if (!$purchaseOrder) {
            return;
        }

        $poItem = $purchaseOrder->items()
            ->where('product_id', $this->product_id)
            ->first();

        if ($poItem) {
            $currentReceived = $poItem->received_quantity ?? 0;
            $newReceived = $currentReceived + $this->quantity;
            
            $poItem->update([
                'received_quantity' => $newReceived,
                'status' => $newReceived >= $poItem->quantity ? 'received' : 'partial',
            ]);

            // Trigger aggregate recalculation for the purchase order
            $purchaseOrder->recalculateReceiptAggregates();
        }
    }

    /**
     * Get the subtotal (before tax and discount).
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}