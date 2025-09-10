<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_branch_id',
        'to_branch_id',
        'initiated_by',
        'status',
        'total_value',
        'transport_cost',
        'transport_vendor',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'dispatch_date',
        'expected_delivery',
        'delivered_date',
        'confirmed_date',
        'dispatch_notes',
        'delivery_notes',
        'documents',
    ];

    protected $casts = [
        'total_value' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'dispatch_date' => 'datetime',
        'expected_delivery' => 'datetime',
        'delivered_date' => 'datetime',
        'confirmed_date' => 'datetime',
        'documents' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->transfer_number)) {
                $model->transfer_number = static::generateTransferNumber();
            }
        });
    }

    /**
     * Generate unique transfer number
     */
    public static function generateTransferNumber(): string
    {
        do {
            $number = 'ST' . date('Ym') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('transfer_number', $number)->exists());

        return $number;
    }

    /**
     * Get the branch this transfer is from (null for main warehouse)
     */
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * Get the branch this transfer is to
     */
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Get the user who initiated this transfer
     */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the items in this transfer
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * Get the queries/issues for this transfer
     */
    public function queries(): HasMany
    {
        return $this->hasMany(StockTransferQuery::class);
    }

    /**
     * Get the transport expenses for this transfer
     */
    public function transportExpenses(): HasMany
    {
        return $this->hasMany(TransportExpense::class);
    }

    /**
     * Get the stock movements related to this transfer
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the reconciliations for this transfer
     */
    public function reconciliations(): HasMany
    {
        return $this->hasMany(StockReconciliation::class);
    }

    /**
     * Get the financial impacts of this transfer
     */
    public function financialImpacts()
    {
        return $this->morphMany(StockFinancialImpact::class, 'impactable');
    }

    /**
     * Scope for transfers by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for transfers to a specific branch
     */
    public function scopeToBranch($query, $branchId)
    {
        return $query->where('to_branch_id', $branchId);
    }

    /**
     * Scope for transfers from a specific branch
     */
    public function scopeFromBranch($query, $branchId)
    {
        return $query->where('from_branch_id', $branchId);
    }

    /**
     * Scope for pending transfers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in-transit transfers
     */
    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    /**
     * Scope for delivered but not confirmed transfers
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Check if transfer is overdue
     */
    public function isOverdue(): bool
    {
        return $this->expected_delivery && 
               $this->expected_delivery->isPast() && 
               !in_array($this->status, ['confirmed', 'cancelled']);
    }

    /**
     * Get days until expected delivery
     */
    public function getDaysUntilDelivery(): ?int
    {
        if (!$this->expected_delivery) {
            return null;
        }

        return now()->diffInDays($this->expected_delivery, false);
    }

    /**
     * Calculate total transport expenses
     */
    public function getTotalTransportExpenses(): float
    {
        return $this->transportExpenses()->sum('amount');
    }

    /**
     * Update status with timestamp
     */
    public function updateStatus(string $status, array $additionalData = []): bool
    {
        $updates = array_merge(['status' => $status], $additionalData);

        switch ($status) {
            case 'in_transit':
                if (!$this->dispatch_date) {
                    $updates['dispatch_date'] = now();
                }
                break;
            case 'delivered':
                if (!$this->delivered_date) {
                    $updates['delivered_date'] = now();
                }
                break;
            case 'confirmed':
                if (!$this->confirmed_date) {
                    $updates['confirmed_date'] = now();
                }
                break;
        }

        return $this->update($updates);
    }

    /**
     * Calculate total items count
     */
    public function getTotalItemsCount(): int
    {
        return $this->items()->count();
    }

    /**
     * Calculate total quantity sent
     */
    public function getTotalQuantitySent(): float
    {
        return $this->items()->sum('quantity_sent');
    }

    /**
     * Calculate total quantity received
     */
    public function getTotalQuantityReceived(): float
    {
        return $this->items()->sum('quantity_received');
    }

    /**
     * Check if all items are received
     */
    public function isFullyReceived(): bool
    {
        return $this->items()->whereNull('quantity_received')->count() === 0;
    }

    /**
     * Get pending queries count
     */
    public function getPendingQueriesCount(): int
    {
        return $this->queries()->whereIn('status', ['open', 'in_progress'])->count();
    }

    /**
     * Check if transfer has any critical queries
     */
    public function hasCriticalQueries(): bool
    {
        return $this->queries()
                   ->where('priority', 'critical')
                   ->whereIn('status', ['open', 'in_progress'])
                   ->exists();
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'pending' => 'Pending Dispatch',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered (Awaiting Confirmation)',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'in_transit' => 'blue',
            'delivered' => 'orange',
            'confirmed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}