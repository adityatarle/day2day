<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'account_subtype',
        'parent_account_id',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get parent account
     */
    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id');
    }

    /**
     * Get child accounts
     */
    public function childAccounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id');
    }

    /**
     * Get general ledger entries
     */
    public function generalLedgerEntries(): HasMany
    {
        return $this->hasMany(GeneralLedger::class);
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by account type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope by account subtype
     */
    public function scopeBySubtype($query, string $subtype)
    {
        return $query->where('account_subtype', $subtype);
    }

    /**
     * Get current balance for this account
     */
    public function getCurrentBalance(): float
    {
        return $this->generalLedgerEntries()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;
    }

    /**
     * Get balance for a specific date
     */
    public function getBalanceAtDate(\Carbon\Carbon $date): float
    {
        return $this->generalLedgerEntries()
            ->where('transaction_date', '<=', $date)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;
    }

    /**
     * Get total debits for a period
     */
    public function getTotalDebits(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): float
    {
        return $this->generalLedgerEntries()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('debit_amount');
    }

    /**
     * Get total credits for a period
     */
    public function getTotalCredits(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): float
    {
        return $this->generalLedgerEntries()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('credit_amount');
    }

    /**
     * Check if account is a revenue account
     */
    public function isRevenueAccount(): bool
    {
        return $this->account_type === 'revenue';
    }

    /**
     * Check if account is an expense account
     */
    public function isExpenseAccount(): bool
    {
        return $this->account_type === 'expense';
    }

    /**
     * Check if account is an asset account
     */
    public function isAssetAccount(): bool
    {
        return $this->account_type === 'asset';
    }

    /**
     * Check if account is a liability account
     */
    public function isLiabilityAccount(): bool
    {
        return $this->account_type === 'liability';
    }

    /**
     * Check if account is an equity account
     */
    public function isEquityAccount(): bool
    {
        return $this->account_type === 'equity';
    }

    /**
     * Get account hierarchy path
     */
    public function getAccountPath(): string
    {
        $path = [$this->account_name];
        $parent = $this->parentAccount;
        
        while ($parent) {
            array_unshift($path, $parent->account_name);
            $parent = $parent->parentAccount;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Get all descendant accounts
     */
    public function getAllDescendants(): \Illuminate\Database\Eloquent\Collection
    {
        $descendants = collect();
        
        foreach ($this->childAccounts as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    /**
     * Get total balance including all descendants
     */
    public function getTotalBalanceWithDescendants(): float
    {
        $balance = $this->getCurrentBalance();
        
        foreach ($this->getAllDescendants() as $descendant) {
            $balance += $descendant->getCurrentBalance();
        }
        
        return $balance;
    }
}
