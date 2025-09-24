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
        Schema::create('cash_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pos_session_id')->nullable()->constrained('pos_sessions')->onDelete('set null');
            $table->enum('entry_type', ['give', 'take']); // give = cash out, take = cash in
            $table->decimal('amount', 12, 2);
            $table->string('counterparty')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('entry_date')->useCurrent();
            $table->timestamps();

            $table->index(['branch_id', 'entry_date']);
            $table->index(['user_id', 'entry_date']);
            $table->index(['pos_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_ledger_entries');
    }
};

