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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Add receive status to track partial/complete receives
            $table->enum('receive_status', ['not_received', 'partial', 'complete'])->default('not_received')->after('status');
            
            // Track total ordered and received quantities
            $table->decimal('total_ordered_quantity', 12, 2)->default(0)->after('total_amount');
            $table->decimal('total_received_quantity', 12, 2)->default(0)->after('total_ordered_quantity');
            
            // Add index for better performance
            $table->index(['status', 'receive_status']);
            $table->index('receive_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'receive_status']);
            $table->dropIndex(['receive_status']);
            
            $table->dropColumn([
                'receive_status',
                'total_ordered_quantity',
                'total_received_quantity'
            ]);
        });
    }
};