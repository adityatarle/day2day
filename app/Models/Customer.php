<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'address',
        'type',
        'customer_type',
        'credit_limit',
        'credit_days',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the orders for this customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the credit transactions for this customer.
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /**
     * Scope to get only active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get customers by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the current credit balance for this customer.
     */
    public function getCreditBalance(): float
    {
        return $this->creditTransactions()
                    ->whereIn('type', ['credit_given', 'credit_paid'])
                    ->get()
                    ->reduce(function ($carry, $transaction) {
                        if ($transaction->type === 'credit_given') {
                            return $carry + $transaction->amount;
                        } else {
                            return $carry - $transaction->amount;
                        }
                    }, 0);
    }

    /**
     * Get the total purchase amount for this customer.
     */
    public function getTotalPurchaseAmount(): float
    {
        return $this->orders()
                    ->where('payment_status', 'paid')
                    ->sum('total_amount');
    }

    /**
     * Get the customer's purchase history.
     */
    public function getPurchaseHistory()
    {
        return $this->orders()
                    ->with(['orderItems.product', 'branch'])
                    ->orderBy('order_date', 'desc')
                    ->get();
    }

    /**
     * Get wholesale pricing tiers for this customer.
     */
    public function wholesalePricing(): HasMany
    {
        return $this->hasMany(WholesalePricing::class);
    }

    /**
     * Check if customer is wholesale customer.
     */
    public function isWholesaleCustomer(): bool
    {
        return in_array($this->customer_type, ['regular_wholesale', 'premium_wholesale', 'distributor', 'retailer']);
    }

    /**
     * Check if customer has credit limit.
     */
    public function hasCreditLimit(): bool
    {
        return $this->credit_limit > 0;
    }

    /**
     * Get remaining credit limit.
     */
    public function getRemainingCreditLimit(): float
    {
        if (!$this->hasCreditLimit()) {
            return 0;
        }

        $pendingCreditAmount = $this->orders()
                                   ->where('payment_status', 'pending')
                                   ->sum('total_amount');

        return $this->credit_limit - $pendingCreditAmount;
    }

    /**
     * Check if customer can make credit purchase.
     */
    public function canMakeCreditPurchase(float $amount): bool
    {
        if (!$this->hasCreditLimit()) {
            return false;
        }

        return $this->getRemainingCreditLimit() >= $amount;
    }

    /**
     * Get customer type display name.
     */
    public function getCustomerTypeDisplayName(): string
    {
        $types = [
            'walk_in' => 'Walk-in Customer',
            'regular' => 'Regular Customer',
            'regular_wholesale' => 'Regular Wholesale',
            'premium_wholesale' => 'Premium Wholesale',
            'distributor' => 'Distributor',
            'retailer' => 'Retailer',
        ];

        return $types[$this->customer_type] ?? 'Unknown';
    }
}