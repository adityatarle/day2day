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
        Schema::create('purchase_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Entry details
            $table->date('entry_date');
            $table->date('delivery_date')->nullable();
            $table->string('delivery_person')->nullable();
            $table->string('delivery_vehicle')->nullable();
            $table->text('delivery_notes')->nullable();
            
            // Quantity tracking
            $table->decimal('total_expected_quantity', 12, 2)->default(0);
            $table->decimal('total_received_quantity', 12, 2)->default(0);
            $table->decimal('total_spoiled_quantity', 12, 2)->default(0);
            $table->decimal('total_damaged_quantity', 12, 2)->default(0);
            $table->decimal('total_usable_quantity', 12, 2)->default(0);
            
            // Weight tracking
            $table->decimal('total_expected_weight', 12, 3)->nullable();
            $table->decimal('total_actual_weight', 12, 3)->nullable();
            $table->decimal('total_weight_difference', 12, 3)->default(0);
            
            // Status and flags
            $table->enum('entry_status', ['draft', 'received', 'partial', 'discrepancy', 'completed'])->default('draft');
            $table->boolean('is_partial_receipt')->default(false);
            
            // Notes
            $table->text('quality_notes')->nullable();
            $table->text('discrepancy_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['branch_id', 'entry_date']);
            $table->index(['purchase_order_id']);
            $table->index(['entry_status']);
            $table->index(['is_partial_receipt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_entries');
    }
};