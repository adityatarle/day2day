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
            // Use raw SQL with FOR UPDATE to ensure atomic increment
            $sequence = DB::table('po_number_sequences')
                ->where('prefix', $prefix)
                ->where('order_type', $orderType)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                // Create new sequence record if it doesn't exist
                DB::table('po_number_sequences')->insert([
                    'prefix' => $prefix,
                    'order_type' => $orderType,
                    'year' => $year,
                    'current_sequence' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $nextSequence = 1;
            } else {
                // Increment the sequence atomically
                $nextSequence = $sequence->current_sequence + 1;
                DB::table('po_number_sequences')
                    ->where('id', $sequence->id)
                    ->update([
                        'current_sequence' => $nextSequence,
                        'updated_at' => now(),
                    ]);
            }

            return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        });
    }
}