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
        // Add 'local_purchase' to the stock_movements type enum
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
        // Remove 'local_purchase' from the enum
        DB::statement("ALTER TABLE `stock_movements` MODIFY COLUMN `type` ENUM(
            'purchase', 'sale', 'adjustment', 'adjustment_positive', 'adjustment_negative',
            'loss', 'return', 'transfer_in', 'transfer_out', 'wastage', 'complimentary'
        ) NOT NULL");
    }
};