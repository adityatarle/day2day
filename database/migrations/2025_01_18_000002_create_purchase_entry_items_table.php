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
        Schema::create('purchase_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Quantity tracking
            $table->decimal('expected_quantity', 12, 2);
            $table->decimal('received_quantity', 12, 2)->default(0);
            $table->decimal('spoiled_quantity', 12, 2)->default(0);
            $table->decimal('damaged_quantity', 12, 2)->default(0);
            $table->decimal('usable_quantity', 12, 2)->default(0);
            
            // Weight tracking
            $table->decimal('expected_weight', 12, 3)->nullable();
            $table->decimal('actual_weight', 12, 3)->nullable();
            $table->decimal('weight_difference', 12, 3)->default(0);
            
            // Pricing
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2)->default(0);
            
            // Notes
            $table->text('quality_notes')->nullable();
            $table->text('discrepancy_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['purchase_entry_id']);
            $table->index(['product_id']);
            $table->index(['purchase_order_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_entry_items');
    }
};