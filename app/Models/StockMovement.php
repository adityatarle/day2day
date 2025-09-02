<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'batch_id',
        'type',
        'quantity',
        'unit_price',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get the product for this stock movement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this stock movement.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the batch for this stock movement.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the user who recorded this stock movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get stock movements by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get stock movements for a specific branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get stock movements for a specific product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to get stock movements within a date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the total value of this stock movement.
     */
    public function getTotalValue(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Check if this is an incoming stock movement.
     */
    public function isIncoming(): bool
    {
        return in_array($this->type, ['purchase', 'return', 'adjustment']);
    }

    /**
     * Check if this is an outgoing stock movement.
     */
    public function isOutgoing(): bool
    {
        return in_array($this->type, ['sale', 'loss']);
    }

    /**
     * Get the movement type display name.
     */
    public function getTypeDisplayName(): string
    {
        return match($this->type) {
            'purchase' => 'Purchase',
            'sale' => 'Sale',
            'adjustment' => 'Adjustment',
            'loss' => 'Loss',
            'return' => 'Return',
            default => ucfirst($this->type),
        };
    }
}