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
            if (!Schema::hasColumn('returns', 'cash_refund_amount')) {
                $table->decimal('cash_refund_amount', 10, 2)->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('returns', 'upi_refund_amount')) {
                $table->decimal('upi_refund_amount', 10, 2)->nullable()->after('cash_refund_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropColumn(['cash_refund_amount', 'upi_refund_amount']);
        });
    }
};
