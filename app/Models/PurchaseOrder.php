<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PurchaseOrder Model
 * 
 * Terminology (following Tally conventions):
 * - "Purchase Order" refers to orders SENT FROM main branch TO vendors (outgoing orders)
 * - When status becomes "received", it represents a "Received Order" (materials received FROM vendors)
 * 
 * Status Flow: draft -> sent -> confirmed -> received (Received Order) -> [completed]
 */
class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'vendor_id',
        'branch_id',
        'branch_request_id',
        'user_id',
        'status',
        'receive_status',
        'order_type',
        'delivery_address_type',
        'ship_to_branch_id',
        'delivery_address',
        'is_received_order',
        'payment_terms',
        'subtotal',
        'tax_amount',
        'transport_cost',
        'total_amount',
        'total_ordered_quantity',
        'total_received_quantity',
        'notes',
        'terminology_notes',
        'expected_delivery_date',
        'actual_delivery_date',
        'priority',
        'approved_by',
        'approved_at',
        'fulfilled_by',
        'fulfilled_at',
        'received_by',
        'received_at',
        'cancelled_by',
        'cancelled_at',
        'delivery_notes',
        'delivery_person',
        'delivery_vehicle',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_ordered_quantity' => 'decimal:2',
        'total_received_quantity' => 'decimal:2',
        'is_received_order' => 'boolean',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the vendor for this purchase order.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the branch for this purchase order.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the referenced branch request (self-relation when this PO is created for a branch order).
     */
    public function branchRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'branch_request_id');
    }

    /**
     * Get the branch where goods should be delivered (ship-to), may differ from ordering branch.
     */
    public function shipToBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'ship_to_branch_id');
    }

    /**
     * Get the user who created this purchase order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the purchase order items for this purchase order.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the purchase entries for this purchase order.
     */
    public function purchaseEntries(): HasMany
    {
        return $this->hasMany(PurchaseEntry::class);
    }

    /**
     * Scope to get purchase orders by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get purchase orders for a specific vendor.
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope to get purchase orders for a specific branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if purchase order is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if purchase order is sent.
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if purchase order is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if purchase order materials have been received.
     * Note: This represents the "Received Order" status in Tally terminology.
     */
    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    /**
     * Check if purchase order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if purchase order is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if purchase order is fulfilled.
     */
    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    /**
     * Update purchase order totals.
     */
    public function updateTotals(): void
    {
        $this->subtotal = $this->purchaseOrderItems->sum('total_price');
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->transport_cost;
        $this->save();
    }

    /**
     * Mark purchase order as received (becomes a "Received Order" in Tally terminology).
     * This indicates that the materials ordered from vendor have been received.
     */
    public function markAsReceived(): void
    {
        $this->update([
            'status' => 'received',
            'order_type' => 'received_order',
            'is_received_order' => true,
            'actual_delivery_date' => now(),
            'terminology_notes' => 'Converted from Purchase Order to Received Order - materials received from vendor',
        ]);
    }

    /**
     * Get the total quantity ordered.
     */
    public function getTotalQuantityOrdered(): float
    {
        return $this->purchaseOrderItems->sum('quantity');
    }

    /**
     * Get display name for status using Tally terminology.
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'draft' => 'Draft Purchase Order',
            'sent' => 'Purchase Order Sent',
            'approved' => 'Approved Order',
            'confirmed' => 'Purchase Order Confirmed',
            'fulfilled' => 'Order Fulfilled',
            'received' => 'Received Order (Materials Received)',
            'cancelled' => 'Cancelled Purchase Order',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status badge color class.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'sent' => 'bg-blue-100 text-blue-800',
            'approved' => 'bg-green-100 text-green-800',
            'confirmed' => 'bg-yellow-100 text-yellow-800',
            'fulfilled' => 'bg-purple-100 text-purple-800',
            'received' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check if this is a Purchase Order (outgoing to vendor).
     */
    public function isPurchaseOrder(): bool
    {
        return $this->order_type === 'purchase_order' && !$this->is_received_order;
    }

    /**
     * Check if this is a Received Order (materials received from vendor).
     */
    public function isReceivedOrderType(): bool
    {
        return $this->order_type === 'received_order' || $this->is_received_order;
    }

    /**
     * Get the appropriate terminology display text.
     */
    public function getTerminologyDisplayText(): string
    {
        if ($this->isReceivedOrderType()) {
            return 'Received Order (Materials Received)';
        }
        
        return 'Purchase Order (Outgoing to Vendor)';
    }

    /**
     * Scope to get only Purchase Orders (not yet received).
     */
    public function scopePurchaseOrdersOnly($query)
    {
        return $query->where('order_type', 'purchase_order')
                    ->where('is_received_order', false);
    }

    /**
     * Scope to get only Received Orders.
     */
    public function scopeReceivedOrdersOnly($query)
    {
        return $query->where(function($q) {
            $q->where('order_type', 'received_order')
              ->orWhere('is_received_order', true);
        });
    }

    /**
     * Resolve delivery address string for display/printing.
     */
    public function getResolvedDeliveryAddress(): string
    {
        if ($this->delivery_address_type === 'custom' && filled($this->delivery_address)) {
            return $this->delivery_address;
        }

        if ($this->delivery_address_type === 'branch' && $this->ship_to_branch_id && $this->shipToBranch) {
            $branch = $this->shipToBranch;
            return trim(($branch->name ? ($branch->name . ' - ') : '') . ($branch->address ?? ''));
        }

        // Default: Admin/Main warehouse address from the main branch if available
        $mainBranch = Branch::where('code', 'FDC001')->first();
        if ($mainBranch && $mainBranch->address) {
            return 'Main Warehouse - ' . $mainBranch->address;
        }

        return 'Main Warehouse';
    }

    /**
     * Scope to get purchase orders that are pending to receive (confirmed but not yet received).
     */
    public function scopePendingToReceive($query)
    {
        return $query->where('status', 'confirmed')
                    ->where('receive_status', '!=', 'complete');
    }

    /**
     * Scope to get purchase orders with partial receives.
     */
    public function scopePartiallyReceived($query)
    {
        return $query->where('receive_status', 'partial');
    }

    /**
     * Check if the purchase order is partially received.
     */
    public function isPartiallyReceived(): bool
    {
        return $this->receive_status === 'partial';
    }

    /**
     * Check if the purchase order is completely received.
     */
    public function isCompletelyReceived(): bool
    {
        return $this->receive_status === 'complete';
    }

    /**
     * Calculate and update receive status based on items.
     */
    public function updateReceiveStatus(): void
    {
        $this->load('purchaseOrderItems');
        
        $totalOrdered = $this->purchaseOrderItems->sum('quantity');
        $totalReceived = $this->purchaseOrderItems->sum('received_quantity');
        
        $this->total_ordered_quantity = $totalOrdered;
        $this->total_received_quantity = $totalReceived;
        
        if ($totalReceived == 0) {
            $this->receive_status = 'not_received';
        } elseif ($totalReceived >= $totalOrdered) {
            $this->receive_status = 'complete';
        } else {
            $this->receive_status = 'partial';
        }
        
        $this->save();
    }

    /**
     * Get receive status badge color.
     */
    public function getReceiveStatusBadgeClass(): string
    {
        return match($this->receive_status) {
            'not_received' => 'bg-gray-100 text-gray-800',
            'partial' => 'bg-orange-100 text-orange-800',
            'complete' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get receive status display text.
     */
    public function getReceiveStatusDisplayText(): string
    {
        return match($this->receive_status) {
            'not_received' => 'Not Received',
            'partial' => 'Partially Received',
            'complete' => 'Completely Received',
            default => 'Unknown'
        };
    }

    /**
     * Get total received quantity across all entries.
     */
    public function getTotalReceivedQuantity(): float
    {
        return $this->purchaseEntries->sum('total_received_quantity');
    }

    /**
     * Get total spoiled quantity across all entries.
     */
    public function getTotalSpoiledQuantity(): float
    {
        return $this->purchaseEntries->sum('total_spoiled_quantity');
    }

    /**
     * Get total damaged quantity across all entries.
     */
    public function getTotalDamagedQuantity(): float
    {
        return $this->purchaseEntries->sum('total_damaged_quantity');
    }

    /**
     * Get total usable quantity across all entries.
     */
    public function getTotalUsableQuantity(): float
    {
        return $this->purchaseEntries->sum('total_usable_quantity');
    }

    /**
     * Get remaining quantity to be received.
     */
    public function getRemainingQuantity(): float
    {
        $totalOrdered = $this->purchaseOrderItems->sum('quantity');
        $totalReceived = $this->getTotalReceivedQuantity();
        return max(0, $totalOrdered - $totalReceived);
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentage(): float
    {
        $totalOrdered = $this->purchaseOrderItems->sum('quantity');
        if ($totalOrdered == 0) {
            return 0;
        }
        
        $totalReceived = $this->getTotalReceivedQuantity();
        return ($totalReceived / $totalOrdered) * 100;
    }

    /**
     * Get loss percentage.
     */
    public function getLossPercentage(): float
    {
        $totalReceived = $this->getTotalReceivedQuantity();
        if ($totalReceived == 0) {
            return 0;
        }
        
        $totalLoss = $this->getTotalSpoiledQuantity() + $this->getTotalDamagedQuantity();
        return ($totalLoss / $totalReceived) * 100;
    }

    /**
     * Check if order has any discrepancies.
     */
    public function hasDiscrepancies(): bool
    {
        return $this->getTotalSpoiledQuantity() > 0 || $this->getTotalDamagedQuantity() > 0;
    }

    /**
     * Get detailed item tracking for this order.
     */
    public function getItemTracking(): array
    {
        return $this->purchaseOrderItems->map(function ($item) {
            $totalReceived = $this->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('received_quantity');
            
            $totalSpoiled = $this->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('spoiled_quantity');
            
            $totalDamaged = $this->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('damaged_quantity');
            
            $totalUsable = $this->purchaseEntries
                ->flatMap->purchaseEntryItems
                ->where('product_id', $item->product_id)
                ->sum('usable_quantity');
            
            $remaining = $item->quantity - $totalReceived;
            $completionPercentage = $item->quantity > 0 ? ($totalReceived / $item->quantity) * 100 : 0;
            
            return [
                'item' => $item,
                'expected_quantity' => $item->quantity,
                'received_quantity' => $totalReceived,
                'spoiled_quantity' => $totalSpoiled,
                'damaged_quantity' => $totalDamaged,
                'usable_quantity' => $totalUsable,
                'remaining_quantity' => $remaining,
                'completion_percentage' => $completionPercentage,
                'is_complete' => $remaining <= 0,
                'has_discrepancies' => $totalSpoiled > 0 || $totalDamaged > 0,
            ];
        })->toArray();
    }
}