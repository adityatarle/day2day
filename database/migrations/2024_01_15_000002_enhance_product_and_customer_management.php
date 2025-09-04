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
        // Enhance products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('subcategory')->nullable()->after('category');
            $table->integer('shelf_life_days')->nullable()->after('stock_threshold');
            $table->string('storage_temperature')->nullable()->after('shelf_life_days');
            $table->boolean('is_perishable')->default(true)->after('storage_temperature');
        });

        // Enhance customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('customer_type', [
                'walk_in', 
                'regular', 
                'regular_wholesale', 
                'premium_wholesale', 
                'distributor', 
                'retailer'
            ])->default('regular')->after('type');
            $table->decimal('credit_limit', 10, 2)->default(0)->after('customer_type');
            $table->integer('credit_days')->default(0)->after('credit_limit');
        });

        // Enhance orders table for adjustments
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('adjustment_amount', 10, 2)->default(0)->after('total_amount');
            $table->string('adjusted_invoice_number')->nullable()->after('adjustment_amount');
            $table->timestamp('adjustment_date')->nullable()->after('adjusted_invoice_number');
            $table->string('payment_terms')->nullable()->after('adjustment_date');
            $table->integer('credit_days')->nullable()->after('payment_terms');
        });

        // Enhance deliveries table for location tracking
        Schema::table('deliveries', function (Blueprint $table) {
            $table->decimal('current_latitude', 10, 8)->nullable()->after('return_reason');
            $table->decimal('current_longitude', 11, 8)->nullable()->after('current_latitude');
            $table->timestamp('last_location_update')->nullable()->after('current_longitude');
            $table->timestamp('pickup_time')->nullable()->after('last_location_update');
            $table->timestamp('delivery_time')->nullable()->after('pickup_time');
            $table->json('customer_adjustments')->nullable()->after('delivery_time');
        });

        // Enhance loss_tracking table
        Schema::table('loss_tracking', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('user_id');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->decimal('initial_quantity', 10, 2)->nullable()->after('reference_id');
            $table->decimal('final_quantity', 10, 2)->nullable()->after('initial_quantity');
        });

        // Create wholesale_pricing table
        Schema::create('wholesale_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('customer_type', [
                'regular_wholesale', 
                'premium_wholesale', 
                'distributor', 
                'retailer'
            ]);
            $table->decimal('min_quantity', 10, 2);
            $table->decimal('max_quantity', 10, 2)->nullable();
            $table->decimal('wholesale_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'customer_type']);
            $table->index(['customer_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wholesale_pricing');
        
        Schema::table('loss_tracking', function (Blueprint $table) {
            $table->dropColumn(['reference_type', 'reference_id', 'initial_quantity', 'final_quantity']);
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'current_latitude', 
                'current_longitude', 
                'last_location_update', 
                'pickup_time', 
                'delivery_time', 
                'customer_adjustments'
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'adjustment_amount', 
                'adjusted_invoice_number', 
                'adjustment_date', 
                'payment_terms', 
                'credit_days'
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_type', 'credit_limit', 'credit_days']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['subcategory', 'shelf_life_days', 'storage_temperature', 'is_perishable']);
        });
    }
};