<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GstTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'invoice_number',
        'invoice_date',
        'customer_id',
        'vendor_id',
        'branch_id',
        'taxable_value',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_gst',
        'total_amount',
        'gst_rate',
        'is_reverse_charge',
        'place_of_supply',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'taxable_value' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_gst' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_reverse_charge' => 'boolean',
    ];

    /**
     * Get the customer for this transaction
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vendor for this transaction
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the branch for this transaction
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope for sales transactions
     */
    public function scopeSales($query)
    {
        return $query->where('transaction_type', 'sale');
    }

    /**
     * Scope for purchase transactions
     */
    public function scopePurchases($query)
    {
        return $query->where('transaction_type', 'purchase');
    }

    /**
     * Scope for return transactions
     */
    public function scopeReturns($query)
    {
        return $query->where('transaction_type', 'return');
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    /**
     * Scope for branch
     */
    public function scopeBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for GST rate
     */
    public function scopeGstRate($query, string $rate)
    {
        return $query->where('gst_rate', $rate);
    }

    /**
     * Get GST rate as decimal
     */
    public function getGstRateDecimalAttribute(): float
    {
        return (float) str_replace('%', '', $this->gst_rate);
    }

    /**
     * Check if this is an interstate transaction
     */
    public function isInterstate(): bool
    {
        return $this->igst_amount > 0;
    }

    /**
     * Check if this is an intrastate transaction
     */
    public function isIntrastate(): bool
    {
        return $this->cgst_amount > 0 || $this->sgst_amount > 0;
    }

    /**
     * Get GST type (CGST+SGST or IGST)
     */
    public function getGstTypeAttribute(): string
    {
        return $this->isInterstate() ? 'IGST' : 'CGST+SGST';
    }

    /**
     * Get formatted GST rate
     */
    public function getFormattedGstRateAttribute(): string
    {
        return $this->gst_rate . '%';
    }

    /**
     * Get total taxable value including GST
     */
    public function getTotalTaxableValueAttribute(): float
    {
        return $this->taxable_value + $this->total_gst;
    }

    /**
     * Get input tax credit (for purchases)
     */
    public function getInputTaxCreditAttribute(): float
    {
        return $this->transaction_type === 'purchase' ? $this->total_gst : 0;
    }

    /**
     * Get output tax (for sales)
     */
    public function getOutputTaxAttribute(): float
    {
        return $this->transaction_type === 'sale' ? $this->total_gst : 0;
    }

    /**
     * Create GST transaction from order
     * No GST for sales - always 0%
     */
    public static function createFromOrder(Order $order): self
    {
        $gstRate = '0%'; // No GST for sales
        $taxableValue = $order->subtotal;
        $gstAmount = 0; // No GST
        
        return self::create([
            'transaction_type' => 'sale',
            'invoice_number' => $order->order_number,
            'invoice_date' => $order->created_at->toDateString(),
            'customer_id' => $order->customer_id,
            'branch_id' => $order->branch_id,
            'taxable_value' => $taxableValue,
            'cgst_amount' => 0,
            'sgst_amount' => 0,
            'igst_amount' => 0,
            'total_gst' => 0,
            'total_amount' => $order->total_amount,
            'gst_rate' => $gstRate,
            'place_of_supply' => $order->customer ? $order->customer->state : $order->branch->city->state,
        ]);
    }

    /**
     * Create GST transaction from purchase order
     */
    public static function createFromPurchaseOrder(PurchaseOrder $purchaseOrder): self
    {
        $gstRate = $purchaseOrder->gst_rate ?? '18%';
        $taxableValue = $purchaseOrder->subtotal;
        $gstAmount = $purchaseOrder->tax_amount;
        
        // Determine if interstate or intrastate
        $isInterstate = $purchaseOrder->vendor && 
                       $purchaseOrder->vendor->state !== $purchaseOrder->branch->city->state;
        
        $cgstAmount = $isInterstate ? 0 : $gstAmount / 2;
        $sgstAmount = $isInterstate ? 0 : $gstAmount / 2;
        $igstAmount = $isInterstate ? $gstAmount : 0;
        
        return self::create([
            'transaction_type' => 'purchase',
            'invoice_number' => $purchaseOrder->po_number,
            'invoice_date' => $purchaseOrder->created_at->toDateString(),
            'vendor_id' => $purchaseOrder->vendor_id,
            'branch_id' => $purchaseOrder->branch_id,
            'taxable_value' => $taxableValue,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'igst_amount' => $igstAmount,
            'total_gst' => $gstAmount,
            'total_amount' => $purchaseOrder->total_amount,
            'gst_rate' => $gstRate,
            'place_of_supply' => $purchaseOrder->vendor ? $purchaseOrder->vendor->state : $purchaseOrder->branch->city->state,
        ]);
    }

    /**
     * Get monthly GST summary for GSTR-1
     */
    public static function getMonthlyGstSummary(int $year, int $month): array
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $transactions = self::sales()
            ->dateRange($startDate, $endDate)
            ->get();
        
        $summary = [
            'total_taxable_value' => 0,
            'total_cgst' => 0,
            'total_sgst' => 0,
            'total_igst' => 0,
            'total_gst' => 0,
            'rate_wise_summary' => [],
        ];
        
        foreach ($transactions as $transaction) {
            $summary['total_taxable_value'] += $transaction->taxable_value;
            $summary['total_cgst'] += $transaction->cgst_amount;
            $summary['total_sgst'] += $transaction->sgst_amount;
            $summary['total_igst'] += $transaction->igst_amount;
            $summary['total_gst'] += $transaction->total_gst;
            
            $rate = $transaction->gst_rate;
            if (!isset($summary['rate_wise_summary'][$rate])) {
                $summary['rate_wise_summary'][$rate] = [
                    'taxable_value' => 0,
                    'cgst' => 0,
                    'sgst' => 0,
                    'igst' => 0,
                    'total_gst' => 0,
                ];
            }
            
            $summary['rate_wise_summary'][$rate]['taxable_value'] += $transaction->taxable_value;
            $summary['rate_wise_summary'][$rate]['cgst'] += $transaction->cgst_amount;
            $summary['rate_wise_summary'][$rate]['sgst'] += $transaction->sgst_amount;
            $summary['rate_wise_summary'][$rate]['igst'] += $transaction->igst_amount;
            $summary['rate_wise_summary'][$rate]['total_gst'] += $transaction->total_gst;
        }
        
        return $summary;
    }
}
