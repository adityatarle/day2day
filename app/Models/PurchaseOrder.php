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
            'confirmed' => 'Purchase Order Confirmed',
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
            'confirmed' => 'bg-yellow-100 text-yellow-800',
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
}