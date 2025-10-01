<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CycleCountSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'branch_id',
        'warehouse_id',
        'product_category',
        'product_id',
        'frequency',
        'schedule_days',
        'next_count_date',
        'last_count_date',
        'is_active',
        'assigned_to',
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'next_count_date' => 'date',
        'last_count_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the branch for this schedule.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the warehouse for this schedule.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product for this schedule.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user assigned to this schedule.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the physical count sessions for this schedule.
     */
    public function countSessions(): HasMany
    {
        return $this->hasMany(PhysicalCountSession::class, 'cycle_count_schedule_id');
    }

    /**
     * Scope to get active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get schedules due for counting.
     */
    public function scopeDueForCount($query)
    {
        return $query->where('is_active', true)
                     ->where('next_count_date', '<=', now()->toDateString());
    }

    /**
     * Calculate next count date based on frequency.
     */
    public function calculateNextCountDate(): void
    {
        $currentDate = $this->next_count_date ?? now();

        $nextDate = match($this->frequency) {
            'daily' => $currentDate->addDay(),
            'weekly' => $currentDate->addWeek(),
            'biweekly' => $currentDate->addWeeks(2),
            'monthly' => $currentDate->addMonth(),
            'quarterly' => $currentDate->addMonths(3),
            default => $currentDate->addMonth(),
        };

        $this->update(['next_count_date' => $nextDate]);
    }

    /**
     * Mark as counted and update dates.
     */
    public function markAsCounted(): void
    {
        $this->update(['last_count_date' => now()->toDateString()]);
        $this->calculateNextCountDate();
    }

    /**
     * Get products to count for this schedule.
     */
    public function getProductsToCount()
    {
        if ($this->product_id) {
            return Product::where('id', $this->product_id)->get();
        }

        if ($this->product_category) {
            return Product::where('category', $this->product_category)
                         ->active()
                         ->get();
        }

        // Get all products in branch/warehouse
        if ($this->warehouse_id) {
            return Product::whereHas('warehouseStock', function ($query) {
                $query->where('warehouse_id', $this->warehouse_id)
                     ->where('available_quantity', '>', 0);
            })->get();
        }

        if ($this->branch_id) {
            return Product::whereHas('branches', function ($query) {
                $query->where('branch_id', $this->branch_id)
                     ->where('current_stock', '>', 0);
            })->get();
        }

        return collect();
    }
}
