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
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // who recorded expense
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->enum('payment_method', ['cash', 'bank', 'upi', 'card']);
            $table->string('reference_number')->nullable(); // invoice/bill number
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['customer_payment', 'vendor_payment', 'expense_payment']);
            $table->foreignId('payable_id'); // polymorphic relationship
            $table->string('payable_type'); // Order, PurchaseOrder, Expense
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank', 'upi', 'card', 'credit']);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('payment_date');
            $table->timestamps();
        });

        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['credit_given', 'credit_received', 'credit_paid', 'credit_received_payment']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->text('description');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('transaction_date');
            $table->timestamps();
        });

        Schema::create('gst_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "0%", "5%", "12%", "18%", "28%"
            $table->decimal('rate', 5, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_gst_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('gst_rate_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['product_id', 'gst_rate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_gst_rates');
        Schema::dropIfExists('gst_rates');
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};