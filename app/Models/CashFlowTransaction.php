<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashFlowTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'transaction_date',
        'reference_type',
        'reference_id',
        'description',
        'amount',
        'flow_type',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the category this transaction belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CashFlowCategory::class, 'category_id');
    }

    /**
     * Get the branch this transaction belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this transaction
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
     * Scope for inflow transactions
     */
    public function scopeInflow($query)
    {
        return $query->where('flow_type', 'inflow');
    }

    /**
     * Scope for outflow transactions
     */
    public function scopeOutflow($query)
    {
        return $query->where('flow_type', 'outflow');
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for category type
     */
    public function scopeCategoryType($query, string $type)
    {
        return $query->whereHas('category', function ($q) use ($type) {
            $q->where('type', $type);
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
     * Get formatted amount with sign
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->flow_type === 'inflow' ? '+' : '-';
        return $sign . '₹' . number_format($this->amount, 2);
    }

    /**
     * Check if this is an inflow transaction
     */
    public function isInflow(): bool
    {
        return $this->flow_type === 'inflow';
    }

    /**
     * Check if this is an outflow transaction
     */
    public function isOutflow(): bool
    {
        return $this->flow_type === 'outflow';
    }

    /**
     * Create cash flow transaction from order
     */
    public static function createFromOrder(Order $order): self
    {
        $category = CashFlowCategory::where('name', 'Sales Revenue')->first();
        
        if (!$category) {
            $category = CashFlowCategory::create([
                'name' => 'Sales Revenue',
                'type' => 'operating',
                'subtype' => 'revenue',
                'is_positive_flow' => true,
                'description' => 'Revenue from sales transactions',
            ]);
        }
        
        return self::create([
            'category_id' => $category->id,
            'transaction_date' => $order->created_at->toDateString(),
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'description' => "Sale - Order #{$order->order_number}",
            'amount' => $order->total_amount,
            'flow_type' => 'inflow',
            'branch_id' => $order->branch_id,
            'created_by' => $order->user_id,
        ]);
    }

    /**
     * Create cash flow transaction from purchase order
     */
    public static function createFromPurchaseOrder(PurchaseOrder $purchaseOrder): self
    {
        $category = CashFlowCategory::where('name', 'Purchase Payments')->first();
        
        if (!$category) {
            $category = CashFlowCategory::create([
                'name' => 'Purchase Payments',
                'type' => 'operating',
                'subtype' => 'expense',
                'is_positive_flow' => false,
                'description' => 'Payments made for purchases',
            ]);
        }
        
        return self::create([
            'category_id' => $category->id,
            'transaction_date' => $purchaseOrder->created_at->toDateString(),
            'reference_type' => 'purchase_order',
            'reference_id' => $purchaseOrder->id,
            'description' => "Purchase - PO #{$purchaseOrder->po_number}",
            'amount' => $purchaseOrder->total_amount,
            'flow_type' => 'outflow',
            'branch_id' => $purchaseOrder->branch_id,
            'created_by' => $purchaseOrder->user_id,
        ]);
    }

    /**
     * Create cash flow transaction from expense
     */
    public static function createFromExpense(Expense $expense): self
    {
        $category = CashFlowCategory::where('name', $expense->category->name)->first();
        
        if (!$category) {
            $category = CashFlowCategory::create([
                'name' => $expense->category->name,
                'type' => 'operating',
                'subtype' => 'expense',
                'is_positive_flow' => false,
                'description' => "Expense category: {$expense->category->name}",
            ]);
        }
        
        return self::create([
            'category_id' => $category->id,
            'transaction_date' => $expense->expense_date,
            'reference_type' => 'expense',
            'reference_id' => $expense->id,
            'description' => "Expense - {$expense->description}",
            'amount' => $expense->amount,
            'flow_type' => 'outflow',
            'branch_id' => $expense->branch_id,
            'created_by' => $expense->user_id,
        ]);
    }
}
