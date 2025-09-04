<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CityProductPricing extends Model
{
    use HasFactory;

    protected $table = 'city_product_pricing';

    protected $fillable = [
        'city_id',
        'product_id',
        'selling_price',
        'mrp',
        'discount_percentage',
        'is_available',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_available' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    /**
     * Get the city that owns the pricing.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the product that owns the pricing.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get only available pricing.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to get pricing effective for a specific date.
     */
    public function scopeEffectiveOn($query, $date = null)
    {
        $date = $date ?? now();
        
        return $query->where('effective_from', '<=', $date)
                    ->where(function ($query) use ($date) {
                        $query->whereNull('effective_until')
                              ->orWhere('effective_until', '>=', $date);
                    });
    }

    /**
     * Get the final price after applying discount.
     */
    public function getFinalPrice()
    {
        return $this->selling_price * (1 - $this->discount_percentage / 100);
    }
}
