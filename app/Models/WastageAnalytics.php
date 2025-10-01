<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastageAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'batch_id',
        'wastage_date',
        'quantity_wasted',
        'value_wasted',
        'wastage_reason',
        'root_cause_analysis',
        'corrective_action',
        'is_preventable',
        'recorded_by',
    ];

    protected $casts = [
        'wastage_date' => 'date',
        'quantity_wasted' => 'decimal:2',
        'value_wasted' => 'decimal:2',
        'is_preventable' => 'boolean',
    ];

    /**
     * Get the product for this wastage record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this wastage record.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the batch for this wastage record.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the user who recorded this wastage.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope to get preventable wastage.
     */
    public function scopePreventable($query)
    {
        return $query->where('is_preventable', true);
    }

    /**
     * Scope to get wastage by reason.
     */
    public function scopeByReason($query, string $reason)
    {
        return $query->where('wastage_reason', $reason);
    }

    /**
     * Scope to get wastage within date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('wastage_date', [$startDate, $endDate]);
    }

    /**
     * Record wastage from a batch.
     */
    public static function recordFromBatch(
        Batch $batch,
        float $quantity,
        string $reason,
        ?User $user = null,
        ?string $rootCause = null,
        ?string $correctiveAction = null
    ): self {
        $valueWasted = $quantity * $batch->purchase_price;

        return static::create([
            'product_id' => $batch->product_id,
            'branch_id' => $batch->branch_id,
            'batch_id' => $batch->id,
            'wastage_date' => now()->toDateString(),
            'quantity_wasted' => $quantity,
            'value_wasted' => $valueWasted,
            'wastage_reason' => $reason,
            'root_cause_analysis' => $rootCause,
            'corrective_action' => $correctiveAction,
            'is_preventable' => static::isReasonPreventable($reason),
            'recorded_by' => $user?->id,
        ]);
    }

    /**
     * Determine if wastage reason is preventable.
     */
    private static function isReasonPreventable(string $reason): bool
    {
        $preventableReasons = [
            'expired',
            'overstocked',
            'handling_error',
            'temperature_failure',
        ];

        return in_array($reason, $preventableReasons);
    }

    /**
     * Get wastage statistics for a product.
     */
    public static function getProductStats(int $productId, int $days = 30): array
    {
        $startDate = now()->subDays($days)->toDateString();
        
        $wastage = static::where('product_id', $productId)
                        ->where('wastage_date', '>=', $startDate)
                        ->get();

        if ($wastage->isEmpty()) {
            return [
                'total_quantity' => 0,
                'total_value' => 0,
                'wastage_count' => 0,
                'preventable_percentage' => 0,
                'top_reason' => null,
            ];
        }

        $reasonCounts = $wastage->groupBy('wastage_reason')
                               ->map(fn($group) => $group->count())
                               ->sortDesc();

        return [
            'total_quantity' => $wastage->sum('quantity_wasted'),
            'total_value' => $wastage->sum('value_wasted'),
            'wastage_count' => $wastage->count(),
            'preventable_percentage' => $wastage->isEmpty() ? 0 : 
                ($wastage->where('is_preventable', true)->count() / $wastage->count()) * 100,
            'top_reason' => $reasonCounts->keys()->first(),
            'by_reason' => $reasonCounts,
        ];
    }

    /**
     * Get wastage statistics by category.
     */
    public static function getCategoryStats(int $branchId, int $days = 30): array
    {
        $startDate = now()->subDays($days)->toDateString();
        
        $wastage = static::where('branch_id', $branchId)
                        ->where('wastage_date', '>=', $startDate)
                        ->with('product')
                        ->get();

        $byCategory = $wastage->groupBy(fn($w) => $w->product->category)
                             ->map(function ($group) {
                                 return [
                                     'quantity' => $group->sum('quantity_wasted'),
                                     'value' => $group->sum('value_wasted'),
                                     'count' => $group->count(),
                                 ];
                             })
                             ->sortByDesc('value');

        return $byCategory->toArray();
    }

    /**
     * Get wastage percentage for a product.
     */
    public static function getWastagePercentage(int $productId, int $branchId, int $days = 30): float
    {
        $startDate = now()->subDays($days);
        
        // Get total received quantity
        $totalReceived = StockMovement::where('product_id', $productId)
                                     ->where('branch_id', $branchId)
                                     ->where('created_at', '>=', $startDate)
                                     ->whereIn('type', ['purchase', 'local_purchase', 'transfer_in'])
                                     ->sum('quantity');

        if ($totalReceived <= 0) {
            return 0;
        }

        // Get total wasted quantity
        $totalWasted = static::where('product_id', $productId)
                            ->where('branch_id', $branchId)
                            ->where('wastage_date', '>=', $startDate->toDateString())
                            ->sum('quantity_wasted');

        return ($totalWasted / $totalReceived) * 100;
    }

    /**
     * Get reason display name.
     */
    public function getReasonDisplayName(): string
    {
        return match($this->wastage_reason) {
            'expired' => 'Expired',
            'spoiled' => 'Spoiled',
            'damaged' => 'Damaged',
            'quality_issue' => 'Quality Issue',
            'overstocked' => 'Overstocked',
            'customer_return' => 'Customer Return',
            'handling_error' => 'Handling Error',
            'temperature_failure' => 'Temperature Failure',
            'pest_infestation' => 'Pest Infestation',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->wastage_reason)),
        };
    }
}
