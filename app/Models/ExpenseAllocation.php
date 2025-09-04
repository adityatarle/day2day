<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'product_id',
        'allocated_amount',
        'allocation_weight',
        'allocation_date',
        'notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'allocation_weight' => 'decimal:2',
        'allocation_date' => 'date',
    ];

    /**
     * Get the expense for this allocation.
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the product for this allocation.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get allocations for a specific product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to get allocations for a specific expense.
     */
    public function scopeByExpense($query, $expenseId)
    {
        return $query->where('expense_id', $expenseId);
    }

    /**
     * Scope to get allocations within a date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('allocation_date', [$startDate, $endDate]);
    }
}