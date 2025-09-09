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
        // Transfers (Admin/DC -> Branch/Sub-branch)
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('to_branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('to_subbranch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->enum('status', [
                'draft',
                'approved',
                'dispatched',
                'in_transit',
                'delivered_pending_confirm',
                'received',
                'disputed',
                'reconciled'
            ])->default('draft');
            $table->timestamp('expected_dispatch_ts')->nullable();
            $table->timestamp('expected_arrival_ts')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('delivered_marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['from_branch_id', 'to_branch_id']);
            $table->index(['status']);
        });

        // Transfer lines
        Schema::create('transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('batch_number')->nullable();
            $table->decimal('expected_qty', 10, 2);
            $table->decimal('expected_weight_kg', 10, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('standard_cost', 10, 2)->nullable();
            $table->timestamps();
            $table->index(['transfer_id', 'product_id']);
        });

        // Shipments (dispatch details)
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade');
            $table->string('transporter_name')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('lr_no')->nullable();
            $table->string('seal_no')->nullable();
            $table->decimal('gross_weight_kg', 10, 2)->nullable();
            $table->decimal('tare_weight_kg', 10, 2)->nullable();
            $table->decimal('net_weight_kg', 10, 2)->nullable();
            $table->timestamp('dispatch_ts');
            $table->json('documents')->nullable();
            $table->timestamps();
            $table->index(['transfer_id']);
        });

        // Receipts (arrival & reweigh details)
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade');
            $table->foreignId('received_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('received_subbranch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->timestamp('arrival_ts');
            $table->decimal('reweigh_gross_kg', 10, 2)->nullable();
            $table->decimal('reweigh_tare_kg', 10, 2)->nullable();
            $table->decimal('reweigh_net_kg', 10, 2)->nullable();
            $table->boolean('within_tolerance')->default(false);
            $table->decimal('tolerance_percent', 5, 2)->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['transfer_id']);
        });

        // Discrepancies (header)
        Schema::create('discrepancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade');
            $table->enum('status', ['open', 'under_review', 'resolved', 'reopened'])->default('open');
            $table->enum('reason_category', [
                'weight_diff', 'damaged', 'spoiled', 'expired', 'short', 'excess', 'mispick', 'other'
            ]);
            $table->text('notes')->nullable();
            $table->foreignId('raised_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['transfer_id', 'status']);
        });

        // Discrepancy lines (itemized deltas)
        Schema::create('discrepancy_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discrepancy_id')->constrained('discrepancies')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('qty_delta', 10, 2)->nullable();
            $table->decimal('weight_delta_kg', 10, 2)->nullable();
            $table->enum('disposition', ['adjust', 'return', 'scrap', 'quarantine', 'replace']);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['discrepancy_id', 'product_id']);
        });

        // Attachments (polymorphic) for shipments/receipts/discrepancies
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // attachable_type, attachable_id
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->string('category')->nullable(); // e.g., weighbridge_slip, photo, invoice
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('discrepancy_lines');
        Schema::dropIfExists('discrepancies');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('transfer_lines');
        Schema::dropIfExists('transfers');
    }
};

