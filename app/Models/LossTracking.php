<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LossTracking extends Model
{
    use HasFactory;

    /**
     * Explicit table name because migration uses singular 'loss_tracking'
     */
    protected $table = 'loss_tracking';

    protected $fillable = [
        'product_id',
        'branch_id',
        'batch_id',
        'loss_type',
        'quantity_lost',
        'financial_loss',
        'reason',
        'user_id',
        'reference_type',
        'reference_id',
        'initial_quantity',
        'final_quantity',
    ];

    protected $casts = [
        'quantity_lost' => 'decimal:2',
        'financial_loss' => 'decimal:2',
        'initial_quantity' => 'decimal:2',
        'final_quantity' => 'decimal:2',
    ];

    /**
     * Get the product for this loss record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this loss record.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the batch for this loss record.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the user who recorded this loss.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get losses by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('loss_type', $type);
    }

    /**
     * Scope to get losses for a specific branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get losses for a specific product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to get losses within a date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the loss type display name.
     */
    public function getLossTypeDisplayName(): string
    {
        return match($this->loss_type) {
            'weight_loss' => 'Weight Loss',
            'water_loss' => 'Water Loss',
            'wastage' => 'Wastage',
            'complimentary' => 'Complimentary/Adjustment',
            default => ucfirst($this->loss_type),
        };
    }

    /**
     * Get the loss percentage based on product stock.
     */
    public function getLossPercentage(): float
    {
        if (!$this->product || !$this->branch) {
            return 0;
        }

        $currentStock = $this->product->getCurrentStock($this->branch);
        $totalStock = $currentStock + $this->quantity_lost;

        if ($totalStock <= 0) {
            return 0;
        }

        return ($this->quantity_lost / $totalStock) * 100;
    }

    /**
     * Check if this is a critical loss.
     */
    public function isCriticalLoss(): bool
    {
        return $this->financial_loss > 1000; // Define your threshold
    }

        /**
     * Get the average loss per unit.
     */
    public function getAverageLossPerUnit(): float
    {
        if ($this->quantity_lost <= 0) {
            return 0;
        }
        
        return $this->financial_loss / $this->quantity_lost;
    }

    /**
     * Check if this is a complimentary loss.
     */
    public function isComplimentaryLoss(): bool
    {
        return $this->loss_type === 'complimentary';
    }

    /**
     * Check if this is a weight loss.
     */
    public function isWeightLoss(): bool
    {
        return $this->loss_type === 'weight_loss';
    }

    /**
     * Check if this is a water loss.
     */
    public function isWaterLoss(): bool
    {
        return $this->loss_type === 'water_loss';
    }

    /**
     * Scope to get losses by reference.
     */
    public function scopeByReference($query, $referenceType, $referenceId)
    {
        return $query->where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId);
    }

}