<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PoNumberSequence extends Model
{
    protected $fillable = [
        'prefix',
        'order_type', 
        'year',
        'current_sequence'
    ];

    /**
     * Generate the next PO number atomically for the given order type and year.
     * 
     * @param string $orderType
     * @param int $year
     * @return string
     */
    public static function getNextPoNumber(string $orderType, int $year): string
    {
        $prefix = match($orderType) {
            'branch_request' => "BR-{$year}-",
            'purchase_order' => "PO-{$year}-",
            default => "PO-{$year}-"
        };

        return DB::transaction(function () use ($prefix, $orderType, $year) {
            // 1) Ensure a sequence row exists and is initialized to the current max from purchase_orders
            //    This handles environments where purchase_orders already contains numbers before the
            //    sequence table was introduced.
            $prefixLength = strlen($prefix);

            // Seed or bump the baseline using an upsert that takes the greatest value
            $existingMax = DB::table('purchase_orders')
                ->where('po_number', 'like', $prefix . '%')
                ->selectRaw('MAX(CAST(SUBSTRING(po_number, ' . ($prefixLength + 1) . ') AS UNSIGNED)) as max_seq')
                ->value('max_seq') ?? 0;

            // Create the sequence row if missing, or bump it up if it lags behind existing data
            DB::statement(
                'INSERT INTO po_number_sequences (prefix, order_type, year, current_sequence, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE current_sequence = GREATEST(current_sequence, VALUES(current_sequence)), updated_at = NOW()',
                [$prefix, $orderType, $year, (int) $existingMax]
            );

            // 2) Lock the sequence row and increment atomically
            $sequenceRow = DB::table('po_number_sequences')
                ->where('prefix', $prefix)
                ->where('order_type', $orderType)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $nextSequence = ((int) ($sequenceRow->current_sequence ?? 0)) + 1;

            DB::table('po_number_sequences')
                ->where('id', $sequenceRow->id)
                ->update([
                    'current_sequence' => $nextSequence,
                    'updated_at' => now(),
                ]);

            return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        });
    }
}