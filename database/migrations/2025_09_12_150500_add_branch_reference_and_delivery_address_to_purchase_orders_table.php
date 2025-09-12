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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Link to a branch request (self reference to purchase_orders table when order_type = branch_request)
            $table->unsignedBigInteger('branch_request_id')->nullable()->after('branch_id');

            // Delivery address handling
            // admin_main (warehouse/main address), branch (deliver directly to a branch), custom (free text)
            $table->enum('delivery_address_type', ['admin_main', 'branch', 'custom'])
                  ->default('admin_main')
                  ->after('order_type');

            // If delivery to a specific branch (could be different from ordering branch)
            $table->unsignedBigInteger('ship_to_branch_id')->nullable()->after('delivery_address_type');

            // Custom address text when type = custom
            $table->text('delivery_address')->nullable()->after('ship_to_branch_id');

            // Foreign keys
            $table->foreign('branch_request_id')->references('id')->on('purchase_orders')->onDelete('set null');
            $table->foreign('ship_to_branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['branch_request_id']);
            $table->dropForeign(['ship_to_branch_id']);
            $table->dropColumn([
                'branch_request_id',
                'delivery_address_type',
                'ship_to_branch_id',
                'delivery_address',
            ]);
        });
    }
};

