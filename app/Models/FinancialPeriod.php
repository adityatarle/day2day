<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'start_date',
        'end_date',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the user who closed this period
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get budgets for this period
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get general ledger entries for this period
     */
    public function generalLedgerEntries(): HasMany
    {
        return $this->hasMany(GeneralLedger::class)
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date]);
    }

    /**
     * Get GST transactions for this period
     */
    public function gstTransactions(): HasMany
    {
        return $this->hasMany(GstTransaction::class)
            ->whereBetween('invoice_date', [$this->start_date, $this->end_date]);
    }

    /**
     * Get cash flow transactions for this period
     */
    public function cashFlowTransactions(): HasMany
    {
        return $this->hasMany(CashFlowTransaction::class)
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date]);
    }

    /**
     * Scope for active periods
     */
    public function scopeActive($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Scope for closed periods
     */
    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    /**
     * Scope for current period
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->where('is_closed', false);
    }

    /**
     * Get period duration in days
     */
    public function getDurationInDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if period is current
     */
    public function isCurrent(): bool
    {
        $today = now()->toDateString();
        return $this->start_date <= $today && 
               $this->end_date >= $today && 
               !$this->is_closed;
    }

    /**
     * Close the financial period
     */
    public function close(User $user): bool
    {
        if ($this->is_closed) {
            return false;
        }

        return $this->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $user->id,
        ]);
    }

    /**
     * Get revenue for this period
     */
    public function getRevenue(): float
    {
        return $this->generalLedgerEntries()
            ->whereHas('account', function ($query) {
                $query->where('account_type', 'revenue');
            })
            ->sum('credit_amount');
    }

    /**
     * Get expenses for this period
     */
    public function getExpenses(): float
    {
        return $this->generalLedgerEntries()
            ->whereHas('account', function ($query) {
                $query->where('account_type', 'expense');
            })
            ->sum('debit_amount');
    }

    /**
     * Get net profit for this period
     */
    public function getNetProfit(): float
    {
        return $this->getRevenue() - $this->getExpenses();
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMargin(): float
    {
        $revenue = $this->getRevenue();
        if ($revenue == 0) {
            return 0;
        }
        
        return ($this->getNetProfit() / $revenue) * 100;
    }
}
