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
        // Create stock transfers table for admin-initiated transfers to branches
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique(); // Auto-generated transfer number
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->onDelete('cascade'); // null for main warehouse
            $table->foreignId('to_branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade'); // Admin who initiated
            $table->enum('status', ['pending', 'in_transit', 'delivered', 'confirmed', 'cancelled'])->default('pending');
            $table->decimal('total_value', 12, 2)->default(0); // Total value of transfer
            $table->decimal('transport_cost', 10, 2)->default(0); // Transport expenses
            $table->string('transport_vendor')->nullable(); // Transport company/vendor
            $table->string('vehicle_number')->nullable(); // Vehicle details
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->timestamp('dispatch_date')->nullable(); // When dispatched
            $table->timestamp('expected_delivery')->nullable(); // Expected delivery date
            $table->timestamp('delivered_date')->nullable(); // Actual delivery date
            $table->timestamp('confirmed_date')->nullable(); // When branch confirmed receipt
            $table->text('dispatch_notes')->nullable(); // Admin notes
            $table->text('delivery_notes')->nullable(); // Delivery notes
            $table->json('documents')->nullable(); // Store document paths/URLs
            $table->timestamps();

            $table->index(['status', 'to_branch_id']);
            $table->index(['dispatch_date', 'expected_delivery']);
        });

        // Create stock transfer items table
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity_sent', 10, 3); // Quantity sent by admin
            $table->decimal('quantity_received', 10, 3)->nullable(); // Quantity received by branch
            $table->decimal('unit_price', 10, 2); // Unit price for financial calculations
            $table->decimal('total_value', 12, 2); // Total value (quantity_sent * unit_price)
            $table->string('unit_of_measurement', 20)->default('kg'); // kg, pieces, liters, etc.
            $table->date('expiry_date')->nullable(); // For perishable items
            $table->text('item_notes')->nullable(); // Notes for specific item
            $table->timestamps();

            $table->index(['stock_transfer_id', 'product_id']);
        });

        // Create stock transfer queries/issues table
        Schema::create('stock_transfer_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query_number')->unique(); // Auto-generated query number
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_transfer_item_id')->nullable()->constrained()->onDelete('cascade'); // Specific item query
            $table->foreignId('raised_by')->constrained('users')->onDelete('cascade'); // Branch manager who raised
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Admin assigned
            $table->enum('query_type', [
                'weight_difference', 'quantity_shortage', 'quality_issue', 
                'damaged_goods', 'expired_goods', 'missing_items', 'other'
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'escalated'])->default('open');
            $table->string('title'); // Brief title of the issue
            $table->text('description'); // Detailed description
            $table->decimal('expected_quantity', 10, 3)->nullable(); // What was expected
            $table->decimal('actual_quantity', 10, 3)->nullable(); // What was received
            $table->decimal('difference_quantity', 10, 3)->nullable(); // Difference
            $table->decimal('financial_impact', 12, 2)->default(0); // Financial impact of the issue
            $table->json('evidence_photos')->nullable(); // Store photo paths/URLs
            $table->json('documents')->nullable(); // Store document paths/URLs
            $table->text('resolution')->nullable(); // Resolution provided
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['query_type', 'stock_transfer_id']);
        });

        // Create query responses/communications table
        Schema::create('stock_query_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_query_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who responded
            $table->enum('response_type', ['comment', 'status_update', 'resolution', 'escalation']);
            $table->text('message');
            $table->json('attachments')->nullable(); // Store attachment paths/URLs
            $table->boolean('is_internal')->default(false); // Internal admin notes vs branch communication
            $table->timestamps();

            $table->index(['stock_transfer_query_id', 'created_at']);
        });

        // Create transport expenses table for detailed expense tracking
        Schema::create('transport_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->enum('expense_type', [
                'vehicle_rent', 'fuel', 'driver_payment', 'toll_charges', 
                'loading_charges', 'unloading_charges', 'insurance', 'other'
            ]);
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('vendor_name')->nullable(); // Who was paid
            $table->string('receipt_number')->nullable(); // Receipt/bill number
            $table->date('expense_date');
            $table->string('payment_method')->nullable(); // cash, bank, etc.
            $table->json('receipts')->nullable(); // Store receipt image paths
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_transfer_id', 'expense_type']);
        });

        // Create stock reconciliation table for branch weight adjustments
        Schema::create('stock_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade'); // Branch staff
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Branch manager
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reconciliation_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });

        // Create stock reconciliation items table
        Schema::create('stock_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_reconciliation_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('system_quantity', 10, 3); // Quantity as per system/transfer
            $table->decimal('physical_quantity', 10, 3); // Actual weighed quantity
            $table->decimal('variance', 10, 3); // Difference (physical - system)
            $table->decimal('variance_percentage', 5, 2); // Variance as percentage
            $table->enum('variance_type', ['shortage', 'excess', 'none']);
            $table->text('reason')->nullable(); // Reason for variance
            $table->decimal('financial_impact', 12, 2)->default(0); // Financial impact
            $table->timestamps();

            $table->index(['stock_reconciliation_id', 'product_id']);
        });

        // Create financial impact tracking table
        Schema::create('stock_financial_impacts', function (Blueprint $table) {
            $table->id();
            $table->morphs('impactable'); // Can be linked to queries, reconciliations, etc.
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->enum('impact_type', [
                'loss_damaged', 'loss_expired', 'loss_shortage', 'loss_quality',
                'gain_excess', 'transport_cost', 'handling_cost', 'other'
            ]);
            $table->decimal('amount', 12, 2); // Financial impact amount
            $table->enum('impact_category', ['direct_loss', 'indirect_loss', 'cost', 'recovery']);
            $table->text('description');
            $table->date('impact_date');
            $table->boolean('is_recoverable')->default(false); // Can this loss be recovered
            $table->decimal('recovered_amount', 12, 2)->default(0); // Amount recovered
            $table->text('recovery_notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'impact_type', 'impact_date']);
        });

        // Create stock alerts table for automated notifications
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('stock_transfer_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('alert_type', [
                'low_stock', 'expiry_warning', 'transfer_delay', 'query_pending',
                'reconciliation_required', 'financial_impact', 'quality_issue'
            ]);
            $table->enum('severity', ['info', 'warning', 'critical']);
            $table->string('title');
            $table->text('message');
            $table->json('recipients')->nullable(); // User IDs to notify
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'alert_type', 'is_resolved']);
            $table->index(['severity', 'is_read']);
        });

        // Enhance existing stock_movements table for better integration
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_movements', 'stock_transfer_id')) {
                    $table->foreignId('stock_transfer_id')->nullable()->constrained()->onDelete('set null')->after('reference_id');
                }
                if (!Schema::hasColumn('stock_movements', 'reconciliation_id')) {
                    $table->foreignId('reconciliation_id')->nullable()->constrained('stock_reconciliations')->onDelete('set null')->after('stock_transfer_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('stock_financial_impacts');
        Schema::dropIfExists('stock_reconciliation_items');
        Schema::dropIfExists('stock_reconciliations');
        Schema::dropIfExists('transport_expenses');
        Schema::dropIfExists('stock_query_responses');
        Schema::dropIfExists('stock_transfer_queries');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');

        // Remove added columns from stock_movements if they exist
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (Schema::hasColumn('stock_movements', 'stock_transfer_id')) {
                    $table->dropForeign(['stock_transfer_id']);
                    $table->dropColumn('stock_transfer_id');
                }
                if (Schema::hasColumn('stock_movements', 'reconciliation_id')) {
                    $table->dropForeign(['reconciliation_id']);
                    $table->dropColumn('reconciliation_id');
                }
            });
        }
    }
};