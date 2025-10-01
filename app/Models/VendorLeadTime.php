<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorLeadTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'product_id',
        'lead_time_days',
        'order_date',
        'delivery_date',
        'purchase_order_id',
        'notes',
    ];

    protected $casts = [
        'lead_time_days' => 'integer',
        'order_date' => 'date',
        'delivery_date' => 'date',
    ];

    /**
     * Get the vendor for this lead time record.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the product for this lead time record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the purchase order for this lead time record.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Calculate average lead time for a vendor (optionally for a specific product).
     */
    public static function getAverageLeadTime(int $vendorId, ?int $productId = null): float
    {
        $query = static::where('vendor_id', $vendorId);
        
        if ($productId) {
            $query->where('product_id', $productId);
        }
        
        // Get last 10 orders for more accurate recent average
        return $query->orderBy('delivery_date', 'desc')
                    ->limit(10)
                    ->avg('lead_time_days') ?? 2; // Default 2 days
    }

    /**
     * Record lead time from a completed purchase order.
     */
    public static function recordFromPurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        if (!$purchaseOrder->actual_delivery_date) {
            return;
        }

        $orderDate = $purchaseOrder->created_at->toDateString();
        $deliveryDate = $purchaseOrder->actual_delivery_date->toDateString();
        $leadTimeDays = $purchaseOrder->created_at->diffInDays($purchaseOrder->actual_delivery_date);

        foreach ($purchaseOrder->purchaseOrderItems as $item) {
            static::create([
                'vendor_id' => $purchaseOrder->vendor_id,
                'product_id' => $item->product_id,
                'lead_time_days' => $leadTimeDays,
                'order_date' => $orderDate,
                'delivery_date' => $deliveryDate,
                'purchase_order_id' => $purchaseOrder->id,
            ]);
        }
    }

    /**
     * Get lead time statistics for a vendor.
     */
    public static function getLeadTimeStats(int $vendorId): array
    {
        $leadTimes = static::where('vendor_id', $vendorId)
                          ->orderBy('delivery_date', 'desc')
                          ->limit(20)
                          ->pluck('lead_time_days');

        if ($leadTimes->isEmpty()) {
            return [
                'average' => 2,
                'minimum' => 2,
                'maximum' => 2,
                'count' => 0,
            ];
        }

        return [
            'average' => round($leadTimes->avg(), 1),
            'minimum' => $leadTimes->min(),
            'maximum' => $leadTimes->max(),
            'count' => $leadTimes->count(),
        ];
    }
}
