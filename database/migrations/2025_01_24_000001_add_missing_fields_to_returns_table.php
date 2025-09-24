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
        Schema::table('returns', function (Blueprint $table) {
            // Add total_amount field to match controller expectations
            $table->decimal('total_amount', 10, 2)->nullable()->after('refund_amount');
            
            // Add created_by field to track who created the return
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('delivery_boy_id');
            
            // Add reason field (controller expects 'reason' but table has 'return_reason')
            $table->text('reason')->nullable()->after('return_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['total_amount', 'created_by', 'reason']);
        });
    }
};