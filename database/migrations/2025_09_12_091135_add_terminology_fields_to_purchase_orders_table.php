<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add terminology fields to clarify Purchase Order vs Received Order distinction.
     * Following Tally conventions:
     * - Purchase Order: Outgoing orders to vendors
     * - Received Order: When status becomes "received" (incoming materials)
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Add a terminology type field to explicitly distinguish order types
            $table->enum('order_type', ['purchase_order', 'received_order'])
                  ->default('purchase_order')
                  ->after('status')
                  ->comment('Tally terminology: purchase_order (outgoing), received_order (incoming)');
            
            // Add a flag to indicate if this represents received materials
            $table->boolean('is_received_order')
                  ->default(false)
                  ->after('order_type')
                  ->comment('True when materials have been received (Received Order in Tally terms)');
            
            // Add terminology notes field for clarity
            $table->text('terminology_notes')
                  ->nullable()
                  ->after('notes')
                  ->comment('Additional notes about order terminology and status transitions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'is_received_order', 'terminology_notes']);
        });
    }
};
