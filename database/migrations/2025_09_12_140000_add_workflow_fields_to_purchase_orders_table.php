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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Approval fields
            $table->unsignedBigInteger('approved_by')->nullable()->after('priority');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Fulfillment fields
            $table->unsignedBigInteger('fulfilled_by')->nullable()->after('approved_at');
            $table->timestamp('fulfilled_at')->nullable()->after('fulfilled_by');
            
            // Receipt fields
            $table->unsignedBigInteger('received_by')->nullable()->after('fulfilled_at');
            $table->timestamp('received_at')->nullable()->after('received_by');
            
            // Cancellation fields
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('received_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            
            // Delivery tracking fields
            $table->text('delivery_notes')->nullable()->after('cancelled_at');
            $table->string('delivery_person')->nullable()->after('delivery_notes');
            $table->string('delivery_vehicle')->nullable()->after('delivery_person');
            
            // Foreign key constraints
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('fulfilled_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['fulfilled_by']);
            $table->dropForeign(['received_by']);
            $table->dropForeign(['cancelled_by']);
            
            $table->dropColumn([
                'approved_by',
                'approved_at',
                'fulfilled_by',
                'fulfilled_at',
                'received_by',
                'received_at',
                'cancelled_by',
                'cancelled_at',
                'delivery_notes',
                'delivery_person',
                'delivery_vehicle',
            ]);
        });
    }
};