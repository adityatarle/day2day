<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoPurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'vendor_id',
        'current_stock',
        'reorder_point',
        'recommended_quantity',
        'status',
        'purchase_order_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'calculation_details',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'recommended_quantity' => 'decimal:2',
        'approved_at' => 'datetime',
        'calculation_details' => 'array',
    ];

    /**
     * Get the product for this auto purchase order.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this auto purchase order.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the vendor for this auto purchase order.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the generated purchase order.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the user who approved this auto purchase order.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to get pending auto purchase orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved auto purchase orders.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Approve and generate purchase order.
     */
    public function approve(User $user): ?PurchaseOrder
    {
        if ($this->status !== 'pending') {
            return null;
        }

        // Get primary vendor for this product or the assigned vendor
        $vendor = $this->vendor;
        if (!$vendor) {
            $vendor = $this->product->vendors()
                         ->wherePivot('is_primary_supplier', true)
                         ->first();
        }

        if (!$vendor) {
            // Get any active vendor
            $vendor = Vendor::active()->first();
        }

        if (!$vendor) {
            return null;
        }

        // Create purchase order
        $purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-AUTO-' . now()->format('YmdHis') . '-' . $this->id,
            'vendor_id' => $vendor->id,
            'branch_id' => $this->branch_id,
            'user_id' => $user->id,
            'status' => 'draft',
            'order_type' => 'purchase_order',
            'expected_delivery_date' => now()->addDays(2),
            'notes' => 'Auto-generated based on reorder point',
        ]);

        // Add item to purchase order
        $unitPrice = $this->product->getBestVendorPrice($vendor->id);
        $purchaseOrder->purchaseOrderItems()->create([
            'product_id' => $this->product_id,
            'quantity' => $this->recommended_quantity,
            'unit_price' => $unitPrice,
            'total_price' => $this->recommended_quantity * $unitPrice,
        ]);

        // Update totals
        $purchaseOrder->updateTotals();

        // Update auto purchase order
        $this->update([
            'status' => 'generated',
            'purchase_order_id' => $purchaseOrder->id,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return $purchaseOrder;
    }

    /**
     * Reject the auto purchase order.
     */
    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Create auto purchase orders for products below reorder point.
     */
    public static function generateAutoPurchaseOrders(): int
    {
        $count = 0;
        
        $configs = ReorderPointConfig::where('auto_reorder_enabled', true)->get();

        foreach ($configs as $config) {
            if (!$config->shouldReorder()) {
                continue;
            }

            // Check if there's already a pending auto PO
            $existingPending = static::where('product_id', $config->product_id)
                                   ->where('branch_id', $config->branch_id)
                                   ->where('status', 'pending')
                                   ->exists();

            if ($existingPending) {
                continue;
            }

            // Get primary vendor
            $vendor = $config->product->vendors()
                           ->wherePivot('is_primary_supplier', true)
                           ->first();

            $currentStock = $config->product->getCurrentStock($config->branch_id);
            $recommendedQty = $config->getRecommendedOrderQuantity();

            static::create([
                'product_id' => $config->product_id,
                'branch_id' => $config->branch_id,
                'vendor_id' => $vendor?->id,
                'current_stock' => $currentStock,
                'reorder_point' => $config->reorder_point,
                'recommended_quantity' => $recommendedQty,
                'status' => 'pending',
                'calculation_details' => [
                    'average_daily_sales' => $config->average_daily_sales,
                    'lead_time_days' => $config->lead_time_days,
                    'safety_stock_days' => $config->safety_stock_days,
                    'seasonal_factor' => $config->seasonal_factor,
                    'calculated_at' => now()->toDateTimeString(),
                ],
            ]);

            $count++;
        }

        return $count;
    }
}
