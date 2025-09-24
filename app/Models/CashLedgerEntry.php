<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'pos_session_id',
        'entry_type',
        'amount',
        'counterparty',
        'reference_number',
        'notes',
        'entry_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'entry_date' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }
}

