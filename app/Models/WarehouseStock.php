<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'allocated_quantity',
        'available_quantity',
        'reserved_quantity',
        'minimum_quantity',
        'maximum_quantity',
        'storage_zone',
    ];

    protected $casts = [
        'allocated_quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
        'maximum_quantity' => 'decimal:2',
    ];

    /**
     * Get the warehouse for this stock.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product for this stock.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Add stock to warehouse.
     */
    public function addStock(float $quantity): void
    {
        $this->increment('available_quantity', $quantity);
        $this->increment('allocated_quantity', $quantity);
    }

    /**
     * Remove stock from warehouse.
     */
    public function removeStock(float $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->decrement('available_quantity', $quantity);
        $this->decrement('allocated_quantity', $quantity);
        return true;
    }

    /**
     * Reserve stock for a transfer.
     */
    public function reserveStock(float $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->decrement('available_quantity', $quantity);
        $this->increment('reserved_quantity', $quantity);
        return true;
    }

    /**
     * Release reserved stock (transfer cancelled).
     */
    public function releaseReserved(float $quantity): void
    {
        $this->decrement('reserved_quantity', $quantity);
        $this->increment('available_quantity', $quantity);
    }

    /**
     * Confirm reserved stock (transfer completed).
     */
    public function confirmReserved(float $quantity): void
    {
        $this->decrement('reserved_quantity', $quantity);
        $this->decrement('allocated_quantity', $quantity);
    }

    /**
     * Check if stock is below minimum level.
     */
    public function isBelowMinimum(): bool
    {
        return $this->available_quantity < $this->minimum_quantity;
    }

    /**
     * Check if stock exceeds maximum level.
     */
    public function isAboveMaximum(): bool
    {
        if (!$this->maximum_quantity) {
            return false;
        }
        return $this->allocated_quantity > $this->maximum_quantity;
    }

    /**
     * Get reorder recommendation.
     */
    public function getReorderRecommendation(): ?array
    {
        if (!$this->isBelowMinimum()) {
            return null;
        }

        $shortfall = $this->minimum_quantity - $this->available_quantity;
        $recommendedQty = $shortfall + ($this->minimum_quantity * 0.2); // Add 20% buffer

        if ($this->maximum_quantity) {
            $recommendedQty = min($recommendedQty, $this->maximum_quantity - $this->allocated_quantity);
        }

        return [
            'warehouse_id' => $this->warehouse_id,
            'product_id' => $this->product_id,
            'current_stock' => $this->available_quantity,
            'minimum_required' => $this->minimum_quantity,
            'shortfall' => $shortfall,
            'recommended_quantity' => max(0, $recommendedQty),
        ];
    }

    /**
     * Get stock availability percentage.
     */
    public function getAvailabilityPercentage(): float
    {
        if ($this->allocated_quantity <= 0) {
            return 0;
        }

        return ($this->available_quantity / $this->allocated_quantity) * 100;
    }

    /**
     * Scope to get low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('available_quantity < minimum_quantity');
    }

    /**
     * Scope to get overstocked items.
     */
    public function scopeOverstocked($query)
    {
        return $query->whereNotNull('maximum_quantity')
                     ->whereRaw('allocated_quantity > maximum_quantity');
    }
}
