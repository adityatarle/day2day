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
        // Create local purchases table
        Schema::create('local_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->string('vendor_name')->nullable(); // For one-time vendors
            $table->string('vendor_phone')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_method', ['cash', 'upi', 'credit', 'bank_transfer', 'card', 'other'])->default('cash');
            $table->string('payment_reference')->nullable();
            $table->date('purchase_date');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable(); // For uploaded receipts/invoices
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->foreignId('expense_id')->nullable()->constrained()->onDelete('set null'); // Link to expense record
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null'); // Link to PO if fulfilling
            $table->timestamps();
            
            // Indexes
            $table->index(['branch_id', 'purchase_date']);
            $table->index(['vendor_id']);
            $table->index('status');
        });

        // Create local purchase items table
        Schema::create('local_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 3);
            $table->string('unit')->default('kg');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['local_purchase_id', 'product_id']);
        });

        // Create local purchase notifications table
        Schema::create('local_purchase_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who should receive notification
            $table->enum('type', ['created', 'approved', 'rejected', 'updated'])->default('created');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index(['local_purchase_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_purchase_notifications');
        Schema::dropIfExists('local_purchase_items');
        Schema::dropIfExists('local_purchases');
    }
};