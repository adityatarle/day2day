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
        // Add new columns to expenses table
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('expense_type', ['transport', 'labour', 'operational', 'overhead', 'direct'])
                  ->default('operational')
                  ->after('status');
            $table->enum('allocation_method', ['equal', 'weighted', 'manual', 'none'])
                  ->default('none')
                  ->after('expense_type');
        });

        // Create expense_allocations table
        Schema::create('expense_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('allocated_amount', 10, 2);
            $table->decimal('allocation_weight', 8, 2)->nullable();
            $table->date('allocation_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['expense_id', 'product_id']);
            $table->index('allocation_date');
        });

        // Update expense categories with transport, labour, and operational categories
        DB::table('expense_categories')->insert([
            [
                'name' => 'Transport',
                'code' => 'TRANSPORT',
                'description' => 'Transportation costs including CNG, Diesel, and delivery vehicle expenses',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Labour',
                'code' => 'LABOUR',
                'description' => 'Labour costs including loading, unloading, and operational staff',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Operational',
                'code' => 'OPERATIONAL',
                'description' => 'Other operational costs including rent, electricity, and miscellaneous',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_allocations');
        
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['expense_type', 'allocation_method']);
        });

        // Remove the added expense categories
        DB::table('expense_categories')->whereIn('code', ['TRANSPORT', 'LABOUR', 'OPERATIONAL'])->delete();
    }
};