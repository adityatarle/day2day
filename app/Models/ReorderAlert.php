<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReorderAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'current_stock',
        'reorder_point',
        'severity',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the product for this alert.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this alert.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who resolved this alert.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope to get unresolved alerts.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope to get alerts by severity.
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Calculate severity based on stock level vs reorder point.
     */
    public static function calculateSeverity(float $currentStock, float $reorderPoint): string
    {
        if ($currentStock <= 0) {
            return 'critical';
        }

        $percentage = ($currentStock / $reorderPoint) * 100;

        if ($percentage <= 25) {
            return 'critical';
        } elseif ($percentage <= 50) {
            return 'high';
        } elseif ($percentage <= 75) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Mark alert as resolved.
     */
    public function resolve(User $user, ?string $notes = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $user->id,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Generate alerts for products below reorder point.
     */
    public static function generateAlerts(): int
    {
        $count = 0;
        
        $configs = ReorderPointConfig::all();

        foreach ($configs as $config) {
            $currentStock = $config->product->getCurrentStock($config->branch_id);

            if ($currentStock > $config->reorder_point) {
                // Check if there's an unresolved alert and resolve it
                static::where('product_id', $config->product_id)
                     ->where('branch_id', $config->branch_id)
                     ->where('is_resolved', false)
                     ->update([
                         'is_resolved' => true,
                         'resolved_at' => now(),
                         'resolution_notes' => 'Stock level restored above reorder point',
                     ]);
                continue;
            }

            // Check if there's already an unresolved alert
            $existingAlert = static::where('product_id', $config->product_id)
                                  ->where('branch_id', $config->branch_id)
                                  ->where('is_resolved', false)
                                  ->first();

            if ($existingAlert) {
                // Update existing alert if stock has changed
                if ($existingAlert->current_stock != $currentStock) {
                    $existingAlert->update([
                        'current_stock' => $currentStock,
                        'severity' => static::calculateSeverity($currentStock, $config->reorder_point),
                    ]);
                }
                continue;
            }

            // Create new alert
            static::create([
                'product_id' => $config->product_id,
                'branch_id' => $config->branch_id,
                'current_stock' => $currentStock,
                'reorder_point' => $config->reorder_point,
                'severity' => static::calculateSeverity($currentStock, $config->reorder_point),
                'is_resolved' => false,
            ]);

            $count++;
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
     * Get severity display name.
     */
    public function getSeverityDisplayName(): string
    {
        return ucfirst($this->severity);
    }
}
