<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state',
        'country',
        'code',
        'delivery_charge',
        'tax_rate',
        'is_active',
    ];

    protected $casts = [
        'delivery_charge' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the branches in this city.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the product pricing for this city.
     */
    public function productPricing(): HasMany
    {
        return $this->hasMany(CityProductPricing::class);
    }

    /**
     * Scope to get only active cities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the effective price for a product in this city.
     */
    public function getProductPrice($productId)
    {
        $pricing = $this->productPricing()
            ->where('product_id', $productId)
            ->where('is_available', true)
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                      ->orWhere('effective_until', '>=', now());
            })
            ->orderBy('effective_from', 'desc')
            ->first();

        return $pricing ? $pricing->selling_price : null;
    }
}
