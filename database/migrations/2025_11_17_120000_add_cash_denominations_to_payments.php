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
        // Add cash denominations JSON column to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'cash_denominations')) {
                $table->json('cash_denominations')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('payments', 'upi_qr_code')) {
                $table->text('upi_qr_code')->nullable()->after('reference_number');
            }
        });

        // Create cash_denomination_breakdowns table for detailed tracking
        if (!Schema::hasTable('cash_denomination_breakdowns')) {
            Schema::create('cash_denomination_breakdowns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained()->onDelete('cascade');
                $table->decimal('denomination_2000', 10, 2)->default(0);
                $table->decimal('denomination_500', 10, 2)->default(0);
                $table->decimal('denomination_200', 10, 2)->default(0);
                $table->decimal('denomination_100', 10, 2)->default(0);
                $table->decimal('denomination_50', 10, 2)->default(0);
                $table->decimal('denomination_20', 10, 2)->default(0);
                $table->decimal('denomination_10', 10, 2)->default(0);
                $table->decimal('denomination_5', 10, 2)->default(0);
                $table->decimal('denomination_2', 10, 2)->default(0);
                $table->decimal('denomination_1', 10, 2)->default(0);
                $table->decimal('coins', 10, 2)->default(0);
                $table->decimal('total_cash', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'cash_denominations')) {
                $table->dropColumn('cash_denominations');
            }
            if (Schema::hasColumn('payments', 'upi_qr_code')) {
                $table->dropColumn('upi_qr_code');
            }
        });

        Schema::dropIfExists('cash_denomination_breakdowns');
    }
};




