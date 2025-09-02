<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users associated with this branch.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the orders associated with this branch.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the products available at this branch.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_branches')
                    ->withPivot(['selling_price', 'current_stock', 'is_available_online'])
                    ->withTimestamps();
    }

    /**
     * Scope to get only active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}