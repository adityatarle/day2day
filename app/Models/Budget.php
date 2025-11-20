<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_period_id',
        'category',
        'subcategory',
        'budgeted_amount',
        'actual_amount',
        'notes',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
    ];

    /**
     * Get the financial period this budget belongs to
     */
    public function financialPeriod(): BelongsTo
    {
        return $this->belongsTo(FinancialPeriod::class);
    }

    /**
     * Get variance (actual - budgeted)
     */
    public function getVarianceAttribute(): float
    {
        return $this->actual_amount - $this->budgeted_amount;
    }

    /**
     * Get variance percentage
     */
    public function getVariancePercentageAttribute(): float
    {
        if ($this->budgeted_amount == 0) {
            return 0;
        }
        
        return ($this->variance / $this->budgeted_amount) * 100;
    }

    /**
     * Check if budget is over
     */
    public function isOverBudget(): bool
    {
        return $this->actual_amount > $this->budgeted_amount;
    }

    /**
     * Check if budget is under
     */
    public function isUnderBudget(): bool
    {
        return $this->actual_amount < $this->budgeted_amount;
    }

    /**
     * Get budget utilization percentage
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->budgeted_amount == 0) {
            return 0;
        }
        
        return ($this->actual_amount / $this->budgeted_amount) * 100;
    }

    /**
     * Update actual amount
     */
    public function updateActualAmount(float $amount): bool
    {
        return $this->update(['actual_amount' => $amount]);
    }

    /**
     * Add to actual amount
     */
    public function addToActualAmount(float $amount): bool
    {
        return $this->update(['actual_amount' => $this->actual_amount + $amount]);
    }
}
