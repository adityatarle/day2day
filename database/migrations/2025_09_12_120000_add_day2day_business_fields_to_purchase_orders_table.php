<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add Day2Day business-specific fields to support the proper workflow:
     * - Sub-branches send purchase requests to main branch only
     * - Material receipts can come from main branch or vendors (via main branch)
     * - Priority levels for branch requests
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Update order_type enum to include new Day2Day types
            $table->dropColumn('order_type');
        });
        
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Add enhanced order type for Day2Day business model
            $table->enum('order_type', [
                'purchase_order',    // Main branch orders to vendors
                'received_order',    // When purchase order is received
                'branch_request',    // Sub-branch requests to main branch
                'material_receipt'   // Sub-branch material receipts (from main branch or vendor via main branch)
            ])->default('purchase_order')
              ->after('status')
              ->comment('Day2Day order types: purchase_order, received_order, branch_request, material_receipt');
            
            // Add priority field for branch requests
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                  ->default('medium')
                  ->after('order_type')
                  ->comment('Priority level for branch requests to main branch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['priority']);
            $table->dropColumn('order_type');
        });
        
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Restore original order_type enum
            $table->enum('order_type', ['purchase_order', 'received_order'])
                  ->default('purchase_order')
                  ->after('status');
        });
    }
};