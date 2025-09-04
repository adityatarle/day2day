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
        Schema::table('branches', function (Blueprint $table) {
            // Add city_id column if it doesn't exist
            if (!Schema::hasColumn('branches', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            }
            
            // Add other columns if they don't exist
            if (!Schema::hasColumn('branches', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('branches', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('branches', 'outlet_type')) {
                $table->string('outlet_type')->default('retail'); // retail, wholesale, kiosk
            }
            if (!Schema::hasColumn('branches', 'operating_hours')) {
                $table->json('operating_hours')->nullable(); // Store operating hours
            }
            if (!Schema::hasColumn('branches', 'pos_enabled')) {
                $table->boolean('pos_enabled')->default(true);
            }
            if (!Schema::hasColumn('branches', 'pos_terminal_id')) {
                $table->string('pos_terminal_id')->nullable()->unique();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Drop foreign key if it exists
            if (Schema::hasColumn('branches', 'city_id')) {
                $table->dropForeign(['city_id']);
            }
            
            // Drop columns if they exist
            $columnsToCheck = [
                'city_id',
                'latitude',
                'longitude',
                'outlet_type',
                'operating_hours',
                'pos_enabled',
                'pos_terminal_id'
            ];
            
            $columnsToRemove = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('branches', $column)) {
                    $columnsToRemove[] = $column;
                }
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
