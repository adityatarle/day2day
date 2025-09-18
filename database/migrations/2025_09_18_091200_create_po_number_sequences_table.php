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
        Schema::create('po_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20); // e.g., 'BR-2025-', 'PO-2025-'
            $table->string('order_type', 50); // e.g., 'branch_request', 'purchase_order'
            $table->integer('year');
            $table->integer('current_sequence')->default(0);
            $table->timestamps();
            
            // Ensure unique combination of prefix, order_type, and year
            $table->unique(['prefix', 'order_type', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_number_sequences');
    }
};