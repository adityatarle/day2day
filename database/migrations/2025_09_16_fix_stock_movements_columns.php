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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Add missing columns only if they don't exist
            if (!Schema::hasColumn('stock_movements', 'reference_type')) {
                $table->string('reference_type')->after('user_id')->nullable();
            }
            if (!Schema::hasColumn('stock_movements', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->after('reference_type')->nullable();
            }
            if (!Schema::hasColumn('stock_movements', 'movement_date')) {
                $table->timestamp('movement_date')->after('reference_id')->nullable();
            }
            
            // Add index for better performance
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex(['reference_type', 'reference_id']);
            
            // Drop columns if they exist
            if (Schema::hasColumn('stock_movements', 'reference_type')) {
                $table->dropColumn('reference_type');
            }
            if (Schema::hasColumn('stock_movements', 'reference_id')) {
                $table->dropColumn('reference_id');
            }
            if (Schema::hasColumn('stock_movements', 'movement_date')) {
                $table->dropColumn('movement_date');
            }
        });
    }
};