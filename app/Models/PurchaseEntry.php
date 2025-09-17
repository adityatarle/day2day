<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PurchaseEntry Model
 * 
 * Tracks individual purchase entries when materials are received from vendors.
 * Each entry represents a specific delivery receipt with detailed quantity tracking.
 */
class PurchaseEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'entry_number',
        'vendor_id',
        'branch_id',
        'user_id',
        'entry_date',
        'delivery_date',
        'delivery_person',
        'delivery_vehicle',
        'delivery_notes',
        'total_expected_quantity',
        'total_received_quantity',
        'total_spoiled_quantity',
        'total_damaged_quantity',
        'total_usable_quantity',
        'total_expected_weight',
        'total_actual_weight',
        'total_weight_difference',
        'entry_status',
        'is_partial_receipt',
        'quality_notes',
        'discrepancy_notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'delivery_date' => 'date',
        'total_expected_quantity' => 'decimal:2',
        'total_received_quantity' => 'decimal:2',
        'total_spoiled_quantity' => 'decimal:2',
        'total_damaged_quantity' => 'decimal:2',
        'total_usable_quantity' => 'decimal:2',
        'total_expected_weight' => 'decimal:3',
        'total_actual_weight' => 'decimal:3',
        'total_weight_difference' => 'decimal:3',
        'is_partial_receipt' => 'boolean',
    ];

    /**
     * Get the purchase order for this entry.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the vendor for this entry.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the branch for this entry.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the purchase entry items for this entry.
     */
    public function purchaseEntryItems(): HasMany
    {
        return $this->hasMany(PurchaseEntryItem::class);
    }

    /**
     * Scope to get entries by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('entry_status', $status);
    }

    /**
     * Scope to get entries for a specific branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get partial receipts.
     */
    public function scopePartialReceipts($query)
    {
        return $query->where('is_partial_receipt', true);
    }

    /**
     * Scope to get complete receipts.
     */
    public function scopeCompleteReceipts($query)
    {
        return $query->where('is_partial_receipt', false);
    }

    /**
     * Check if this is a partial receipt.
     */
    public function isPartialReceipt(): bool
    {
        return $this->is_partial_receipt;
    }

    /**
     * Check if this is a complete receipt.
     */
    public function isCompleteReceipt(): bool
    {
        return !$this->is_partial_receipt;
    }

    /**
     * Get status badge color class.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->entry_status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'received' => 'bg-green-100 text-green-800',
            'partial' => 'bg-orange-100 text-orange-800',
            'discrepancy' => 'bg-red-100 text-red-800',
            'completed' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get status display text.
     */
    public function getStatusDisplayText(): string
    {
        return match($this->entry_status) {
            'draft' => 'Draft',
            'received' => 'Received',
            'partial' => 'Partial Receipt',
            'discrepancy' => 'Discrepancy Found',
            'completed' => 'Completed',
            default => 'Unknown'
        };
    }

    /**
     * Calculate loss percentage.
     */
    public function getLossPercentage(): float
    {
        if ($this->total_received_quantity == 0) {
            return 0;
        }
        
        $totalLoss = $this->total_spoiled_quantity + $this->total_damaged_quantity;
        return ($totalLoss / $this->total_received_quantity) * 100;
    }

    /**
     * Calculate remaining quantity to be received.
     */
    public function getRemainingQuantity(): float
    {
        return max(0, $this->total_expected_quantity - $this->total_received_quantity);
    }

    /**
     * Check if entry has discrepancies.
     */
    public function hasDiscrepancies(): bool
    {
        return $this->total_spoiled_quantity > 0 || 
               $this->total_damaged_quantity > 0 || 
               abs($this->total_weight_difference) > 0.1;
    }

    /**
     * Generate entry number.
     */
    public static function generateEntryNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $count = self::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count() + 1;
        
        return "PE-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}