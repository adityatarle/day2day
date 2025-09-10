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
            $table->foreignId('pos_session_id')->nullable()->constrained('pos_sessions')->onDelete('set null');
            $table->index(['pos_session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['pos_session_id', 'created_at']);
            $table->dropForeign(['pos_session_id']);
            $table->dropColumn(['pos_session_id']);
        });
    }
};
