<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'order_id',
        'delivery_boy_id',
        'created_by',
        'status',
        'return_reason',
        'reason',
        'refund_amount',
        'total_amount',
        'refund_method',
        'notes',
        'return_date',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'return_date' => 'datetime',
    ];

    /**
     * Get the order for this return.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the delivery boy for this return.
     */
    public function deliveryBoy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    /**
     * Get the return items for this return.
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    /**
     * Scope to get returns by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if return is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if return is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if return is processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Get the total quantity returned.
     */
    public function getTotalQuantityReturned(): float
    {
        return $this->returnItems->sum('returned_quantity');
    }
}