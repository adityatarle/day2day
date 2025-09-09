<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'received_branch_id',
        'received_subbranch_id',
        'arrival_ts',
        'reweigh_gross_kg',
        'reweigh_tare_kg',
        'reweigh_net_kg',
        'within_tolerance',
        'tolerance_percent',
        'accepted_by',
    ];

    protected $casts = [
        'arrival_ts' => 'datetime',
        'reweigh_gross_kg' => 'decimal:2',
        'reweigh_tare_kg' => 'decimal:2',
        'reweigh_net_kg' => 'decimal:2',
        'within_tolerance' => 'boolean',
        'tolerance_percent' => 'decimal:2',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'received_branch_id');
    }

    public function subbranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'received_subbranch_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}

