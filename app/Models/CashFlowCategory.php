<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashFlowCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subtype',
        'is_positive_flow',
        'description',
    ];

    protected $casts = [
        'is_positive_flow' => 'boolean',
    ];

    /**
     * Get cash flow transactions for this category
     */
    public function cashFlowTransactions(): HasMany
    {
        return $this->hasMany(CashFlowTransaction::class, 'category_id');
    }

    /**
     * Scope for operating activities
     */
    public function scopeOperating($query)
    {
        return $query->where('type', 'operating');
    }

    /**
     * Scope for investing activities
     */
    public function scopeInvesting($query)
    {
        return $query->where('type', 'investing');
    }

    /**
     * Scope for financing activities
     */
    public function scopeFinancing($query)
    {
        return $query->where('type', 'financing');
    }

    /**
     * Get total inflow for a period
     */
    public function getTotalInflow(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): float
    {
        return $this->cashFlowTransactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('flow_type', 'inflow')
            ->sum('amount');
    }

    /**
     * Get total outflow for a period
     */
    public function getTotalOutflow(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): float
    {
        return $this->cashFlowTransactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('flow_type', 'outflow')
            ->sum('amount');
    }

    /**
     * Get net cash flow for a period
     */
    public function getNetCashFlow(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): float
    {
        return $this->getTotalInflow($startDate, $endDate) - $this->getTotalOutflow($startDate, $endDate);
    }
}
