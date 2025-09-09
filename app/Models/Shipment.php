<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'transporter_name',
        'vehicle_no',
        'lr_no',
        'seal_no',
        'gross_weight_kg',
        'tare_weight_kg',
        'net_weight_kg',
        'dispatch_ts',
        'documents',
    ];

    protected $casts = [
        'gross_weight_kg' => 'decimal:2',
        'tare_weight_kg' => 'decimal:2',
        'net_weight_kg' => 'decimal:2',
        'dispatch_ts' => 'datetime',
        'documents' => 'array',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}

