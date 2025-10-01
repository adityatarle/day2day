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
        // Cycle Counting Schedule
        Schema::create('cycle_count_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('product_category')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly'])->default('monthly');
            $table->json('schedule_days')->nullable(); // [1, 15] for 1st and 15th of month
            $table->date('next_count_date');
            $table->date('last_count_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['next_count_date', 'is_active']);
            $table->index('product_category');
        });

        // Physical Count Sessions
        Schema::create('physical_count_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number')->unique();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('cycle_count_schedule_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('count_type', ['full', 'cycle', 'spot', 'blind'])->default('cycle');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->date('scheduled_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('conducted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'scheduled_date']);
        });

        // Physical Count Items
        Schema::create('physical_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('physical_count_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('system_quantity', 10, 2); // Quantity as per system
            $table->decimal('counted_quantity', 10, 2)->nullable(); // Actual counted quantity
            $table->decimal('variance', 10, 2)->default(0); // Difference
            $table->decimal('variance_percentage', 5, 2)->default(0);
            $table->decimal('value_variance', 10, 2)->default(0); // Financial impact
            $table->enum('variance_type', ['match', 'overage', 'shortage'])->default('match');
            $table->string('storage_location')->nullable();
            $table->string('barcode_scanned')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'product_id']);
            $table->index('variance_type');
        });

        // Variance Analysis
        Schema::create('variance_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_count_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('reconciliation_id')->nullable()->constrained('stock_reconciliations')->onDelete('set null');
            $table->enum('variance_category', [
                'theft',
                'spoilage',
                'measurement_error',
                'data_entry_error',
                'shrinkage',
                'spillage',
                'unrecorded_sale',
                'unrecorded_wastage',
                'system_error',
                'other'
            ]);
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->boolean('is_preventable')->default(true);
            $table->decimal('financial_impact', 10, 2);
            $table->foreignId('analyzed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });

        // Mobile Scanning Records
        Schema::create('mobile_scan_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('physical_count_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('barcode');
            $table->decimal('scanned_quantity', 10, 2);
            $table->string('storage_location')->nullable();
            $table->json('gps_coordinates')->nullable(); // {'lat': 12.34, 'lng': 56.78}
            $table->string('device_id')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();
            
            $table->index(['session_id', 'user_id']);
            $table->index('scanned_at');
        });

        // Reconciliation Approval Workflow
        Schema::table('stock_reconciliations', function (Blueprint $table) {
            $table->foreignId('physical_count_session_id')->nullable()->after('id')->constrained('physical_count_sessions')->onDelete('set null');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->decimal('total_variance_value', 10, 2)->default(0)->after('notes');
            $table->boolean('requires_investigation')->default(false)->after('total_variance_value');
        });

        // Shrinkage Tracking
        Schema::create('shrinkage_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->date('tracking_date');
            $table->decimal('expected_quantity', 10, 2);
            $table->decimal('actual_quantity', 10, 2);
            $table->decimal('shrinkage_quantity', 10, 2);
            $table->decimal('shrinkage_percentage', 5, 2);
            $table->decimal('shrinkage_value', 10, 2);
            $table->enum('shrinkage_type', ['theft', 'damage', 'expiry', 'administrative', 'unknown'])->nullable();
            $table->timestamps();
            
            $table->index(['tracking_date', 'product_id']);
        });

        // Accuracy Metrics
        Schema::create('inventory_accuracy_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('metric_date');
            $table->integer('total_items_counted')->default(0);
            $table->integer('items_with_variance')->default(0);
            $table->integer('items_within_tolerance')->default(0); // Within 2% variance
            $table->decimal('overall_accuracy_percentage', 5, 2)->default(0);
            $table->decimal('total_variance_value', 10, 2)->default(0);
            $table->decimal('average_variance_percentage', 5, 2)->default(0);
            $table->json('category_accuracy')->nullable(); // Accuracy by product category
            $table->timestamps();
            
            $table->unique(['branch_id', 'warehouse_id', 'metric_date']);
        });

        // Variance Tolerance Settings
        Schema::create('variance_tolerance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('product_category')->nullable();
            $table->decimal('tolerance_percentage', 5, 2)->default(2.0); // Default 2%
            $table->decimal('tolerance_value', 10, 2)->nullable(); // Absolute value tolerance
            $table->boolean('auto_adjust_within_tolerance')->default(true);
            $table->boolean('require_approval_outside_tolerance')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variance_tolerance_settings');
        Schema::dropIfExists('inventory_accuracy_metrics');
        Schema::dropIfExists('shrinkage_tracking');

        Schema::table('stock_reconciliations', function (Blueprint $table) {
            $table->dropForeign(['physical_count_session_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'physical_count_session_id',
                'approval_status',
                'approved_by',
                'approved_at',
                'rejection_reason',
                'total_variance_value',
                'requires_investigation',
            ]);
        });

        Schema::dropIfExists('mobile_scan_records');
        Schema::dropIfExists('variance_analyses');
        Schema::dropIfExists('physical_count_items');
        Schema::dropIfExists('physical_count_sessions');
        Schema::dropIfExists('cycle_count_schedules');
    }
};
