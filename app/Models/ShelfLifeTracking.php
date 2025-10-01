<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelfLifeTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'product_id',
        'branch_id',
        'purchase_date',
        'expected_expiry_date',
        'actual_expiry_date',
        'expected_shelf_life_days',
        'actual_shelf_life_days',
        'shelf_life_utilization_percentage',
        'disposal_method',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expected_expiry_date' => 'date',
        'actual_expiry_date' => 'date',
        'expected_shelf_life_days' => 'integer',
        'actual_shelf_life_days' => 'integer',
        'shelf_life_utilization_percentage' => 'decimal:2',
    ];

    /**
     * Get the batch for this shelf life tracking.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the product for this shelf life tracking.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this shelf life tracking.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Create shelf life tracking from a batch.
     */
    public static function trackBatch(Batch $batch): self
    {
        $expectedShelfLife = $batch->product->shelf_life_days ?? 
                           $batch->purchase_date->diffInDays($batch->expiry_date);

        return static::create([
            'batch_id' => $batch->id,
            'product_id' => $batch->product_id,
            'branch_id' => $batch->branch_id,
            'purchase_date' => $batch->purchase_date,
            'expected_expiry_date' => $batch->expiry_date,
            'expected_shelf_life_days' => $expectedShelfLife,
        ]);
    }

    /**
     * Complete shelf life tracking when batch is disposed.
     */
    public function complete(string $disposalMethod, ?\DateTime $actualExpiryDate = null): void
    {
        $actualExpiry = $actualExpiryDate ?? now();
        $actualShelfLife = $this->purchase_date->diffInDays($actualExpiry);
        
        $utilization = $this->expected_shelf_life_days > 0 
            ? ($actualShelfLife / $this->expected_shelf_life_days) * 100 
            : 0;

        $this->update([
            'actual_expiry_date' => $actualExpiry,
            'actual_shelf_life_days' => $actualShelfLife,
            'shelf_life_utilization_percentage' => $utilization,
            'disposal_method' => $disposalMethod,
        ]);
    }

    /**
     * Get shelf life statistics for a product.
     */
    public static function getProductStats(int $productId, int $branchId, int $days = 90): array
    {
        $since = now()->subDays($days);

        $tracking = static::where('product_id', $productId)
                         ->where('branch_id', $branchId)
                         ->whereNotNull('actual_shelf_life_days')
                         ->where('created_at', '>=', $since)
                         ->get();

        if ($tracking->isEmpty()) {
            return [
                'avg_shelf_life_utilization' => 0,
                'avg_actual_shelf_life' => 0,
                'total_batches_tracked' => 0,
                'sold_percentage' => 0,
                'wasted_percentage' => 0,
            ];
        }

        $disposalCounts = $tracking->groupBy('disposal_method')->map->count();
        $totalBatches = $tracking->count();

        return [
            'avg_shelf_life_utilization' => round($tracking->avg('shelf_life_utilization_percentage'), 2),
            'avg_actual_shelf_life' => round($tracking->avg('actual_shelf_life_days'), 1),
            'total_batches_tracked' => $totalBatches,
            'sold_percentage' => ($disposalCounts->get('sold', 0) / $totalBatches) * 100,
            'wasted_percentage' => ($disposalCounts->get('wasted', 0) / $totalBatches) * 100,
            'by_disposal_method' => $disposalCounts,
        ];
    }

    /**
     * Get average shelf life utilization by category.
     */
    public static function getCategoryUtilization(int $branchId, int $days = 90): array
    {
        $since = now()->subDays($days);

        $tracking = static::where('branch_id', $branchId)
                         ->whereNotNull('shelf_life_utilization_percentage')
                         ->where('created_at', '>=', $since)
                         ->with('product')
                         ->get();

        $byCategory = $tracking->groupBy(fn($t) => $t->product->category)
                              ->map(function ($group) {
                                  return [
                                      'avg_utilization' => round($group->avg('shelf_life_utilization_percentage'), 2),
                                      'count' => $group->count(),
                                  ];
                              })
                              ->sortByDesc('avg_utilization');

        return $byCategory->toArray();
    }

    /**
     * Get disposal method display name.
     */
    public function getDisposalMethodDisplayName(): string
    {
        return match($this->disposal_method) {
            'sold' => 'Sold',
            'wasted' => 'Wasted',
            'donated' => 'Donated',
            'returned_to_vendor' => 'Returned to Vendor',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->disposal_method ?? '')),
        };
    }
}
