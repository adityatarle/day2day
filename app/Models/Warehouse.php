<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'branch_id',
        'address',
        'city',
        'state',
        'pincode',
        'storage_capacity',
        'current_utilization',
        'storage_zones',
        'manager_name',
        'manager_phone',
        'is_active',
    ];

    protected $casts = [
        'storage_capacity' => 'decimal:2',
        'current_utilization' => 'decimal:2',
        'storage_zones' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the branch for this warehouse.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the stock allocations for this warehouse.
     */
    public function warehouseStock(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /**
     * Get outgoing transfers from this warehouse.
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    /**
     * Get incoming transfers to this warehouse.
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    /**
     * Get proximity data to branches.
     */
    public function branchProximity(): HasMany
    {
        return $this->hasMany(WarehouseBranchProximity::class);
    }

    /**
     * Get performance metrics.
     */
    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(WarehousePerformanceMetric::class);
    }

    /**
     * Scope to get active warehouses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get warehouses by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if warehouse is central warehouse.
     */
    public function isCentral(): bool
    {
        return $this->type === 'central';
    }

    /**
     * Get utilization percentage.
     */
    public function getUtilizationPercentage(): float
    {
        if (!$this->storage_capacity || $this->storage_capacity <= 0) {
            return 0;
        }

        return ($this->current_utilization / $this->storage_capacity) * 100;
    }

    /**
     * Get available capacity.
     */
    public function getAvailableCapacity(): float
    {
        return max(0, $this->storage_capacity - $this->current_utilization);
    }

    /**
     * Update utilization based on current stock.
     */
    public function updateUtilization(): void
    {
        $totalStock = $this->warehouseStock()->sum('available_quantity');
        $this->update(['current_utilization' => $totalStock]);
    }

    /**
     * Get stock for a specific product.
     */
    public function getProductStock(int $productId): ?WarehouseStock
    {
        return $this->warehouseStock()
                    ->where('product_id', $productId)
                    ->first();
    }

    /**
     * Find optimal warehouse for a product based on proximity and availability.
     */
    public static function findOptimalWarehouse(int $productId, int $branchId): ?self
    {
        // Get product to check storage requirements
        $product = Product::find($productId);
        if (!$product) {
            return null;
        }

        // If product has preferred warehouse, check availability there first
        if ($product->preferred_warehouse_id) {
            $preferred = static::find($product->preferred_warehouse_id);
            $stock = $preferred->getProductStock($productId);
            if ($stock && $stock->available_quantity > 0) {
                return $preferred;
            }
        }

        // Find warehouses with stock, ordered by proximity to branch
        $warehousesWithStock = static::active()
            ->whereHas('warehouseStock', function ($query) use ($productId) {
                $query->where('product_id', $productId)
                     ->where('available_quantity', '>', 0);
            })
            ->get();

        if ($warehousesWithStock->isEmpty()) {
            return null;
        }

        // Get proximity data
        $proximities = WarehouseBranchProximity::where('branch_id', $branchId)
            ->whereIn('warehouse_id', $warehousesWithStock->pluck('id'))
            ->orderBy('distance_km')
            ->get();

        // Return closest warehouse with stock
        return $proximities->first()?->warehouse;
    }

    /**
     * Get products stored in this warehouse.
     */
    public function getStoredProducts()
    {
        return Product::whereHas('warehouseStock', function ($query) {
            $query->where('warehouse_id', $this->id)
                  ->where('available_quantity', '>', 0);
        })->get();
    }

    /**
     * Calculate total value of stock in warehouse.
     */
    public function getTotalStockValue(): float
    {
        return $this->warehouseStock()
                    ->with('product')
                    ->get()
                    ->sum(function ($stock) {
                        return $stock->available_quantity * ($stock->product->purchase_price ?? 0);
                    });
    }
}
