<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'terminal_id',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_transactions',
        'total_sales',
        'started_at',
        'ended_at',
        'status',
        'session_notes',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'session_notes' => 'array',
    ];

    /**
     * Get the user who owns this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch where this session is running.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the orders created during this session.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'pos_session_id');
    }

    /**
     * Scope to get only active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get sessions for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Calculate the expected cash based on opening cash and sales.
     */
    public function calculateExpectedCash()
    {
        $cashSales = $this->orders()
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        return $this->opening_cash + $cashSales;
    }

    /**
     * Close the session with final counts.
     */
    public function closeSession($closingCash, $notes = null)
    {
        $this->update([
            'closing_cash' => $closingCash,
            'expected_cash' => $this->calculateExpectedCash(),
            'cash_difference' => $closingCash - $this->calculateExpectedCash(),
            'ended_at' => now(),
            'status' => 'closed',
            'session_notes' => array_merge($this->session_notes ?? [], $notes ?? []),
        ]);
    }
}
