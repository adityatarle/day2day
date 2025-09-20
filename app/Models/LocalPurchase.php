<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class LocalPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'branch_id',
        'manager_id',
        'vendor_id',
        'vendor_name',
        'vendor_phone',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_reference',
        'purchase_date',
        'notes',
        'receipt_path',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'expense_id',
        'purchase_order_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'purchase_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($localPurchase) {
            if (empty($localPurchase->purchase_number)) {
                $localPurchase->purchase_number = self::generatePurchaseNumber($localPurchase->branch_id);
            }
        });
    }

    /**
     * Generate a unique purchase number.
     */
    public static function generatePurchaseNumber($branchId): string
    {
        $branch = Branch::find($branchId);
        $branchCode = $branch ? strtoupper(substr($branch->code, 0, 3)) : 'BR' . $branchId;
        
        $year = now()->format('Y');
        $month = now()->format('m');
        
        // Get the last purchase number for this branch and month
        $lastPurchase = self::where('branch_id', $branchId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastPurchase) {
            // Extract the sequence number from the last purchase number
            preg_match('/LP-' . $branchCode . '-' . $year . $month . '-(\d+)$/', $lastPurchase->purchase_number, $matches);
            $sequence = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('LP-%s-%s%s-%04d', $branchCode, $year, $month, $sequence);
    }

    /**
     * Get the branch associated with this purchase.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the manager who created this purchase.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the vendor associated with this purchase.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user who approved this purchase.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the items in this purchase.
     */
    public function items(): HasMany
    {
        return $this->hasMany(LocalPurchaseItem::class);
    }

    /**
     * Get the expense record associated with this purchase.
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the purchase order being fulfilled by this purchase.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the notifications for this purchase.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(LocalPurchaseNotification::class);
    }

    /**
     * Scope to get purchases by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get purchases for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get purchases within a date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('purchase_date', [$startDate, $endDate]);
    }

    /**
     * Check if the purchase is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the purchase is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the purchase is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the purchase is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Approve the purchase.
     */
    public function approve($userId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the purchase.
     */
    public function reject($userId, $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Mark the purchase as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Calculate totals from items.
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;
        $taxAmount = 0;
        $discountAmount = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->quantity * $item->unit_price;
            $taxAmount += $item->tax_amount;
            $discountAmount += $item->discount_amount;
        }

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $subtotal + $taxAmount - $discountAmount,
        ]);
    }

    /**
     * Get the vendor display name.
     */
    public function getVendorDisplayNameAttribute(): string
    {
        return $this->vendor ? $this->vendor->name : ($this->vendor_name ?: 'Unknown Vendor');
    }

    /**
     * Create an expense record for this purchase.
     */
    public function createExpenseRecord(): void
    {
        if ($this->expense_id) {
            return; // Expense already created
        }

        // Find or create "Local Purchase" expense category
        $category = ExpenseCategory::firstOrCreate(
            ['code' => 'LOCAL_PURCHASE'],
            [
                'name' => 'Local Purchase',
                'description' => 'Expenses for local purchases by branch managers',
            ]
        );

        $expense = Expense::create([
            'expense_category_id' => $category->id,
            'branch_id' => $this->branch_id,
            'user_id' => $this->manager_id,
            'title' => 'Local Purchase: ' . $this->purchase_number,
            'description' => $this->getExpenseDescription(),
            'amount' => $this->total_amount,
            'expense_date' => $this->purchase_date,
            'payment_method' => $this->mapExpensePaymentMethod($this->payment_method),
            'reference_number' => $this->purchase_number,
            'status' => $this->isApproved() ? 'approved' : 'pending',
            'expense_type' => 'operational',
            'allocation_method' => 'none',
            'notes' => $this->notes,
        ]);

        $this->update(['expense_id' => $expense->id]);
    }

    /**
     * Get expense description from items.
     */
    private function getExpenseDescription(): string
    {
        $items = $this->items()->with('product')->get();
        $itemDescriptions = $items->map(function ($item) {
            return $item->product->name . ' (' . $item->quantity . ' ' . $item->unit . ')';
        })->take(3)->implode(', ');

        if ($items->count() > 3) {
            $itemDescriptions .= ' and ' . ($items->count() - 3) . ' more items';
        }

        return 'Purchase of: ' . $itemDescriptions;
    }

    /**
     * Map local purchase payment method to valid Expense enum values.
     */
    private function mapExpensePaymentMethod(?string $method): string
    {
        $method = strtolower((string) $method);

        return match ($method) {
            'cash' => 'cash',
            'upi' => 'upi',
            'card' => 'card',
            'bank_transfer', 'bank' => 'bank',
            // 'credit' or 'other' will be treated as bank for accounting entry
            'credit', 'other', default => 'bank',
        };
    }
}