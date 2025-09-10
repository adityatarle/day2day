<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockFinancialImpact extends Model
{
    use HasFactory;

    protected $fillable = [
        'impactable_type',
        'impactable_id',
        'branch_id',
        'impact_type',
        'amount',
        'impact_category',
        'description',
        'impact_date',
        'is_recoverable',
        'recovered_amount',
        'recovery_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'recovered_amount' => 'decimal:2',
        'impact_date' => 'date',
        'is_recoverable' => 'boolean',
    ];

    /**
     * Get the owning impactable model (query, reconciliation, etc.)
     */
    public function impactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the branch this impact belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope for impacts by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('impact_type', $type);
    }

    /**
     * Scope for impacts by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('impact_category', $category);
    }

    /**
     * Scope for losses
     */
    public function scopeLosses($query)
    {
        return $query->whereIn('impact_category', ['direct_loss', 'indirect_loss']);
    }

    /**
     * Scope for recoverable impacts
     */
    public function scopeRecoverable($query)
    {
        return $query->where('is_recoverable', true);
    }

    /**
     * Scope for impacts by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('impact_date', [$startDate, $endDate]);
    }

    /**
     * Scope for impacts by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Record recovery of amount
     */
    public function recordRecovery(float $amount, ?string $notes = null): bool
    {
        $newRecoveredAmount = $this->recovered_amount + $amount;
        
        if ($newRecoveredAmount > $this->amount) {
            return false; // Cannot recover more than the impact amount
        }

        return $this->update([
            'recovered_amount' => $newRecoveredAmount,
            'recovery_notes' => $notes ? 
                ($this->recovery_notes ? $this->recovery_notes . "\n" . $notes : $notes) : 
                $this->recovery_notes,
        ]);
    }

    /**
     * Get net impact amount (amount - recovered)
     */
    public function getNetImpactAmount(): float
    {
        return $this->amount - $this->recovered_amount;
    }

    /**
     * Get recovery percentage
     */
    public function getRecoveryPercentage(): float
    {
        if ($this->amount == 0) {
            return 0;
        }

        return ($this->recovered_amount / $this->amount) * 100;
    }

    /**
     * Check if fully recovered
     */
    public function isFullyRecovered(): bool
    {
        return $this->recovered_amount >= $this->amount;
    }

    /**
     * Check if partially recovered
     */
    public function isPartiallyRecovered(): bool
    {
        return $this->recovered_amount > 0 && $this->recovered_amount < $this->amount;
    }

    /**
     * Get impact type display name
     */
    public function getImpactTypeDisplayName(): string
    {
        return match($this->impact_type) {
            'loss_damaged' => 'Loss - Damaged Goods',
            'loss_expired' => 'Loss - Expired Goods',
            'loss_shortage' => 'Loss - Shortage',
            'loss_quality' => 'Loss - Quality Issues',
            'gain_excess' => 'Gain - Excess Stock',
            'transport_cost' => 'Transport Cost',
            'handling_cost' => 'Handling Cost',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->impact_type)),
        };
    }

    /**
     * Get impact category display name
     */
    public function getImpactCategoryDisplayName(): string
    {
        return match($this->impact_category) {
            'direct_loss' => 'Direct Loss',
            'indirect_loss' => 'Indirect Loss',
            'cost' => 'Cost',
            'recovery' => 'Recovery',
            default => ucfirst(str_replace('_', ' ', $this->impact_category)),
        };
    }

    /**
     * Get impact type color for UI
     */
    public function getImpactTypeColor(): string
    {
        return match($this->impact_category) {
            'direct_loss' => 'red',
            'indirect_loss' => 'orange',
            'cost' => 'yellow',
            'recovery' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get recovery status color for UI
     */
    public function getRecoveryStatusColor(): string
    {
        if (!$this->is_recoverable) {
            return 'gray';
        }

        if ($this->isFullyRecovered()) {
            return 'green';
        } elseif ($this->isPartiallyRecovered()) {
            return 'yellow';
        } else {
            return 'red';
        }
    }

    /**
     * Static method to get total impact by type for a branch
     */
    public static function getTotalImpactByType(int $branchId, string $type, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = static::where('branch_id', $branchId)
                      ->where('impact_type', $type);

        if ($startDate && $endDate) {
            $query->whereBetween('impact_date', [$startDate, $endDate]);
        }

        return $query->sum('amount');
    }

    /**
     * Static method to get total losses for a branch
     */
    public static function getTotalLosses(int $branchId, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = static::where('branch_id', $branchId)
                      ->whereIn('impact_category', ['direct_loss', 'indirect_loss']);

        if ($startDate && $endDate) {
            $query->whereBetween('impact_date', [$startDate, $endDate]);
        }

        return $query->sum('amount');
    }

    /**
     * Static method to get total recoverable amount for a branch
     */
    public static function getTotalRecoverableAmount(int $branchId, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = static::where('branch_id', $branchId)
                      ->where('is_recoverable', true);

        if ($startDate && $endDate) {
            $query->whereBetween('impact_date', [$startDate, $endDate]);
        }

        return $query->sum('amount') - $query->sum('recovered_amount');
    }
}