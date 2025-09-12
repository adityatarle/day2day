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

    /**
     * Get age of impact in days
     */
    public function getAgeInDays(): int
    {
        return now()->diffInDays($this->impact_date);
    }

    /**
     * Check if impact is old (more than 30 days)
     */
    public function isOld(): bool
    {
        return $this->getAgeInDays() > 30;
    }

    /**
     * Get recovery status text
     */
    public function getRecoveryStatusText(): string
    {
        if (!$this->is_recoverable) {
            return 'Non-recoverable';
        }

        if ($this->isFullyRecovered()) {
            return 'Fully recovered';
        } elseif ($this->isPartiallyRecovered()) {
            return 'Partially recovered';
        } else {
            return 'Pending recovery';
        }
    }

    /**
     * Get remaining recoverable amount
     */
    public function getRemainingRecoverableAmount(): float
    {
        if (!$this->is_recoverable) {
            return 0;
        }

        return max(0, $this->amount - $this->recovered_amount);
    }

    /**
     * Check if impact requires urgent attention (old and unrecovered)
     */
    public function requiresUrgentAttention(): bool
    {
        return $this->is_recoverable && 
               $this->getRemainingRecoverableAmount() > 0 && 
               $this->getAgeInDays() > 14; // Older than 2 weeks
    }

    /**
     * Scope for impacts requiring urgent attention
     */
    public function scopeRequiringUrgentAttention($query)
    {
        return $query->where('is_recoverable', true)
                    ->whereRaw('amount > recovered_amount')
                    ->where('impact_date', '<', now()->subDays(14));
    }

    /**
     * Scope for recent impacts (last 7 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('impact_date', '>=', now()->subDays(7));
    }

    /**
     * Scope for high value impacts (above threshold)
     */
    public function scopeHighValue($query, float $threshold = 1000)
    {
        return $query->where('amount', '>', $threshold);
    }

    /**
     * Static method to get impact summary for dashboard
     */
    public static function getImpactSummary(?int $branchId = null, ?string $period = 'month'): array
    {
        $query = static::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Set date range based on period
        switch ($period) {
            case 'week':
                $query->where('impact_date', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('impact_date', '>=', now()->subMonth());
                break;
            case 'quarter':
                $query->where('impact_date', '>=', now()->subQuarter());
                break;
            case 'year':
                $query->where('impact_date', '>=', now()->subYear());
                break;
        }

        $impacts = $query->get();

        return [
            'total_count' => $impacts->count(),
            'total_amount' => $impacts->sum('amount'),
            'total_recovered' => $impacts->sum('recovered_amount'),
            'net_impact' => $impacts->sum('amount') - $impacts->sum('recovered_amount'),
            'recovery_rate' => $impacts->sum('amount') > 0 ? 
                ($impacts->sum('recovered_amount') / $impacts->sum('amount')) * 100 : 0,
            'by_category' => [
                'direct_loss' => $impacts->where('impact_category', 'direct_loss')->sum('amount'),
                'indirect_loss' => $impacts->where('impact_category', 'indirect_loss')->sum('amount'),
                'cost' => $impacts->where('impact_category', 'cost')->sum('amount'),
                'recovery' => $impacts->where('impact_category', 'recovery')->sum('amount'),
            ],
            'recoverable_stats' => [
                'total_recoverable' => $impacts->where('is_recoverable', true)->sum('amount'),
                'recovered_amount' => $impacts->where('is_recoverable', true)->sum('recovered_amount'),
                'pending_recovery' => $impacts->where('is_recoverable', true)->sum(function ($impact) {
                    return max(0, $impact->amount - $impact->recovered_amount);
                }),
                'urgent_count' => $impacts->filter(fn($i) => $i->requiresUrgentAttention())->count(),
            ],
            'top_impact_types' => $impacts->groupBy('impact_type')
                                        ->map(fn($group) => $group->sum('amount'))
                                        ->sortDesc()
                                        ->take(5)
                                        ->toArray(),
        ];
    }
}