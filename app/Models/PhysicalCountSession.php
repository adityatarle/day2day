<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalCountSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_number',
        'branch_id',
        'warehouse_id',
        'cycle_count_schedule_id',
        'count_type',
        'status',
        'scheduled_date',
        'started_at',
        'completed_at',
        'conducted_by',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the branch for this session.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the warehouse for this session.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the cycle count schedule for this session.
     */
    public function cycleCountSchedule(): BelongsTo
    {
        return $this->belongsTo(CycleCountSchedule::class);
    }

    /**
     * Get the user conducting this session.
     */
    public function conductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    /**
     * Get the user verifying this session.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the count items for this session.
     */
    public function countItems(): HasMany
    {
        return $this->hasMany(PhysicalCountItem::class, 'session_id');
    }

    /**
     * Get the mobile scan records for this session.
     */
    public function scanRecords(): HasMany
    {
        return $this->hasMany(MobileScanRecord::class, 'session_id');
    }

    /**
     * Get the reconciliations for this session.
     */
    public function reconciliations(): HasMany
    {
        return $this->hasMany(StockReconciliation::class, 'physical_count_session_id');
    }

    /**
     * Generate unique session number.
     */
    public static function generateSessionNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now())->count() + 1;
        return "CNT-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Start the count session.
     */
    public function start(User $user): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'conducted_by' => $user->id,
        ]);
    }

    /**
     * Complete the count session.
     */
    public function complete(User $verifier): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'verified_by' => $verifier->id,
        ]);

        // Update cycle count schedule if linked
        if ($this->cycleCountSchedule) {
            $this->cycleCountSchedule->markAsCounted();
        }
    }

    /**
     * Get variance summary for this session.
     */
    public function getVarianceSummary(): array
    {
        $items = $this->countItems;

        return [
            'total_items' => $items->count(),
            'items_counted' => $items->whereNotNull('counted_quantity')->count(),
            'items_with_variance' => $items->where('variance_type', '!=', 'match')->count(),
            'total_variance_value' => $items->sum('value_variance'),
            'overages' => $items->where('variance_type', 'overage')->count(),
            'shortages' => $items->where('variance_type', 'shortage')->count(),
            'matches' => $items->where('variance_type', 'match')->count(),
        ];
    }

    /**
     * Get accuracy percentage for this session.
     */
    public function getAccuracyPercentage(): float
    {
        $items = $this->countItems->whereNotNull('counted_quantity');
        
        if ($items->isEmpty()) {
            return 0;
        }

        $matches = $items->where('variance_type', 'match')->count();
        return ($matches / $items->count()) * 100;
    }

    /**
     * Create count items from products.
     */
    public function createCountItems(array $productIds): int
    {
        $count = 0;

        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if (!$product) {
                continue;
            }

            // Get system quantity
            $systemQty = 0;
            if ($this->warehouse_id) {
                $warehouseStock = WarehouseStock::where('warehouse_id', $this->warehouse_id)
                                                ->where('product_id', $productId)
                                                ->first();
                $systemQty = $warehouseStock?->available_quantity ?? 0;
            } elseif ($this->branch_id) {
                $systemQty = $product->getCurrentStock($this->branch_id);
            }

            PhysicalCountItem::create([
                'session_id' => $this->id,
                'product_id' => $productId,
                'system_quantity' => $systemQty,
            ]);

            $count++;
        }

        return $count;
    }
}
