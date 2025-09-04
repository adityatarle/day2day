<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesalePricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_id',
        'customer_type',
        'min_quantity',
        'max_quantity',
        'wholesale_price',
        'discount_percentage',
        'is_active',
    ];

    protected $casts = [
        'min_quantity' => 'decimal:2',
        'max_quantity' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product for this pricing tier.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the customer for this pricing tier (if customer-specific).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope to get only active pricing tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get pricing for a specific product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to get pricing for a specific customer.
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to get pricing for a customer type.
     */
    public function scopeByCustomerType($query, $customerType)
    {
        return $query->where('customer_type', $customerType);
    }

    /**
     * Check if quantity falls within this pricing tier.
     */
    public function isApplicableForQuantity(float $quantity): bool
    {
        $minCheck = $quantity >= $this->min_quantity;
        $maxCheck = !$this->max_quantity || $quantity <= $this->max_quantity;
        
        return $minCheck && $maxCheck;
    }

    /**
     * Calculate final price with discount.
     */
    public function calculateFinalPrice(float $quantity): float
    {
        $basePrice = $quantity * $this->wholesale_price;
        
        if ($this->discount_percentage > 0) {
            $discount = ($basePrice * $this->discount_percentage) / 100;
            return $basePrice - $discount;
        }

        return $basePrice;
    }

    /**
     * Calculate savings compared to regular price.
     */
    public function calculateSavings(float $quantity, float $regularPrice): float
    {
        $wholesaleTotal = $this->calculateFinalPrice($quantity);
        $regularTotal = $quantity * $regularPrice;
        
        return $regularTotal - $wholesaleTotal;
    }

    /**
     * Get available customer types.
     */
    public static function getCustomerTypes(): array
    {
        return [
            'regular_wholesale' => 'Regular Wholesale',
            'premium_wholesale' => 'Premium Wholesale',
            'distributor' => 'Distributor',
            'retailer' => 'Retailer',
        ];
    }
}