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
        Schema::table('payments', function (Blueprint $table) {
            // Ensure cash_denominations column exists
            if (!Schema::hasColumn('payments', 'cash_denominations')) {
                $table->json('cash_denominations')->nullable()->after('amount');
            }

            // Ensure upi_qr_code column exists
            if (!Schema::hasColumn('payments', 'upi_qr_code')) {
                $table->text('upi_qr_code')->nullable()->after('reference_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Optional: do not drop columns automatically to avoid data loss
            // If you need to drop them, uncomment below:
            //
            // if (Schema::hasColumn('payments', 'cash_denominations')) {
            //     $table->dropColumn('cash_denominations');
            // }
            // if (Schema::hasColumn('payments', 'upi_qr_code')) {
            //     $table->dropColumn('upi_qr_code');
            // }
        });
    }
};
