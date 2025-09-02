<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'vendor_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'user_id',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /**
     * Get the customer for this credit transaction.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vendor for this credit transaction.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user who recorded this credit transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get credit transactions by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get credit transactions for a specific customer.
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to get credit transactions for a specific vendor.
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope to get credit transactions within a date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Check if this is a credit given transaction.
     */
    public function isCreditGiven(): bool
    {
        return $this->type === 'credit_given';
    }

    /**
     * Check if this is a credit received transaction.
     */
    public function isCreditReceived(): bool
    {
        return $this->type === 'credit_received';
    }

    /**
     * Check if this is a credit paid transaction.
     */
    public function isCreditPaid(): bool
    {
        return $this->type === 'credit_paid';
    }

    /**
     * Check if this is a credit received payment transaction.
     */
    public function isCreditReceivedPayment(): bool
    {
        return $this->type === 'credit_received_payment';
    }

    /**
     * Get the transaction type display name.
     */
    public function getTypeDisplayName(): string
    {
        return match($this->type) {
            'credit_given' => 'Credit Given',
            'credit_received' => 'Credit Received',
            'credit_paid' => 'Credit Paid',
            'credit_received_payment' => 'Credit Payment Received',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the entity name (customer or vendor).
     */
    public function getEntityName(): string
    {
        if ($this->customer) {
            return $this->customer->name;
        }
        if ($this->vendor) {
            return $this->vendor->name;
        }
        return 'Unknown';
    }
}