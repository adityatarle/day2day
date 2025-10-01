<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReorderPointConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'average_daily_sales',
        'lead_time_days',
        'safety_stock_days',
        'reorder_point',
        'seasonal_factor',
        'calculation_period_days',
        'last_calculated_at',
        'auto_reorder_enabled',
    ];

    protected $casts = [
        'average_daily_sales' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'seasonal_factor' => 'decimal:2',
        'lead_time_days' => 'integer',
        'safety_stock_days' => 'integer',
        'calculation_period_days' => 'integer',
        'last_calculated_at' => 'datetime',
        'auto_reorder_enabled' => 'boolean',
    ];

    /**
     * Get the product for this reorder config.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this reorder config.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Calculate reorder point using the formula:
     * Reorder Point = (Average Daily Sales × Lead Time) + Safety Stock
     */
    public function calculateReorderPoint(): float
    {
        $safetyStock = $this->average_daily_sales * $this->safety_stock_days;
        $reorderPoint = ($this->average_daily_sales * $this->lead_time_days) + $safetyStock;
        
        // Apply seasonal adjustment
        return $reorderPoint * $this->seasonal_factor;
    }

    /**
     * Update average daily sales based on recent sales data.
     */
    public function updateAverageDailySales(): void
    {
        $startDate = now()->subDays($this->calculation_period_days);
        
        // Get total sales quantity for the product at this branch
        $totalSales = OrderItem::whereHas('order', function ($query) use ($startDate) {
            $query->where('branch_id', $this->branch_id)
                  ->where('status', 'completed')
                  ->where('created_at', '>=', $startDate);
        })
        ->where('product_id', $this->product_id)
        ->sum('quantity');

        $this->average_daily_sales = $totalSales / $this->calculation_period_days;
        $this->last_calculated_at = now();
        $this->save();
    }

    /**
     * Recalculate and update reorder point.
     */
    public function recalculate(): void
    {
        $this->updateAverageDailySales();
        $this->reorder_point = $this->calculateReorderPoint();
        $this->save();
    }

    /**
     * Check if current stock is below reorder point.
     */
    public function shouldReorder(): bool
    {
        $currentStock = $this->product->getCurrentStock($this->branch_id);
        return $currentStock <= $this->reorder_point;
    }

    /**
     * Get recommended order quantity.
     */
    public function getRecommendedOrderQuantity(): float
    {
        $currentStock = $this->product->getCurrentStock($this->branch_id);
        $optimalStock = $this->product->optimal_stock_level ?? ($this->average_daily_sales * 7); // 7 days worth
        
        $recommendedQty = $optimalStock - $currentStock;
        
        // Apply min/max constraints
        if ($this->product->min_order_quantity) {
            $recommendedQty = max($recommendedQty, $this->product->min_order_quantity);
        }
        
        if ($this->product->max_order_quantity) {
            $recommendedQty = min($recommendedQty, $this->product->max_order_quantity);
        }
        
        return max(0, $recommendedQty);
    }

    /**
     * Scope to get configs that need reordering.
     */
    public function scopeNeedsReorder($query)
    {
        return $query->where('auto_reorder_enabled', true)
                     ->whereRaw('(SELECT SUM(current_stock) FROM product_branches WHERE product_id = reorder_point_configs.product_id AND branch_id = reorder_point_configs.branch_id) <= reorder_point');
    }
}
