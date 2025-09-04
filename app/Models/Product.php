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
        'subcategory',
        'weight_unit',
        'purchase_price',
        'mrp',
        'selling_price',
        'stock_threshold',
        'shelf_life_days',
        'storage_temperature',
        'is_perishable',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_threshold' => 'integer',
        'shelf_life_days' => 'integer',
        'is_perishable' => 'boolean',
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

    /**
     * Get the expense allocations for this product.
     */
    public function expenseAllocations(): HasMany
    {
        return $this->hasMany(ExpenseAllocation::class);
    }

    /**
     * Get total allocated expenses for this product.
     */
    public function getTotalAllocatedExpenses(): float
    {
        return $this->expenseAllocations()->sum('allocated_amount');
    }

    /**
     * Get cost per unit including allocated expenses.
     */
    public function getCostPerUnit($branchId = null): float
    {
        $allocatedExpenses = $this->getTotalAllocatedExpenses();
        $currentStock = $branchId ? $this->getCurrentStock($branchId) : $this->getTotalCurrentStock();
        
        if ($currentStock <= 0) {
            return $this->purchase_price;
        }

        return $this->purchase_price + ($allocatedExpenses / $currentStock);
    }

    /**
     * Get profit margin including allocated expenses.
     */
    public function getProfitMargin($branchId = null): float
    {
        $costPerUnit = $this->getCostPerUnit($branchId);
        $sellingPrice = $branchId ? 
            $this->branches()->where('branches.id', $branchId)->first()?->pivot?->selling_price ?? $this->selling_price :
            $this->selling_price;
            
        return $sellingPrice - $costPerUnit;
    }

    /**
     * Get profit percentage including allocated expenses.
     */
    public function getProfitPercentage($branchId = null): float
    {
        $costPerUnit = $this->getCostPerUnit($branchId);
        
        if ($costPerUnit <= 0) {
            return 0;
        }

        $profitMargin = $this->getProfitMargin($branchId);
        return ($profitMargin / $costPerUnit) * 100;
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            'fruit' => 'Fruits',
            'vegetable' => 'Vegetables', 
            'leafy' => 'Leafy Vegetables',
            'exotic' => 'Exotic Items',
            'herbs' => 'Herbs & Spices',
            'dry_fruits' => 'Dry Fruits',
            'organic' => 'Organic Products',
        ];
    }

    /**
     * Get available subcategories for a category.
     */
    public static function getSubcategories($category): array
    {
        $subcategories = [
            'fruit' => ['citrus', 'tropical', 'seasonal', 'berries', 'stone_fruits'],
            'vegetable' => ['root', 'gourd', 'pod', 'bulb', 'stem'],
            'leafy' => ['greens', 'herbs', 'salads'],
            'exotic' => ['imported', 'specialty', 'rare'],
            'herbs' => ['fresh', 'dried', 'medicinal'],
            'dry_fruits' => ['nuts', 'dried_fruits', 'seeds'],
            'organic' => ['certified', 'natural', 'pesticide_free'],
        ];

        return $subcategories[$category] ?? [];
    }

    /**
     * Check if product is perishable.
     */
    public function isPerishable(): bool
    {
        return $this->is_perishable;
    }

    /**
     * Check if product is fruit.
     */
    public function isFruit(): bool
    {
        return $this->category === 'fruit';
    }

    /**
     * Check if product is vegetable.
     */
    public function isVegetable(): bool
    {
        return in_array($this->category, ['vegetable', 'leafy']);
    }

    /**
     * Check if product is exotic.
     */
    public function isExotic(): bool
    {
        return $this->category === 'exotic';
    }

    /**
     * Get recommended storage temperature.
     */
    public function getStorageTemperature(): string
    {
        return $this->storage_temperature ?? $this->getDefaultStorageTemperature();
    }

    /**
     * Get default storage temperature based on category.
     */
    private function getDefaultStorageTemperature(): string
    {
        $defaults = [
            'fruit' => '2-8°C',
            'vegetable' => '0-4°C',
            'leafy' => '0-2°C',
            'exotic' => '8-12°C',
            'herbs' => '0-2°C',
            'dry_fruits' => 'Room Temperature',
            'organic' => '0-4°C',
        ];

        return $defaults[$this->category] ?? 'Room Temperature';
    }

    /**
     * Calculate expected expiry date for new batch.
     */
    public function calculateExpiryDate(\DateTime $purchaseDate = null): ?\DateTime
    {
        if (!$this->shelf_life_days) {
            return null;
        }

        $startDate = $purchaseDate ?? new \DateTime();
        return $startDate->modify("+{$this->shelf_life_days} days");
    }

    /**
     * Get vendor-specific pricing.
     */
    public function getVendorPrice(int $vendorId): ?float
    {
        $vendor = $this->vendors()->where('vendors.id', $vendorId)->first();
        return $vendor ? $vendor->pivot->vendor_price : null;
    }

    /**
     * Update vendor pricing.
     */
    public function updateVendorPrice(int $vendorId, float $price): void
    {
        $this->vendors()->updateExistingPivot($vendorId, [
            'vendor_price' => $price,
            'updated_at' => now(),
        ]);
    }
}