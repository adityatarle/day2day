<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscrepancyLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'discrepancy_id',
        'product_id',
        'qty_delta',
        'weight_delta_kg',
        'disposition',
        'notes',
    ];

    protected $casts = [
        'qty_delta' => 'decimal:2',
        'weight_delta_kg' => 'decimal:2',
    ];

    public function discrepancy(): BelongsTo
    {
        return $this->belongsTo(Discrepancy::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

