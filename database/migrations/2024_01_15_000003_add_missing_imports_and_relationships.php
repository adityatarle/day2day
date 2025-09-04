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
        // Add missing columns to product_vendors pivot table
        Schema::table('product_vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('product_vendors', 'vendor_price')) {
                $table->decimal('vendor_price', 10, 2)->default(0)->after('vendor_id');
            }
            if (!Schema::hasColumn('product_vendors', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('vendor_price');
            }
        });

        // Ensure product_branches has all required columns
        if (!Schema::hasColumn('product_branches', 'selling_price')) {
            Schema::table('product_branches', function (Blueprint $table) {
                $table->decimal('selling_price', 10, 2)->default(0)->after('current_stock');
                $table->boolean('is_available_online')->default(true)->after('selling_price');
            });
        }

        // Add HSN code to products if not exists
        if (!Schema::hasColumn('products', 'hsn_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('hsn_code')->nullable()->after('code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_vendors', function (Blueprint $table) {
            $table->dropColumn(['vendor_price', 'is_primary']);
        });

        if (Schema::hasColumn('product_branches', 'selling_price')) {
            Schema::table('product_branches', function (Blueprint $table) {
                $table->dropColumn(['selling_price', 'is_available_online']);
            });
        }

        if (Schema::hasColumn('products', 'hsn_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('hsn_code');
            });
        }
    }
};