<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchPriceAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'product_id',
        'branch_id',
        'original_price',
        'adjusted_price',
        'discount_percentage',
        'effective_from',
        'effective_until',
        'reason',
        'is_active',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'adjusted_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the batch for this price adjustment.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the product for this price adjustment.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this price adjustment.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this adjustment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active adjustments.
     */
    public function scopeActive($query)
    {
        $today = now()->toDateString();
        return $query->where('is_active', true)
                     ->where('effective_from', '<=', $today)
                     ->where('effective_until', '>=', $today);
    }

    /**
     * Check if adjustment is currently effective.
     */
    public function isEffective(): bool
    {
        $today = now()->toDateString();
        return $this->is_active 
               && $this->effective_from->lte($today) 
               && $this->effective_until->gte($today);
    }

    /**
     * Apply automatic price reduction based on days until expiry.
     */
    public static function applyNearExpiryDiscount(Batch $batch): ?self
    {
        if (!$batch->expiry_date) {
            return null;
        }

        $daysUntilExpiry = now()->startOfDay()->diffInDays($batch->expiry_date, false);

        // Don't apply discount if already expired or too far from expiry
        if ($daysUntilExpiry < 0 || $daysUntilExpiry > 7) {
            return null;
        }

        // Calculate discount percentage based on days remaining
        $discountPercentage = match(true) {
            $daysUntilExpiry <= 1 => 50, // 50% off for 1 day or less
            $daysUntilExpiry <= 2 => 40, // 40% off for 2 days
            $daysUntilExpiry <= 3 => 30, // 30% off for 3 days
            $daysUntilExpiry <= 5 => 20, // 20% off for 4-5 days
            $daysUntilExpiry <= 7 => 10, // 10% off for 6-7 days
            default => 0,
        };

        if ($discountPercentage <= 0) {
            return null;
        }

        // Get original price from product
        $originalPrice = $batch->product->selling_price;
        $adjustedPrice = $originalPrice * (1 - ($discountPercentage / 100));

        // Check if adjustment already exists
        $existing = static::where('batch_id', $batch->id)
                         ->where('reason', 'near_expiry')
                         ->where('is_active', true)
                         ->first();

        if ($existing) {
            // Update existing adjustment
            $existing->update([
                'discount_percentage' => $discountPercentage,
                'adjusted_price' => $adjustedPrice,
                'effective_until' => $batch->expiry_date,
            ]);
            return $existing;
        }

        // Create new adjustment
        $adjustment = static::create([
            'batch_id' => $batch->id,
            'product_id' => $batch->product_id,
            'branch_id' => $batch->branch_id,
            'original_price' => $originalPrice,
            'adjusted_price' => $adjustedPrice,
            'discount_percentage' => $discountPercentage,
            'effective_from' => now()->toDateString(),
            'effective_until' => $batch->expiry_date,
            'reason' => 'near_expiry',
            'is_active' => true,
            'notes' => "Automatic discount applied - {$daysUntilExpiry} days until expiry",
        ]);

        // Mark batch as having near-expiry discount
        $batch->update(['near_expiry_discount_applied' => true]);

        return $adjustment;
    }

    /**
     * Apply discounts to all near-expiry batches.
     */
    public static function applyAutomaticDiscounts(): int
    {
        $count = 0;

        $batches = Batch::where('status', 'active')
                       ->whereNotNull('expiry_date')
                       ->where('expiry_date', '>=', now())
                       ->where('expiry_date', '<=', now()->addDays(7))
                       ->where('current_quantity', '>', 0)
                       ->get();

        foreach ($batches as $batch) {
            $adjustment = static::applyNearExpiryDiscount($batch);
            if ($adjustment) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get adjusted price for a batch.
     */
    public static function getAdjustedPrice(int $batchId): ?float
    {
        $adjustment = static::where('batch_id', $batchId)
                           ->active()
                           ->orderBy('discount_percentage', 'desc')
                           ->first();

        return $adjustment?->adjusted_price;
    }

    /**
     * Deactivate expired adjustments.
     */
    public static function deactivateExpired(): int
    {
        return static::where('is_active', true)
                    ->where('effective_until', '<', now()->toDateString())
                    ->update(['is_active' => false]);
    }
}
