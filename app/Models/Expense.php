<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id',
        'branch_id',
        'user_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'status',
        'expense_type',
        'allocation_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    /**
     * Get the expense category for this expense.
     */
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * Get the branch for this expense.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who recorded this expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get expenses by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get expenses for a specific branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get expenses for a specific category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    /**
     * Scope to get expenses within a date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Check if expense is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if expense is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if expense is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if expense is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the expense.
     */
    public function approve(): void
    {
        $this->update(['status' => 'approved']);
    }

    /**
     * Reject the expense.
     */
    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    /**
     * Mark expense as paid.
     */
    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    /**
     * Get the expense allocations.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(ExpenseAllocation::class);
    }

    /**
     * Scope to get expenses by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('expense_type', $type);
    }

    /**
     * Check if expense is transport related.
     */
    public function isTransportExpense(): bool
    {
        return $this->expense_type === 'transport';
    }

    /**
     * Check if expense is labour related.
     */
    public function isLabourExpense(): bool
    {
        return $this->expense_type === 'labour';
    }

    /**
     * Check if expense is operational.
     */
    public function isOperationalExpense(): bool
    {
        return $this->expense_type === 'operational';
    }

    /**
     * Get total allocated amount.
     */
    public function getTotalAllocatedAmount(): float
    {
        return $this->allocations->sum('allocated_amount');
    }

    /**
     * Get unallocated amount.
     */
    public function getUnallocatedAmount(): float
    {
        return $this->amount - $this->getTotalAllocatedAmount();
    }
}