<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'batch_id',
        'quantity_sent',
        'quantity_received',
        'unit_price',
        'total_value',
        'unit_of_measurement',
        'expiry_date',
        'item_notes',
    ];

    protected $casts = [
        'quantity_sent' => 'decimal:3',
        'quantity_received' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_value' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->total_value)) {
                $model->total_value = $model->quantity_sent * $model->unit_price;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(['quantity_sent', 'unit_price'])) {
                $model->total_value = $model->quantity_sent * $model->unit_price;
            }
        });
    }

    /**
     * Get the stock transfer this item belongs to
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
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
     * Get the queries specific to this item
     */
    public function queries(): HasMany
    {
        return $this->hasMany(StockTransferQuery::class);
    }

    /**
     * Calculate quantity difference (received - sent)
     */
    public function getQuantityDifference(): ?float
    {
        if ($this->quantity_received === null) {
            return null;
        }

        return $this->quantity_received - $this->quantity_sent;
    }

    /**
     * Calculate quantity difference percentage
     */
    public function getQuantityDifferencePercentage(): ?float
    {
        $difference = $this->getQuantityDifference();
        
        if ($difference === null || $this->quantity_sent == 0) {
            return null;
        }

        return ($difference / $this->quantity_sent) * 100;
    }

    /**
     * Check if there's a shortage
     */
    public function hasShortage(): bool
    {
        $difference = $this->getQuantityDifference();
        return $difference !== null && $difference < 0;
    }

    /**
     * Check if there's excess
     */
    public function hasExcess(): bool
    {
        $difference = $this->getQuantityDifference();
        return $difference !== null && $difference > 0;
    }

    /**
     * Check if quantities match exactly
     */
    public function isQuantityMatched(): bool
    {
        $difference = $this->getQuantityDifference();
        return $difference !== null && abs($difference) < 0.001; // Allow for small rounding differences
    }

    /**
     * Check if item is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if item is expiring soon (within 7 days)
     */
    public function isExpiringSoon(): bool
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= 7;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Calculate financial impact of quantity difference
     */
    public function getFinancialImpact(): float
    {
        $difference = $this->getQuantityDifference();
        
        if ($difference === null) {
            return 0;
        }

        return abs($difference) * $this->unit_price;
    }

    /**
     * Check if item needs quality inspection
     */
    public function needsQualityInspection(): bool
    {
        return $this->product->is_perishable || 
               $this->isExpiringSoon() || 
               $this->hasShortage();
    }

    /**
     * Get variance type
     */
    public function getVarianceType(): string
    {
        $difference = $this->getQuantityDifference();
        
        if ($difference === null) {
            return 'pending';
        }
        
        if (abs($difference) < 0.001) {
            return 'matched';
        }
        
        return $difference < 0 ? 'shortage' : 'excess';
    }

    /**
     * Get variance display color
     */
    public function getVarianceColor(): string
    {
        return match($this->getVarianceType()) {
            'matched' => 'green',
            'shortage' => 'red',
            'excess' => 'orange',
            'pending' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Scope for items with pending receipt
     */
    public function scopePendingReceipt($query)
    {
        return $query->whereNull('quantity_received');
    }

    /**
     * Scope for items with shortage
     */
    public function scopeWithShortage($query)
    {
        return $query->whereNotNull('quantity_received')
                    ->whereRaw('quantity_received < quantity_sent');
    }

    /**
     * Scope for items with excess
     */
    public function scopeWithExcess($query)
    {
        return $query->whereNotNull('quantity_received')
                    ->whereRaw('quantity_received > quantity_sent');
    }

    /**
     * Scope for expired items
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
    }

    /**
     * Scope for items expiring soon
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '>', now())
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Update received quantity and create stock movement
     */
    public function updateReceivedQuantity(float $quantity, ?string $notes = null): bool
    {
        $this->update([
            'quantity_received' => $quantity,
            'item_notes' => $notes ? ($this->item_notes ? $this->item_notes . "\n" . $notes : $notes) : $this->item_notes,
        ]);

        // Create stock movement for received quantity
        $this->createStockMovement($quantity, 'transfer_in');

        return true;
    }

    /**
     * Create stock movement for this item
     */
    protected function createStockMovement(float $quantity, string $type): void
    {
        StockMovement::create([
            'product_id' => $this->product_id,
            'branch_id' => $this->stockTransfer->to_branch_id,
            'batch_id' => $this->batch_id,
            'type' => $type,
            'quantity' => $quantity,
            'unit_price' => $this->unit_price,
            'notes' => "Stock transfer: {$this->stockTransfer->transfer_number}",
            'user_id' => auth()->id(),
            'stock_transfer_id' => $this->stock_transfer_id,
        ]);
    }
}