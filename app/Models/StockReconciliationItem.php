<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReconciliationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_reconciliation_id',
        'product_id',
        'batch_id',
        'system_quantity',
        'physical_quantity',
        'variance',
        'variance_percentage',
        'variance_type',
        'reason',
        'financial_impact',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:3',
        'physical_quantity' => 'decimal:3',
        'variance' => 'decimal:3',
        'variance_percentage' => 'decimal:2',
        'financial_impact' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Auto-calculate variance and percentage
            $model->calculateVariance();
        });

        static::updating(function ($model) {
            if ($model->isDirty(['system_quantity', 'physical_quantity'])) {
                $model->calculateVariance();
            }
        });
    }

    /**
     * Get the stock reconciliation this item belongs to
     */
    public function stockReconciliation(): BelongsTo
    {
        return $this->belongsTo(StockReconciliation::class);
    }

    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this item
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Calculate variance and related fields
     */
    public function calculateVariance(): void
    {
        $this->variance = $this->physical_quantity - $this->system_quantity;
        
        if ($this->system_quantity > 0) {
            $this->variance_percentage = ($this->variance / $this->system_quantity) * 100;
        } else {
            $this->variance_percentage = 0;
        }

        $this->variance_type = $this->getVarianceType();
        $this->financial_impact = $this->calculateFinancialImpact();
    }

    /**
     * Get variance type based on variance value
     */
    protected function getVarianceType(): string
    {
        if (abs($this->variance) < 0.001) {
            return 'none';
        }
        
        return $this->variance < 0 ? 'shortage' : 'excess';
    }

    /**
     * Calculate financial impact of variance
     */
    protected function calculateFinancialImpact(): float
    {
        if (!$this->product) {
            return 0;
        }

        return abs($this->variance) * ($this->product->purchase_price ?? 0);
    }

    /**
     * Check if variance is significant (>5%)
     */
    public function isSignificantVariance(): bool
    {
        return abs($this->variance_percentage) > 5;
    }

    /**
     * Check if variance is critical (>15%)
     */
    public function isCriticalVariance(): bool
    {
        return abs($this->variance_percentage) > 15;
    }

    /**
     * Get variance display color
     */
    public function getVarianceColor(): string
    {
        return match($this->variance_type) {
            'none' => 'green',
            'shortage' => 'red',
            'excess' => 'orange',
            default => 'gray',
        };
    }

    /**
     * Get variance type display name
     */
    public function getVarianceTypeDisplayName(): string
    {
        return match($this->variance_type) {
            'none' => 'No Variance',
            'shortage' => 'Shortage',
            'excess' => 'Excess',
            default => ucfirst($this->variance_type),
        };
    }

    /**
     * Get severity level based on variance percentage
     */
    public function getSeverityLevel(): string
    {
        $absPercentage = abs($this->variance_percentage);
        
        if ($absPercentage < 1) {
            return 'minimal';
        } elseif ($absPercentage < 5) {
            return 'low';
        } elseif ($absPercentage < 15) {
            return 'medium';
        } else {
            return 'high';
        }
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColor(): string
    {
        return match($this->getSeverityLevel()) {
            'minimal' => 'green',
            'low' => 'yellow',
            'medium' => 'orange',
            'high' => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope for items with shortage
     */
    public function scopeWithShortage($query)
    {
        return $query->where('variance_type', 'shortage');
    }

    /**
     * Scope for items with excess
     */
    public function scopeWithExcess($query)
    {
        return $query->where('variance_type', 'excess');
    }

    /**
     * Scope for items with significant variance
     */
    public function scopeSignificantVariance($query)
    {
        return $query->where('variance_percentage', '>', 5)
                    ->orWhere('variance_percentage', '<', -5);
    }

    /**
     * Scope for items with critical variance
     */
    public function scopeCriticalVariance($query)
    {
        return $query->where('variance_percentage', '>', 15)
                    ->orWhere('variance_percentage', '<', -15);
    }
}