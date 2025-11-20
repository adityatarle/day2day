<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneralLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'transaction_date',
        'reference_type',
        'reference_id',
        'description',
        'debit_amount',
        'credit_amount',
        'balance',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the account this entry belongs to
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Get the branch this entry belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this entry
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for account type
     */
    public function scopeAccountType($query, string $type)
    {
        return $query->whereHas('account', function ($q) use ($type) {
            $q->where('account_type', $type);
        });
    }

    /**
     * Scope for branch
     */
    public function scopeBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for reference type
     */
    public function scopeReferenceType($query, string $type)
    {
        return $query->where('reference_type', $type);
    }

    /**
     * Get net amount (debit - credit)
     */
    public function getNetAmountAttribute(): float
    {
        return $this->debit_amount - $this->credit_amount;
    }

    /**
     * Check if this is a debit entry
     */
    public function isDebit(): bool
    {
        return $this->debit_amount > 0;
    }

    /**
     * Check if this is a credit entry
     */
    public function isCredit(): bool
    {
        return $this->credit_amount > 0;
    }

    /**
     * Get formatted amount for display
     */
    public function getFormattedAmountAttribute(): string
    {
        if ($this->isDebit()) {
            return '₹' . number_format($this->debit_amount, 2);
        } else {
            return '₹' . number_format($this->credit_amount, 2);
        }
    }

    /**
     * Create a general ledger entry
     */
    public static function createEntry(
        int $accountId,
        \Carbon\Carbon $transactionDate,
        string $referenceType,
        int $referenceId,
        string $description,
        float $debitAmount = 0,
        float $creditAmount = 0,
        ?int $branchId = null,
        ?int $createdBy = null
    ): self {
        // Get current balance for the account
        $currentBalance = ChartOfAccount::find($accountId)->getCurrentBalance();
        
        // Calculate new balance
        $newBalance = $currentBalance + $debitAmount - $creditAmount;
        
        return self::create([
            'account_id' => $accountId,
            'transaction_date' => $transactionDate,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'debit_amount' => $debitAmount,
            'credit_amount' => $creditAmount,
            'balance' => $newBalance,
            'branch_id' => $branchId,
            'created_by' => $createdBy ?? auth()->id(),
        ]);
    }

    /**
     * Create double entry (debit and credit)
     */
    public static function createDoubleEntry(
        int $debitAccountId,
        int $creditAccountId,
        \Carbon\Carbon $transactionDate,
        string $referenceType,
        int $referenceId,
        string $description,
        float $amount,
        ?int $branchId = null,
        ?int $createdBy = null
    ): array {
        $entries = [];
        
        // Create debit entry
        $entries[] = self::createEntry(
            $debitAccountId,
            $transactionDate,
            $referenceType,
            $referenceId,
            $description,
            $amount,
            0,
            $branchId,
            $createdBy
        );
        
        // Create credit entry
        $entries[] = self::createEntry(
            $creditAccountId,
            $transactionDate,
            $referenceType,
            $referenceId,
            $description,
            0,
            $amount,
            $branchId,
            $createdBy
        );
        
        return $entries;
    }

    /**
     * Get trial balance for a date range
     */
    public static function getTrialBalance(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return ChartOfAccount::active()
            ->with(['generalLedgerEntries' => function ($query) use ($startDate, $endDate) {
                $query->dateRange($startDate, $endDate);
            }])
            ->get()
            ->map(function ($account) {
                $debits = $account->generalLedgerEntries->sum('debit_amount');
                $credits = $account->generalLedgerEntries->sum('credit_amount');
                
                return [
                    'account' => $account,
                    'debits' => $debits,
                    'credits' => $credits,
                    'balance' => $debits - $credits,
                ];
            });
    }
}
