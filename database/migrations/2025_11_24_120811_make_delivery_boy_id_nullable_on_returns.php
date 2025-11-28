<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            // Make delivery_boy_id nullable to support in-store returns
            if (Schema::hasColumn('returns', 'delivery_boy_id')) {
                // Use raw SQL to avoid requiring doctrine/dbal
                \DB::statement('ALTER TABLE `returns` MODIFY `delivery_boy_id` BIGINT UNSIGNED NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            // Optionally revert to NOT NULL (commented out to avoid breaking existing data)
            // if (Schema::hasColumn('returns', 'delivery_boy_id')) {
            //     \DB::statement('ALTER TABLE `returns` MODIFY `delivery_boy_id` BIGINT UNSIGNED NOT NULL');
            // }
        });
    }
};
