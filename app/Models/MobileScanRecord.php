<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileScanRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'barcode',
        'scanned_quantity',
        'storage_location',
        'gps_coordinates',
        'device_id',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_quantity' => 'decimal:2',
        'gps_coordinates' => 'array',
        'scanned_at' => 'datetime',
    ];

    /**
     * Get the session for this scan record.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PhysicalCountSession::class, 'session_id');
    }

    /**
     * Get the user who scanned this item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product for this scan record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Update or create count item from scan.
     */
    public function updateCountItem(): void
    {
        $countItem = PhysicalCountItem::where('session_id', $this->session_id)
                                      ->where('product_id', $this->product_id)
                                      ->first();

        if (!$countItem) {
            return;
        }

        // Add scanned quantity to existing count
        $newCount = ($countItem->counted_quantity ?? 0) + $this->scanned_quantity;
        $countItem->recordCount($newCount, $this->storage_location);
        $countItem->update(['barcode_scanned' => $this->barcode]);
    }
}
