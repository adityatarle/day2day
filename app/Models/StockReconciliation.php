<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'branch_id',
        'performed_by',
        'approved_by',
        'status',
        'reconciliation_date',
        'notes',
    ];

    protected $casts = [
        'reconciliation_date' => 'datetime',
    ];

    /**
     * Get the stock transfer this reconciliation belongs to
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Get the branch where reconciliation was performed
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who performed the reconciliation
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get the user who approved the reconciliation
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the reconciliation items
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockReconciliationItem::class);
    }

    /**
     * Get the financial impacts of this reconciliation
     */
    public function financialImpacts()
    {
        return $this->morphMany(StockFinancialImpact::class, 'impactable');
    }

    /**
     * Scope for reconciliations by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending reconciliations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved reconciliations
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Approve reconciliation
     */
    public function approve(User $approver, ?string $notes = null): bool
    {
        $updates = [
            'status' => 'approved',
            'approved_by' => $approver->id,
        ];

        if ($notes) {
            $updates['notes'] = $this->notes ? $this->notes . "\n\nApproval Notes: " . $notes : $notes;
        }

        $result = $this->update($updates);

        if ($result) {
            // Update stock levels based on reconciliation
            $this->updateStockLevels();
            
            // Create financial impact records
            $this->createFinancialImpacts();
        }

        return $result;
    }

    /**
     * Reject reconciliation
     */
    public function reject(User $rejector, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $rejector->id,
            'notes' => $this->notes ? $this->notes . "\n\nRejection Reason: " . $reason : $reason,
        ]);
    }

    /**
     * Calculate total variance value
     */
    public function getTotalVarianceValue(): float
    {
        return $this->items()->sum('financial_impact');
    }

    /**
     * Get items with significant variance (>5%)
     */
    public function getSignificantVarianceItems()
    {
        return $this->items()->where('variance_percentage', '>', 5)->orWhere('variance_percentage', '<', -5);
    }

    /**
     * Check if reconciliation has significant variances
     */
    public function hasSignificantVariances(): bool
    {
        return $this->getSignificantVarianceItems()->count() > 0;
    }

    /**
     * Update stock levels based on reconciliation
     */
    protected function updateStockLevels(): void
    {
        foreach ($this->items as $item) {
            if ($item->variance != 0) {
                // Create stock movement for variance
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'branch_id' => $this->branch_id,
                    'batch_id' => $item->batch_id,
                    'type' => $item->variance > 0 ? 'adjustment' : 'loss',
                    'quantity' => abs($item->variance),
                    'unit_price' => $item->product->purchase_price ?? 0,
                    'notes' => "Stock reconciliation adjustment: {$item->reason}",
                    'user_id' => $this->approved_by,
                    'reconciliation_id' => $this->id,
                ]);

                // Update product branch stock
                $product = $item->product;
                $currentStock = $product->getCurrentStock($this->branch_id);
                $newStock = $currentStock + $item->variance;
                $product->updateBranchStock($this->branch_id, $newStock);
            }
        }
    }

    /**
     * Create financial impact records
     */
    protected function createFinancialImpacts(): void
    {
        foreach ($this->items as $item) {
            if ($item->financial_impact != 0) {
                $impactType = $item->variance < 0 ? 'loss_shortage' : 'gain_excess';
                $impactCategory = $item->variance < 0 ? 'direct_loss' : 'recovery';

                $this->financialImpacts()->create([
                    'branch_id' => $this->branch_id,
                    'impact_type' => $impactType,
                    'amount' => abs($item->financial_impact),
                    'impact_category' => $impactCategory,
                    'description' => "Stock reconciliation variance for {$item->product->name}: {$item->reason}",
                    'impact_date' => $this->reconciliation_date->toDateString(),
                    'is_recoverable' => false,
                ]);
            }
        }
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}