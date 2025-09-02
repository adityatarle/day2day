<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'weight_unit',
        'purchase_price',
        'mrp',
        'selling_price',
        'stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the branches where this product is available.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'product_branches')
                    ->withPivot(['selling_price', 'current_stock', 'is_available_online'])
                    ->withTimestamps();
    }

    /**
     * Get the vendors who supply this product.
     */
    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'product_vendors')
                    ->withPivot(['supply_price', 'is_primary_supplier'])
                    ->withTimestamps();
    }

    /**
     * Get the batches for this product.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * Get the stock movements for this product.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the loss tracking records for this product.
     */
    public function lossTracking(): HasMany
    {
        return $this->hasMany(LossTracking::class);
    }

    /**
     * Get the order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the purchase order items for this product.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the GST rates applicable to this product.
     */
    public function gstRates(): BelongsToMany
    {
        return $this->belongsToMany(GstRate::class, 'product_gst_rates');
    }

    /**
     * Scope to get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get products by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if product is available online at a specific branch.
     */
    public function isAvailableOnline(Branch $branch): bool
    {
        return $this->branches()
                    ->where('branch_id', $branch->id)
                    ->wherePivot('is_available_online', true)
                    ->exists();
    }

    /**
     * Get current stock at a specific branch.
     */
    public function getCurrentStock($branchId): float
    {
        if ($branchId instanceof Branch) {
            $branchId = $branchId->id;
        }
        
        $productBranch = $this->branches()
                              ->where('branch_id', $branchId)
                              ->first();
        
        return $productBranch ? $productBranch->pivot->current_stock : 0;
    }

    /**
     * Update stock at a specific branch.
     */
    public function updateBranchStock($branchId, $newStock): void
    {
        if ($branchId instanceof Branch) {
            $branchId = $branchId->id;
        }
        
        $this->branches()->updateExistingPivot($branchId, [
            'current_stock' => $newStock,
            'updated_at' => now(),
        ]);
    }

    /**
     * Check if product is sold out at a specific branch.
     */
    public function isSoldOut($branch): bool
    {
        $branchId = $branch instanceof Branch ? $branch->id : $branch;
        $currentStock = $this->getCurrentStock($branchId);
        return $currentStock <= $this->stock_threshold;
    }
}