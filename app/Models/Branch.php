<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'city_id',
        'latitude',
        'longitude',
        'outlet_type',
        'operating_hours',
        'pos_enabled',
        'pos_terminal_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'operating_hours' => 'array',
        'pos_enabled' => 'boolean',
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
     * Get the city where this branch is located.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the POS sessions for this branch.
     */
    public function posSessions(): HasMany
    {
        return $this->hasMany(PosSession::class);
    }

    /**
     * Get the current active POS session for this branch.
     */
    public function currentPosSession()
    {
        return $this->posSessions()->where('status', 'active')->first();
    }

    /**
     * Scope to get only active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get branches by outlet type.
     */
    public function scopeByOutletType($query, $type)
    {
        return $query->where('outlet_type', $type);
    }

    /**
     * Scope to get POS enabled branches.
     */
    public function scopePosEnabled($query)
    {
        return $query->where('pos_enabled', true);
    }

    /**
     * Check if the branch is currently open based on operating hours.
     */
    public function isOpen()
    {
        if (!$this->operating_hours) {
            return true; // If no operating hours set, assume always open
        }

        $currentTime = now()->format('H:i');
        $currentDay = strtolower(now()->format('l'));

        $todayHours = $this->operating_hours[$currentDay] ?? null;
        
        if (!$todayHours || !isset($todayHours['open'], $todayHours['close'])) {
            return false; // Closed if no hours defined for today
        }

        return $currentTime >= $todayHours['open'] && $currentTime <= $todayHours['close'];
    }

    /**
     * Get the branch manager for this branch.
     */
    public function manager(): HasOne
    {
        return $this->hasOne(User::class)
            ->whereHas('role', function($q) {
                $q->where('name', 'branch_manager');
            });
    }

    /**
     * Get all cashiers for this branch.
     */
    public function cashiers()
    {
        return $this->users()->whereHas('role', function($q) {
            $q->where('name', 'cashier');
        });
    }

    /**
     * Get all delivery staff for this branch.
     */
    public function deliveryStaff()
    {
        return $this->users()->whereHas('role', function($q) {
            $q->where('name', 'delivery_boy');
        });
    }

    /**
     * Get branch-specific inventory.
     */
    public function inventory()
    {
        return $this->products()->withPivot(['current_stock', 'selling_price', 'is_available_online']);
    }

    /**
     * Get today's sales for this branch.
     */
    public function todaySales()
    {
        return $this->orders()
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    /**
     * Get active POS sessions count.
     */
    public function activePosSessionsCount()
    {
        return $this->posSessions()->where('status', 'active')->count();
    }

    /**
     * Get the local purchases for this branch.
     */
    public function localPurchases(): HasMany
    {
        return $this->hasMany(LocalPurchase::class);
    }
}