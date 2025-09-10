<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'expense_type',
        'description',
        'amount',
        'vendor_name',
        'receipt_number',
        'expense_date',
        'payment_method',
        'receipts',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'receipts' => 'array',
    ];

    /**
     * Get the stock transfer this expense belongs to
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Scope for expenses by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('expense_type', $type);
    }

    /**
     * Scope for expenses by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Scope for expenses by vendor
     */
    public function scopeByVendor($query, $vendor)
    {
        return $query->where('vendor_name', 'like', "%{$vendor}%");
    }

    /**
     * Get expense type display name
     */
    public function getExpenseTypeDisplayName(): string
    {
        return match($this->expense_type) {
            'vehicle_rent' => 'Vehicle Rent',
            'fuel' => 'Fuel',
            'driver_payment' => 'Driver Payment',
            'toll_charges' => 'Toll Charges',
            'loading_charges' => 'Loading Charges',
            'unloading_charges' => 'Unloading Charges',
            'insurance' => 'Insurance',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->expense_type)),
        };
    }

    /**
     * Check if expense has receipts
     */
    public function hasReceipts(): bool
    {
        return !empty($this->receipts);
    }

    /**
     * Get receipt count
     */
    public function getReceiptCount(): int
    {
        return count($this->receipts ?? []);
    }

    /**
     * Get expense category for reporting
     */
    public function getExpenseCategory(): string
    {
        return match($this->expense_type) {
            'vehicle_rent', 'fuel' => 'Transport',
            'driver_payment' => 'Labor',
            'toll_charges' => 'Charges',
            'loading_charges', 'unloading_charges' => 'Handling',
            'insurance' => 'Insurance',
            default => 'Miscellaneous',
        };
    }
}