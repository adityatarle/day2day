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
}