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
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('outlet_type')->default('retail'); // retail, wholesale, kiosk
            $table->json('operating_hours')->nullable(); // Store operating hours
            $table->boolean('pos_enabled')->default(true);
            $table->string('pos_terminal_id')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn([
                'city_id',
                'latitude',
                'longitude',
                'outlet_type',
                'operating_hours',
                'pos_enabled',
                'pos_terminal_id'
            ]);
        });
    }
};
