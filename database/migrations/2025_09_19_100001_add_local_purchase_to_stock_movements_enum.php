<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we skip this migration since it doesn't support MODIFY COLUMN
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // For MySQL, add 'local_purchase' to the stock_movements type enum
        DB::statement("ALTER TABLE `stock_movements` MODIFY COLUMN `type` ENUM(
            'purchase', 'sale', 'adjustment', 'adjustment_positive', 'adjustment_negative',
            'loss', 'return', 'transfer_in', 'transfer_out', 'wastage', 'complimentary',
            'local_purchase'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For SQLite, we skip this migration
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // For MySQL, remove 'local_purchase' from the enum
        DB::statement("ALTER TABLE `stock_movements` MODIFY COLUMN `type` ENUM(
            'purchase', 'sale', 'adjustment', 'adjustment_positive', 'adjustment_negative',
            'loss', 'return', 'transfer_in', 'transfer_out', 'wastage', 'complimentary'
        ) NOT NULL");
    }
};