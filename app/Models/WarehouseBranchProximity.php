<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseBranchProximity extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'branch_id',
        'distance_km',
        'estimated_travel_minutes',
        'standard_transfer_cost',
        'is_primary_supplier',
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'estimated_travel_minutes' => 'integer',
        'standard_transfer_cost' => 'decimal:2',
        'is_primary_supplier' => 'boolean',
    ];

    /**
     * Get the warehouse for this proximity record.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the branch for this proximity record.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Calculate estimated transfer cost based on distance and quantity.
     */
    public function calculateTransferCost(float $quantity, float $costPerKm = 10): float
    {
        // Base cost + distance-based cost + quantity-based cost
        $baseCost = $this->standard_transfer_cost;
        $distanceCost = $this->distance_km * $costPerKm;
        $quantityCost = $quantity * 0.5; // ₹0.50 per kg/unit
        
        return $baseCost + $distanceCost + $quantityCost;
    }

    /**
     * Get estimated delivery time.
     */
    public function getEstimatedDeliveryTime(): \DateTime
    {
        return now()->addMinutes($this->estimated_travel_minutes);
    }

    /**
     * Find closest warehouse to a branch.
     */
    public static function findClosestWarehouse(int $branchId, bool $onlyActive = true): ?self
    {
        $query = static::where('branch_id', $branchId);
        
        if ($onlyActive) {
            $query->whereHas('warehouse', function ($q) {
                $q->where('is_active', true);
            });
        }
        
        return $query->orderBy('distance_km')->first();
    }

    /**
     * Find primary warehouse for a branch.
     */
    public static function findPrimaryWarehouse(int $branchId): ?self
    {
        return static::where('branch_id', $branchId)
                    ->where('is_primary_supplier', true)
                    ->whereHas('warehouse', function ($q) {
                        $q->where('is_active', true);
                    })
                    ->first();
    }

    /**
     * Set as primary warehouse for branch.
     */
    public function setAsPrimary(): void
    {
        // Unset other primary warehouses for this branch
        static::where('branch_id', $this->branch_id)
             ->where('id', '!=', $this->id)
             ->update(['is_primary_supplier' => false]);
        
        $this->update(['is_primary_supplier' => true]);
    }
}
