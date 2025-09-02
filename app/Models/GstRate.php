<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GstRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate',
        'description',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the products that use this GST rate.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_gst_rates');
    }

    /**
     * Scope to get only active GST rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the GST rate as a percentage string.
     */
    public function getRatePercentage(): string
    {
        return $this->rate . '%';
    }

    /**
     * Calculate GST amount for a given price.
     */
    public function calculateGstAmount(float $price): float
    {
        return ($price * $this->rate) / 100;
    }
}