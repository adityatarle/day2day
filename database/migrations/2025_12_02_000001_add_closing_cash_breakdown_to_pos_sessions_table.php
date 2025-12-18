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
        Schema::table('pos_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_sessions', 'closing_cash_breakdown')) {
                $table->json('closing_cash_breakdown')->nullable()->after('session_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('pos_sessions', 'closing_cash_breakdown')) {
                $table->dropColumn('closing_cash_breakdown');
            }
        });
    }
};





