<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
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
        return $this->role->permissions();
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->role && $this->role->hasPermission($permissionName);
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user is a branch manager.
     */
    public function isBranchManager(): bool
    {
        return $this->hasRole('branch_manager');
    }

    /**
     * Check if the user is a cashier.
     */
    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    /**
     * Check if the user is a delivery boy.
     */
    public function isDeliveryBoy(): bool
    {
        return $this->hasRole('delivery_boy');
    }

    /**
     * Get the POS sessions for this user.
     */
    public function posSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
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
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope to get users by branch.
     */
    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if user can manage another user based on hierarchy.
     */
    public function canManageUser(User $targetUser): bool
    {
        // Super admin can manage everyone
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Branch manager can manage users in their branch (except super admin and other branch managers)
        if ($this->isBranchManager()) {
            return $this->branch_id === $targetUser->branch_id && 
                   !$targetUser->isSuperAdmin() && 
                   !$targetUser->isBranchManager();
        }

        // Others cannot manage users
        return false;
    }

    /**
     * Check if user can manage a specific branch.
     */
    public function canManageBranch(Branch $branch): bool
    {
        // Super admin can manage all branches
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Branch manager can only manage their own branch
        if ($this->isBranchManager()) {
            return $this->branch_id === $branch->id;
        }

        return false;
    }

    /**
     * Get manageable users for current user.
     */
    public function getManageableUsers()
    {
        if ($this->isSuperAdmin()) {
            return User::with(['role', 'branch'])->get();
        }

        if ($this->isBranchManager()) {
            return User::with(['role', 'branch'])
                ->where('branch_id', $this->branch_id)
                ->whereHas('role', function($q) {
                    $q->whereIn('name', ['cashier', 'delivery_boy']);
                })
                ->get();
        }

        return collect();
    }

    /**
     * Get manageable branches for current user.
     */
    public function getManageableBranches()
    {
        if ($this->isSuperAdmin()) {
            return Branch::with(['users', 'city'])->get();
        }

        if ($this->isBranchManager()) {
            return Branch::with(['users', 'city'])->where('id', $this->branch_id)->get();
        }

        return collect();
    }
}
