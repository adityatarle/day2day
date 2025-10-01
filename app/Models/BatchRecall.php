<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchRecall extends Model
{
    use HasFactory;

    protected $fillable = [
        'recall_number',
        'product_id',
        'batch_id',
        'batch_number',
        'recall_reason',
        'severity',
        'recall_date',
        'quantity_recalled',
        'quantity_sold',
        'status',
        'description',
        'corrective_action',
        'affected_customers',
        'customer_notification_sent',
        'initiated_by',
        'initiated_at',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'recall_date' => 'date',
        'quantity_recalled' => 'decimal:2',
        'quantity_sold' => 'decimal:2',
        'affected_customers' => 'array',
        'customer_notification_sent' => 'boolean',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the product for this recall.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this recall.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the user who initiated this recall.
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the user who completed this recall.
     */
    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Scope to get active recalls.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['initiated', 'in_progress']);
    }

    /**
     * Scope to get recalls by severity.
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Generate unique recall number.
     */
    public static function generateRecallNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now())->count() + 1;
        return "RCL-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Initiate a batch recall.
     */
    public static function initiateRecall(
        Batch $batch,
        string $reason,
        string $severity,
        string $description,
        User $user
    ): self {
        // Get orders that included this batch
        $orders = Order::whereHas('orderItems', function ($query) use ($batch) {
            $query->where('batch_id', $batch->id);
        })->where('status', 'completed')->get();

        $affectedCustomers = $orders->pluck('customer_id')->unique()->values()->toArray();
        $quantitySold = $orders->flatMap->orderItems
                              ->where('batch_id', $batch->id)
                              ->sum('quantity');

        $recall = static::create([
            'recall_number' => static::generateRecallNumber(),
            'product_id' => $batch->product_id,
            'batch_id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'recall_reason' => $reason,
            'severity' => $severity,
            'recall_date' => now()->toDateString(),
            'quantity_recalled' => $batch->current_quantity,
            'quantity_sold' => $quantitySold,
            'status' => 'initiated',
            'description' => $description,
            'affected_customers' => $affectedCustomers,
            'customer_notification_sent' => false,
            'initiated_by' => $user->id,
            'initiated_at' => now(),
        ]);

        // Mark batch as recalled
        $batch->update([
            'is_recalled' => true,
            'recall_id' => $recall->id,
            'status' => 'recalled',
        ]);

        return $recall;
    }

    /**
     * Complete the recall process.
     */
    public function complete(User $user, string $correctiveAction): void
    {
        $this->update([
            'status' => 'completed',
            'corrective_action' => $correctiveAction,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);
    }

    /**
     * Cancel the recall.
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);

        // Unmark batch as recalled if it exists
        if ($this->batch) {
            $this->batch->update([
                'is_recalled' => false,
                'recall_id' => null,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Get affected customers count.
     */
    public function getAffectedCustomersCount(): int
    {
        return count($this->affected_customers ?? []);
    }

    /**
     * Get recall effectiveness percentage.
     */
    public function getRecallEffectiveness(): float
    {
        $total = $this->quantity_recalled + $this->quantity_sold;
        
        if ($total <= 0) {
            return 0;
        }

        return ($this->quantity_recalled / $total) * 100;
    }

    /**
     * Get status badge color class.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'initiated' => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get severity badge color class.
     */
    public function getSeverityBadgeClass(): string
    {
        return match($this->severity) {
            'critical' => 'bg-red-100 text-red-800',
            'high' => 'bg-orange-100 text-orange-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'low' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get reason display name.
     */
    public function getReasonDisplayName(): string
    {
        return match($this->recall_reason) {
            'quality_issue' => 'Quality Issue',
            'contamination' => 'Contamination',
            'mislabeling' => 'Mislabeling',
            'foreign_object' => 'Foreign Object',
            'allergen' => 'Allergen',
            'vendor_issue' => 'Vendor Issue',
            'regulatory' => 'Regulatory',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->recall_reason)),
        };
    }
}
