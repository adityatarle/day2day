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
        if (!Schema::hasTable('purchase_orders')) {
            return;
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'branch_request_id')) {
                $table->unsignedBigInteger('branch_request_id')->nullable()->after('branch_id');
            }

            if (!Schema::hasColumn('purchase_orders', 'delivery_address_type')) {
                $table->enum('delivery_address_type', ['admin_main', 'branch', 'custom'])
                      ->default('admin_main')
                      ->after('order_type');
            }

            if (!Schema::hasColumn('purchase_orders', 'ship_to_branch_id')) {
                $table->unsignedBigInteger('ship_to_branch_id')->nullable()->after('delivery_address_type');
            }

            if (!Schema::hasColumn('purchase_orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('ship_to_branch_id');
            }
        });

        // Check if foreign keys already exist before trying to create them
        $existingForeignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'purchase_orders' 
            AND CONSTRAINT_NAME LIKE '%_foreign'
        ");
        
        $existingConstraintNames = array_column($existingForeignKeys, 'CONSTRAINT_NAME');
        
        Schema::table('purchase_orders', function (Blueprint $table) use ($existingConstraintNames) {
            // Add foreign keys only if columns exist and FKs aren't already present
            if (Schema::hasColumn('purchase_orders', 'branch_request_id')) {
                $constraintName = 'purchase_orders_branch_request_id_foreign';
                if (!in_array($constraintName, $existingConstraintNames)) {
                    try {
                        // Check if branch_requests table exists before creating foreign key
                        if (Schema::hasTable('branch_requests')) {
                            $table->foreign('branch_request_id')->references('id')->on('branch_requests')->onDelete('set null');
                        }
                    } catch (\Throwable $e) {
                        // ignore if FK already exists
                    }
                }
            }
            if (Schema::hasColumn('purchase_orders', 'ship_to_branch_id')) {
                $constraintName = 'purchase_orders_ship_to_branch_id_foreign';
                if (!in_array($constraintName, $existingConstraintNames)) {
                    try {
                        $table->foreign('ship_to_branch_id')->references('id')->on('branches')->onDelete('set null');
                    } catch (\Throwable $e) {
                        // ignore if FK already exists
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('purchase_orders')) {
            return;
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Best-effort cleanup; ignore errors if constraints are missing
            try { 
                if (Schema::hasTable('branch_requests')) {
                    $table->dropForeign(['branch_request_id']); 
                }
            } catch (\Throwable $e) {}
            try { $table->dropForeign(['ship_to_branch_id']); } catch (\Throwable $e) {}

            if (Schema::hasColumn('purchase_orders', 'delivery_address')) {
                $table->dropColumn('delivery_address');
            }
            if (Schema::hasColumn('purchase_orders', 'ship_to_branch_id')) {
                $table->dropColumn('ship_to_branch_id');
            }
            if (Schema::hasColumn('purchase_orders', 'delivery_address_type')) {
                $table->dropColumn('delivery_address_type');
            }
            if (Schema::hasColumn('purchase_orders', 'branch_request_id')) {
                $table->dropColumn('branch_request_id');
            }
        });
    }
};

