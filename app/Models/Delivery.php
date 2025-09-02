<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_boy_id',
        'status',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'delivery_notes',
        'return_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the order for this delivery.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the delivery boy for this delivery.
     */
    public function deliveryBoy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    /**
     * Scope to get deliveries by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get deliveries for a specific delivery boy.
     */
    public function scopeByDeliveryBoy($query, $deliveryBoyId)
    {
        return $query->where('delivery_boy_id', $deliveryBoyId);
    }

    /**
     * Check if delivery is assigned.
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    /**
     * Check if delivery is picked up.
     */
    public function isPickedUp(): bool
    {
        return $this->status === 'picked_up';
    }

    /**
     * Check if delivery is in transit.
     */
    public function isInTransit(): bool
    {
        return $this->status === 'in_transit';
    }

    /**
     * Check if delivery is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if delivery is returned.
     */
    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /**
     * Mark delivery as picked up.
     */
    public function markAsPickedUp(): void
    {
        $this->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);
    }

    /**
     * Mark delivery as in transit.
     */
    public function markAsInTransit(): void
    {
        $this->update(['status' => 'in_transit']);
    }

    /**
     * Mark delivery as delivered.
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark delivery as returned.
     */
    public function markAsReturned(string $reason): void
    {
        $this->update([
            'status' => 'returned',
            'return_reason' => $reason,
        ]);
    }

    /**
     * Get the delivery duration in minutes.
     */
    public function getDeliveryDuration(): ?int
    {
        if (!$this->assigned_at || !$this->delivered_at) {
            return null;
        }

        return $this->assigned_at->diffInMinutes($this->delivered_at);
    }
}