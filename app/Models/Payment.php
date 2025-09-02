<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'payable_id',
        'payable_type',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'user_id',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    /**
     * Get the user who recorded this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payable entity (Order, PurchaseOrder, Expense).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get payments by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get payments by payment method.
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope to get payments within a date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Get the payment type display name.
     */
    public function getTypeDisplayName(): string
    {
        return match($this->type) {
            'customer_payment' => 'Customer Payment',
            'vendor_payment' => 'Vendor Payment',
            'expense_payment' => 'Expense Payment',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the payment method display name.
     */
    public function getPaymentMethodDisplayName(): string
    {
        return match($this->payment_method) {
            'cash' => 'Cash',
            'bank' => 'Bank Transfer',
            'upi' => 'UPI',
            'card' => 'Card',
            'credit' => 'Credit',
            default => ucfirst($this->payment_method),
        };
    }
}