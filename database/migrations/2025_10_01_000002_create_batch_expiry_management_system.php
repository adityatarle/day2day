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
        // Expiry Alerts
        Schema::create('expiry_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('expiry_date');
            $table->integer('days_until_expiry');
            $table->enum('alert_type', ['7_days', '3_days', '1_day', 'expired'])->default('7_days');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->decimal('quantity_remaining', 10, 2);
            $table->boolean('is_acknowledged')->default(false);
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('action_taken')->nullable();
            $table->timestamps();
            
            $table->index(['expiry_date', 'is_acknowledged']);
            $table->index(['alert_type', 'severity']);
        });

        // Batch Price Adjustments (for near-expiry discounting)
        Schema::create('batch_price_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('original_price', 10, 2);
            $table->decimal('adjusted_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2);
            $table->date('effective_from');
            $table->date('effective_until');
            $table->enum('reason', ['near_expiry', 'overstocked', 'promotional', 'quality_issue', 'seasonal'])->default('near_expiry');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['batch_id', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
        });

        // Wastage Analytics
        Schema::create('wastage_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->date('wastage_date');
            $table->decimal('quantity_wasted', 10, 2);
            $table->decimal('value_wasted', 10, 2); // Financial impact
            $table->enum('wastage_reason', [
                'expired',
                'spoiled',
                'damaged',
                'quality_issue',
                'overstocked',
                'customer_return',
                'handling_error',
                'temperature_failure',
                'pest_infestation',
                'other'
            ]);
            $table->text('root_cause_analysis')->nullable();
            $table->text('corrective_action')->nullable();
            $table->boolean('is_preventable')->default(true);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['wastage_date', 'product_id']);
            $table->index(['wastage_reason', 'is_preventable']);
        });

        // Temperature & Humidity Monitoring (IoT Integration Ready)
        Schema::create('storage_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('storage_zone'); // e.g., 'cold_storage_1', 'dry_storage', 'refrigerator_2'
            $table->decimal('temperature', 5, 2); // Celsius
            $table->decimal('humidity', 5, 2); // Percentage
            $table->timestamp('recorded_at');
            $table->boolean('is_within_threshold')->default(true);
            $table->text('alert_message')->nullable();
            $table->json('sensor_data')->nullable(); // Store raw sensor data
            $table->timestamps();
            
            $table->index(['branch_id', 'storage_zone', 'recorded_at']);
            $table->index('is_within_threshold');
        });

        // Batch Recall Management
        Schema::create('batch_recalls', function (Blueprint $table) {
            $table->id();
            $table->string('recall_number')->unique();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('batch_number');
            $table->enum('recall_reason', [
                'quality_issue',
                'contamination',
                'mislabeling',
                'foreign_object',
                'allergen',
                'vendor_issue',
                'regulatory',
                'other'
            ]);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->date('recall_date');
            $table->decimal('quantity_recalled', 10, 2);
            $table->decimal('quantity_sold', 10, 2)->default(0);
            $table->enum('status', ['initiated', 'in_progress', 'completed', 'cancelled'])->default('initiated');
            $table->text('description');
            $table->text('corrective_action')->nullable();
            $table->json('affected_customers')->nullable(); // Customer IDs who purchased
            $table->boolean('customer_notification_sent')->default(false);
            $table->foreignId('initiated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('initiated_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'severity']);
            $table->index('recall_date');
        });

        // Shelf Life Tracking
        Schema::create('shelf_life_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('purchase_date');
            $table->date('expected_expiry_date');
            $table->date('actual_expiry_date')->nullable();
            $table->integer('expected_shelf_life_days');
            $table->integer('actual_shelf_life_days')->nullable();
            $table->decimal('shelf_life_utilization_percentage', 5, 2)->nullable();
            $table->enum('disposal_method', ['sold', 'wasted', 'donated', 'returned_to_vendor', 'other'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'branch_id']);
            $table->index('expected_expiry_date');
        });

        // Add fields to batches table
        Schema::table('batches', function (Blueprint $table) {
            $table->boolean('near_expiry_discount_applied')->default(false)->after('status');
            $table->enum('quality_status', ['excellent', 'good', 'fair', 'poor', 'rejected'])->default('excellent')->after('near_expiry_discount_applied');
            $table->decimal('temperature_at_receipt', 5, 2)->nullable()->after('quality_status');
            $table->string('storage_zone')->nullable()->after('temperature_at_receipt');
            $table->boolean('is_recalled')->default(false)->after('storage_zone');
            $table->foreignId('recall_id')->nullable()->constrained('batch_recalls')->onDelete('set null')->after('is_recalled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['recall_id']);
            $table->dropColumn([
                'near_expiry_discount_applied',
                'quality_status',
                'temperature_at_receipt',
                'storage_zone',
                'is_recalled',
                'recall_id',
            ]);
        });

        Schema::dropIfExists('shelf_life_tracking');
        Schema::dropIfExists('batch_recalls');
        Schema::dropIfExists('storage_conditions');
        Schema::dropIfExists('wastage_analytics');
        Schema::dropIfExists('batch_price_adjustments');
        Schema::dropIfExists('expiry_alerts');
    }
};
