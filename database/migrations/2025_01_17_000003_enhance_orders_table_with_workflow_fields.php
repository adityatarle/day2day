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
        Schema::table('orders', function (Blueprint $table) {
            // Add workflow timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Add workflow metadata
            $table->json('workflow_metadata')->nullable();
            $table->text('workflow_notes')->nullable();
            
            // Add priority and urgency
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_urgent')->default(false);
            
            // Add delivery information
            $table->string('delivery_address')->nullable();
            $table->string('delivery_phone')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->timestamp('expected_delivery_time')->nullable();
            
            // Add quality control
            $table->boolean('quality_checked')->default(false);
            $table->foreignId('quality_checked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('quality_checked_at')->nullable();
            
            // Add customer communication
            $table->boolean('customer_notified')->default(false);
            $table->timestamp('last_notification_sent_at')->nullable();
            
            // Add performance metrics
            $table->integer('processing_time_minutes')->nullable();
            $table->integer('delivery_time_minutes')->nullable();
            $table->integer('total_cycle_time_minutes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'confirmed_at',
                'processing_at', 
                'ready_at',
                'delivered_at',
                'cancelled_at',
                'workflow_metadata',
                'workflow_notes',
                'priority',
                'is_urgent',
                'delivery_address',
                'delivery_phone',
                'delivery_instructions',
                'expected_delivery_time',
                'quality_checked',
                'quality_checked_by',
                'quality_checked_at',
                'customer_notified',
                'last_notification_sent_at',
                'processing_time_minutes',
                'delivery_time_minutes',
                'total_cycle_time_minutes'
            ]);
        });
    }
};