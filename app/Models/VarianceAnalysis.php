<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VarianceAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_count_item_id',
        'reconciliation_id',
        'variance_category',
        'root_cause',
        'corrective_action',
        'is_preventable',
        'financial_impact',
        'analyzed_by',
        'analyzed_at',
    ];

    protected $casts = [
        'is_preventable' => 'boolean',
        'financial_impact' => 'decimal:2',
        'analyzed_at' => 'datetime',
    ];

    /**
     * Get the physical count item for this analysis.
     */
    public function physicalCountItem(): BelongsTo
    {
        return $this->belongsTo(PhysicalCountItem::class);
    }

    /**
     * Get the reconciliation for this analysis.
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(StockReconciliation::class);
    }

    /**
     * Get the user who analyzed this variance.
     */
    public function analyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analyzed_by');
    }

    /**
     * Get variance category display name.
     */
    public function getCategoryDisplayName(): string
    {
        return match($this->variance_category) {
            'theft' => 'Theft',
            'spoilage' => 'Spoilage',
            'measurement_error' => 'Measurement Error',
            'data_entry_error' => 'Data Entry Error',
            'shrinkage' => 'Shrinkage',
            'spillage' => 'Spillage',
            'unrecorded_sale' => 'Unrecorded Sale',
            'unrecorded_wastage' => 'Unrecorded Wastage',
            'system_error' => 'System Error',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->variance_category)),
        };
    }
}
