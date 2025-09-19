<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'password',
        'role_id',
        'branch_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'last_login_at'     => 'datetime',
        ];
    }

    /**
     * Get the user's role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user's branch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user's permissions through their role.
     */
    public function permissions(): BelongsToMany
    {
        // assuming Role model has belongsToMany(Permission::class)
        return $this->role
            ? $this->role->permissions()
            : $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->role
            ? $this->role->permissions()->where('name', $permissionName)->exists()
            : false;
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Role helpers
     */
    public function isSuperAdmin(): bool { return $this->hasRole('super_admin'); }
    public function isAdmin(): bool { return $this->hasRole('admin'); }
    public function isBranchManager(): bool { return $this->hasRole('branch_manager'); }
    public function isCashier(): bool { return $this->hasRole('cashier'); }
    public function isDeliveryBoy(): bool { return $this->hasRole('delivery_boy'); }

    /**
     * Get the POS sessions for this user.
     */
    public function posSessions(): HasMany
    {
        return $this->hasMany(\App\Models\PosSession::class);
    }

    /**
     * Get the current active POS session for this user.
     */
    public function currentPosSession()
    {
        return $this->posSessions()->where('status', 'active')->first();
    }

    /**
     * Get the orders created/handled by this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the local purchases created by this user as a manager.
     */
    public function localPurchases(): HasMany
    {
        return $this->hasMany(LocalPurchase::class, 'manager_id');
    }

    /**
     * Get the local purchases approved by this user.
     */
    public function approvedLocalPurchases(): HasMany
    {
        return $this->hasMany(LocalPurchase::class, 'approved_by');
    }

    /**
     * Get the local purchase notifications for this user.
     */
    public function localPurchaseNotifications(): HasMany
    {
        return $this->hasMany(LocalPurchaseNotification::class);
    }
}
