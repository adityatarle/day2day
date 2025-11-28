<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the weight_unit enum to include all unit types
        // Since MySQL doesn't support ALTER ENUM directly, we need to use raw SQL
        DB::statement("ALTER TABLE products MODIFY COLUMN weight_unit ENUM('kg', 'gram', 'piece', 'dozen', 'packet', 'box') DEFAULT 'kg'");
        
        // Add bill_by field to products table
        Schema::table('products', function (Blueprint $table) {
            $table->enum('bill_by', ['weight', 'count'])->default('weight')->after('weight_unit');
        });
        
        // Add unit field to order_items for tracking the unit used in sale
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('unit', 20)->nullable()->after('quantity')->comment('Unit used: kg, gram, piece, dozen, packet, box');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('bill_by');
        });
        
        // Revert weight_unit enum
        DB::statement("ALTER TABLE products MODIFY COLUMN weight_unit ENUM('kg', 'gm', 'pcs') DEFAULT 'kg'");
    }
};
