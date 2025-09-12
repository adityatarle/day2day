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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Fulfillment tracking
            $table->decimal('fulfilled_quantity', 10, 2)->nullable()->after('received_quantity');
            
            // Actual receipt tracking
            $table->decimal('actual_received_quantity', 10, 2)->nullable()->after('fulfilled_quantity');
            $table->decimal('actual_weight', 10, 3)->nullable()->after('actual_received_quantity');
            $table->decimal('expected_weight', 10, 3)->nullable()->after('actual_weight');
            $table->decimal('weight_difference', 10, 3)->nullable()->after('expected_weight');
            
            // Discrepancy tracking
            $table->decimal('spoiled_quantity', 10, 2)->default(0)->after('weight_difference');
            $table->decimal('damaged_quantity', 10, 2)->default(0)->after('spoiled_quantity');
            $table->decimal('usable_quantity', 10, 2)->nullable()->after('damaged_quantity');
            
            // Quality and fulfillment notes
            $table->text('quality_notes')->nullable()->after('usable_quantity');
            $table->text('fulfillment_notes')->nullable()->after('quality_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'fulfilled_quantity',
                'actual_received_quantity',
                'actual_weight',
                'expected_weight',
                'weight_difference',
                'spoiled_quantity',
                'damaged_quantity',
                'usable_quantity',
                'quality_notes',
                'fulfillment_notes',
            ]);
        });
    }
};