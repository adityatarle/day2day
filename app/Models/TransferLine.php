<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'product_id',
        'batch_number',
        'expected_qty',
        'expected_weight_kg',
        'expiry_date',
        'standard_cost',
    ];

    protected $casts = [
        'expected_qty' => 'decimal:2',
        'expected_weight_kg' => 'decimal:2',
        'expiry_date' => 'date',
        'standard_cost' => 'decimal:2',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

