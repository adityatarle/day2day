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
        Schema::create('city_product_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('selling_price', 10, 2);
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();
            
            // Ensure unique pricing per city-product combination for active periods
            $table->unique(['city_id', 'product_id', 'effective_from'], 'city_product_pricing_unique');
            $table->index(['city_id', 'is_available']);
            $table->index(['product_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_product_pricing');
    }
};
