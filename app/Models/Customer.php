<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}