<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashDenominationBreakdown extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'denomination_2000',
        'denomination_500',
        'denomination_200',
        'denomination_100',
        'denomination_50',
        'denomination_20',
        'denomination_10',
        'denomination_5',
        'denomination_2',
        'denomination_1',
        'coins',
        'total_cash',
    ];

    protected $casts = [
        'denomination_2000' => 'decimal:2',
        'denomination_500' => 'decimal:2',
        'denomination_200' => 'decimal:2',
        'denomination_100' => 'decimal:2',
        'denomination_50' => 'decimal:2',
        'denomination_20' => 'decimal:2',
        'denomination_10' => 'decimal:2',
        'denomination_5' => 'decimal:2',
        'denomination_2' => 'decimal:2',
        'denomination_1' => 'decimal:2',
        'coins' => 'decimal:2',
        'total_cash' => 'decimal:2',
    ];

    /**
     * Get the payment that owns this breakdown.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Calculate total cash from denominations.
     */
    public function calculateTotal(): float
    {
        return ($this->denomination_2000 * 2000) +
               ($this->denomination_500 * 500) +
               ($this->denomination_200 * 200) +
               ($this->denomination_100 * 100) +
               ($this->denomination_50 * 50) +
               ($this->denomination_20 * 20) +
               ($this->denomination_10 * 10) +
               ($this->denomination_5 * 5) +
               ($this->denomination_2 * 2) +
               ($this->denomination_1 * 1) +
               $this->coins;
    }

    /**
     * Get denominations as array.
     */
    public function toArray(): array
    {
        return [
            '2000' => $this->denomination_2000,
            '500' => $this->denomination_500,
            '200' => $this->denomination_200,
            '100' => $this->denomination_100,
            '50' => $this->denomination_50,
            '20' => $this->denomination_20,
            '10' => $this->denomination_10,
            '5' => $this->denomination_5,
            '2' => $this->denomination_2,
            '1' => $this->denomination_1,
            'coins' => $this->coins,
            'total' => $this->total_cash,
        ];
    }
}

