<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpiryAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'product_id',
        'branch_id',
        'expiry_date',
        'days_until_expiry',
        'alert_type',
        'severity',
        'quantity_remaining',
        'is_acknowledged',
        'acknowledged_by',
        'acknowledged_at',
        'action_taken',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'days_until_expiry' => 'integer',
        'quantity_remaining' => 'decimal:2',
        'is_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Get the batch for this expiry alert.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the product for this expiry alert.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this expiry alert.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who acknowledged this alert.
     */
    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Scope to get unacknowledged alerts.
     */
    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    /**
     * Scope to get alerts by severity.
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope to get alerts by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Acknowledge alert with action taken.
     */
    public function acknowledge(User $user, string $actionTaken): void
    {
        $this->update([
            'is_acknowledged' => true,
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
            'action_taken' => $actionTaken,
        ]);
    }

    /**
     * Calculate severity based on days until expiry.
     */
    public static function calculateSeverity(int $daysUntilExpiry): string
    {
        if ($daysUntilExpiry <= 0) {
            return 'critical';
        } elseif ($daysUntilExpiry <= 1) {
            return 'high';
        } elseif ($daysUntilExpiry <= 3) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Determine alert type based on days until expiry.
     */
    public static function determineAlertType(int $daysUntilExpiry): string
    {
        if ($daysUntilExpiry <= 0) {
            return 'expired';
        } elseif ($daysUntilExpiry <= 1) {
            return '1_day';
        } elseif ($daysUntilExpiry <= 3) {
            return '3_days';
        }
        return '7_days';
    }

    /**
     * Generate expiry alerts for all batches.
     */
    public static function generateAlerts(): int
    {
        $count = 0;
        $today = now()->toDateString();

        // Get batches that will expire in the next 7 days or are already expired
        $batches = Batch::where('status', 'active')
                       ->whereNotNull('expiry_date')
                       ->where('expiry_date', '<=', now()->addDays(7))
                       ->where('current_quantity', '>', 0)
                       ->get();

        foreach ($batches as $batch) {
            $daysUntilExpiry = now()->startOfDay()->diffInDays($batch->expiry_date, false);
            $alertType = static::determineAlertType($daysUntilExpiry);
            $severity = static::calculateSeverity($daysUntilExpiry);

            // Check if alert already exists for this batch and type
            $existingAlert = static::where('batch_id', $batch->id)
                                  ->where('alert_type', $alertType)
                                  ->where('is_acknowledged', false)
                                  ->first();

            if ($existingAlert) {
                // Update existing alert
                $existingAlert->update([
                    'days_until_expiry' => $daysUntilExpiry,
                    'severity' => $severity,
                    'quantity_remaining' => $batch->current_quantity,
                ]);
            } else {
                // Create new alert
                static::create([
                    'batch_id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'branch_id' => $batch->branch_id,
                    'expiry_date' => $batch->expiry_date,
                    'days_until_expiry' => $daysUntilExpiry,
                    'alert_type' => $alertType,
                    'severity' => $severity,
                    'quantity_remaining' => $batch->current_quantity,
                    'is_acknowledged' => false,
                ]);
                $count++;
            }
        }

        return $count;
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
     * Get alert type display name.
     */
    public function getAlertTypeDisplayName(): string
    {
        return match($this->alert_type) {
            'expired' => 'Expired',
            '1_day' => 'Expires in 1 Day',
            '3_days' => 'Expires in 3 Days',
            '7_days' => 'Expires in 7 Days',
            default => 'Unknown',
        };
    }
}
