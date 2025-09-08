<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix product_vendors table inconsistencies
        if (Schema::hasTable('product_vendors')) {
            Schema::table('product_vendors', function (Blueprint $table) {
                // Remove conflicting columns if they exist
                if (Schema::hasColumn('product_vendors', 'vendor_price')) {
                    $table->dropColumn('vendor_price');
                }
                if (Schema::hasColumn('product_vendors', 'is_primary')) {
                    $table->dropColumn('is_primary');
                }
            });
        }

        // Ensure orders table has status = 'completed' instead of 'delivered'
        if (Schema::hasTable('orders')) {
            // Update existing orders with status 'delivered' to 'completed'
            DB::table('orders')->where('status', 'delivered')->update(['status' => 'completed']);
        }

        // Create missing credit_transactions table if it doesn't exist
        if (!Schema::hasTable('credit_transactions')) {
            Schema::create('credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['credit_received', 'credit_paid']);
                $table->decimal('amount', 10, 2);
                $table->text('description');
                $table->timestamps();
            });
        }

        // Ensure product model relationships work correctly by checking for missing methods
        // This is handled in the model files, but we need to ensure the database supports it
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't reverse the status changes as they might cause issues
        Schema::dropIfExists('credit_transactions');
    }
};