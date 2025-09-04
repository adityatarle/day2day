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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable(); // Postal/ZIP code
            $table->string('country')->default('India');
            $table->string('code', 10)->unique(); // City code like 'DEL', 'MUM', 'BLR'
            $table->decimal('delivery_charge', 8, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // City-specific tax rate
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
