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
        // Reorder Point Configurations
        Schema::create('reorder_point_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('average_daily_sales', 10, 2)->default(0);
            $table->integer('lead_time_days')->default(2); // Average vendor lead time
            $table->integer('safety_stock_days')->default(2); // Buffer days
            $table->decimal('reorder_point', 10, 2); // Calculated reorder point
            $table->decimal('seasonal_factor', 5, 2)->default(1.0); // 1.0 = normal, >1 = high demand
            $table->integer('calculation_period_days')->default(30); // Period for average calculation
            $table->timestamp('last_calculated_at')->nullable();
            $table->boolean('auto_reorder_enabled')->default(true);
            $table->timestamps();
            
            $table->unique(['product_id', 'branch_id']);
            $table->index(['auto_reorder_enabled', 'reorder_point']);
        });

        // Vendor Lead Time Tracking
        Schema::create('vendor_lead_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('lead_time_days'); // Actual lead time
            $table->date('order_date');
            $table->date('delivery_date');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['vendor_id', 'product_id']);
            $table->index('order_date');
        });

        // Seasonal Adjustments
        Schema::create('seasonal_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Diwali", "Summer", "Monsoon"
            $table->date('start_date');
            $table->date('end_date');
            $table->string('category')->nullable(); // Apply to specific category
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('demand_multiplier', 5, 2)->default(1.0); // 1.5 = 50% increase
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['start_date', 'end_date', 'is_active']);
        });

        // Demand Forecast
        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('forecast_date');
            $table->decimal('forecasted_demand', 10, 2);
            $table->decimal('actual_demand', 10, 2)->nullable();
            $table->decimal('forecast_accuracy', 5, 2)->nullable(); // Percentage
            $table->string('forecast_method')->default('moving_average'); // moving_average, weighted_average, ml
            $table->json('calculation_data')->nullable(); // Store ML model parameters
            $table->timestamps();
            
            $table->unique(['product_id', 'branch_id', 'forecast_date']);
            $table->index('forecast_date');
        });

        // Auto Purchase Orders
        Schema::create('auto_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('current_stock', 10, 2);
            $table->decimal('reorder_point', 10, 2);
            $table->decimal('recommended_quantity', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'generated'])->default('pending');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('calculation_details')->nullable(); // Store calculation breakdown
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['product_id', 'branch_id']);
        });

        // Reorder Alerts
        Schema::create('reorder_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('current_stock', 10, 2);
            $table->decimal('reorder_point', 10, 2);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            $table->index(['is_resolved', 'severity']);
            $table->index(['product_id', 'branch_id']);
        });

        // Add fields to products table for ML forecasting
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('enable_auto_reorder')->default(true)->after('is_active');
            $table->decimal('optimal_stock_level', 10, 2)->nullable()->after('enable_auto_reorder');
            $table->integer('min_order_quantity')->default(1)->after('optimal_stock_level');
            $table->integer('max_order_quantity')->nullable()->after('min_order_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['enable_auto_reorder', 'optimal_stock_level', 'min_order_quantity', 'max_order_quantity']);
        });

        Schema::dropIfExists('reorder_alerts');
        Schema::dropIfExists('auto_purchase_orders');
        Schema::dropIfExists('demand_forecasts');
        Schema::dropIfExists('seasonal_adjustments');
        Schema::dropIfExists('vendor_lead_times');
        Schema::dropIfExists('reorder_point_configs');
    }
};
