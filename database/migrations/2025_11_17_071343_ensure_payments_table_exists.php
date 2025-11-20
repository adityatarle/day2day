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
        // Create payments table if it doesn't exist
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('customer_payment'); // customer_payment, vendor_payment, etc.
                $table->morphs('payable'); // polymorphic relationship (payable_id, payable_type)
                $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->enum('payment_method', ['cash', 'upi', 'card', 'bank_transfer', 'credit']);
                $table->enum('payment_type', ['order_payment', 'advance_payment', 'refund', 'adjustment'])->nullable();
                $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
                $table->string('reference_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('payment_date');
                $table->timestamps();
            });
        } else {
            // Table exists, just ensure required columns exist
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'payment_type')) {
                    $table->enum('payment_type', ['order_payment', 'advance_payment', 'refund', 'adjustment'])->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('payments', 'status')) {
                    $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed')->after('payment_type');
                }
                if (!Schema::hasColumn('payments', 'order_id')) {
                    $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null')->after('payable_type');
                }
                if (!Schema::hasColumn('payments', 'customer_id')) {
                    $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null')->after('order_id');
                }
                if (!Schema::hasColumn('payments', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null')->after('customer_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table in down() to avoid data loss
        // If you need to drop it, do it manually
    }
};
