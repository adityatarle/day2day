<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'gst_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the products supplied by this vendor.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_vendors')
                    ->withPivot(['supply_price', 'is_primary_supplier'])
                    ->withTimestamps();
    }

    /**
     * Get the purchase orders from this vendor.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the credit transactions with this vendor.
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /**
     * Scope to get only active vendors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the current credit balance with this vendor.
     */
    public function getCreditBalance(): float
    {
        return $this->creditTransactions()
                    ->whereIn('type', ['credit_received', 'credit_paid'])
                    ->get()
                    ->reduce(function ($carry, $transaction) {
                        if ($transaction->type === 'credit_received') {
                            return $carry + $transaction->amount;
                        } else {
                            return $carry - $transaction->amount;
                        }
                    }, 0);
    }

    /**
     * Check if this vendor is a primary supplier for a product.
     */
    public function isPrimarySupplierFor(Product $product): bool
    {
        return $this->products()
                    ->where('product_id', $product->id)
                    ->wherePivot('is_primary_supplier', true)
                    ->exists();
    }

    /**
     * Get the local purchases from this vendor.
     */
    public function localPurchases(): HasMany
    {
        return $this->hasMany(LocalPurchase::class);
    }

    /**
     * Add a product to this vendor with optional pricing.
     */
    public function addProduct(int $productId, float $supplyPrice = null, bool $isPrimarySupplier = false): void
    {
        $this->products()->syncWithoutDetaching([
            $productId => [
                'supply_price' => $supplyPrice,
                'is_primary_supplier' => $isPrimarySupplier,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Check if this vendor can supply a product (either has specific pricing or can supply any product).
     */
    public function canSupplyProduct(Product $product): bool
    {
        // In the improved workflow, any vendor can supply any product
        return true;
    }
}