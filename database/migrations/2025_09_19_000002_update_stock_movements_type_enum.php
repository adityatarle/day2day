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
        // Safely modify the enum to include all movement types used by the app
        DB::statement("ALTER TABLE `stock_movements` MODIFY COLUMN `type` ENUM(
            'purchase', 'sale', 'adjustment', 'adjustment_positive', 'adjustment_negative',
            'loss', 'return', 'transfer_in', 'transfer_out', 'wastage', 'complimentary'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original basic enum (may fail if data exists outside this set)
        DB::statement("ALTER TABLE `stock_movements` MODIFY COLUMN `type` ENUM(
            'purchase', 'sale', 'adjustment', 'loss', 'return'
        ) NOT NULL");
    }
};

