<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PhysicalCountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'product_id',
        'batch_id',
        'system_quantity',
        'counted_quantity',
        'variance',
        'variance_percentage',
        'value_variance',
        'variance_type',
        'storage_location',
        'barcode_scanned',
        'is_verified',
        'notes',
        'counted_at',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'counted_quantity' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
        'value_variance' => 'decimal:2',
        'is_verified' => 'boolean',
        'counted_at' => 'datetime',
    ];

    /**
     * Get the session for this count item.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PhysicalCountSession::class, 'session_id');
    }

    /**
     * Get the product for this count item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this count item.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the variance analysis for this item.
     */
    public function varianceAnalysis(): HasOne
    {
        return $this->hasOne(VarianceAnalysis::class);
    }

    /**
     * Record counted quantity and calculate variance.
     */
    public function recordCount(float $countedQty, ?string $location = null): void
    {
        $variance = $countedQty - $this->system_quantity;
        $variancePercentage = $this->system_quantity > 0 
            ? abs(($variance / $this->system_quantity) * 100) 
            : 0;

        $varianceType = match(true) {
            $variance > 0 => 'overage',
            $variance < 0 => 'shortage',
            default => 'match',
        };

        $valueVariance = $variance * ($this->product->purchase_price ?? 0);

        $this->update([
            'counted_quantity' => $countedQty,
            'variance' => $variance,
            'variance_percentage' => $variancePercentage,
            'value_variance' => $valueVariance,
            'variance_type' => $varianceType,
            'storage_location' => $location,
            'counted_at' => now(),
        ]);
    }

    /**
     * Verify the count.
     */
    public function verify(): void
    {
        $this->update(['is_verified' => true]);
    }

    /**
     * Check if variance is within tolerance.
     */
    public function isWithinTolerance(float $tolerancePercentage = 2.0): bool
    {
        return abs($this->variance_percentage) <= $tolerancePercentage;
    }

    /**
     * Check if requires investigation.
     */
    public function requiresInvestigation(float $tolerancePercentage = 2.0): bool
    {
        return !$this->isWithinTolerance($tolerancePercentage);
    }

    /**
     * Scope to get items with variance.
     */
    public function scopeWithVariance($query)
    {
        return $query->where('variance_type', '!=', 'match');
    }

    /**
     * Scope to get items outside tolerance.
     */
    public function scopeOutsideTolerance($query, float $tolerancePercentage = 2.0)
    {
        return $query->whereRaw('ABS(variance_percentage) > ?', [$tolerancePercentage]);
    }
}
