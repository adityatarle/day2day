<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'to_subbranch_id',
        'status',
        'expected_dispatch_ts',
        'expected_arrival_ts',
        'notes',
        'created_by',
        'approved_by',
        'dispatched_by',
        'delivered_marked_by',
        'received_by',
    ];

    protected $casts = [
        'expected_dispatch_ts' => 'datetime',
        'expected_arrival_ts' => 'datetime',
    ];

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function toSubbranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_subbranch_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(TransferLine::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function discrepancies(): HasMany
    {
        return $this->hasMany(Discrepancy::class);
    }
}

