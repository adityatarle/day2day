<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonalAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'category',
        'product_id',
        'demand_multiplier',
        'description',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'demand_multiplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product for this seasonal adjustment.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get active seasonal adjustments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get current seasonal adjustments (within date range).
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today)
                     ->where('is_active', true);
    }

    /**
     * Check if this adjustment is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        $today = now()->toDateString();
        return $this->is_active 
               && $this->start_date->lte($today) 
               && $this->end_date->gte($today);
    }

    /**
     * Get seasonal factor for a specific product.
     */
    public static function getSeasonalFactor(int $productId, ?string $category = null): float
    {
        $query = static::current();

        // Product-specific adjustment has highest priority
        $productAdjustment = $query->where('product_id', $productId)->first();
        if ($productAdjustment) {
            return $productAdjustment->demand_multiplier;
        }

        // Category-specific adjustment
        if ($category) {
            $categoryAdjustment = $query->where('category', $category)
                                       ->whereNull('product_id')
                                       ->first();
            if ($categoryAdjustment) {
                return $categoryAdjustment->demand_multiplier;
            }
        }

        // Global adjustment (no product or category specified)
        $globalAdjustment = $query->whereNull('product_id')
                                 ->whereNull('category')
                                 ->first();
        if ($globalAdjustment) {
            return $globalAdjustment->demand_multiplier;
        }

        return 1.0; // No adjustment
    }

    /**
     * Get all active adjustments affecting a product.
     */
    public static function getAdjustmentsForProduct(Product $product): array
    {
        $adjustments = [];

        // Product-specific
        $productAdj = static::current()->where('product_id', $product->id)->get();
        if ($productAdj->isNotEmpty()) {
            $adjustments['product'] = $productAdj;
        }

        // Category-specific
        $categoryAdj = static::current()
                            ->where('category', $product->category)
                            ->whereNull('product_id')
                            ->get();
        if ($categoryAdj->isNotEmpty()) {
            $adjustments['category'] = $categoryAdj;
        }

        // Global
        $globalAdj = static::current()
                          ->whereNull('product_id')
                          ->whereNull('category')
                          ->get();
        if ($globalAdj->isNotEmpty()) {
            $adjustments['global'] = $globalAdj;
        }

        return $adjustments;
    }
}
