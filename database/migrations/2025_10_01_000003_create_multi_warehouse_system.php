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
        // Warehouse Management
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['central', 'branch', 'cold_storage', 'dry_storage'])->default('branch');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->decimal('storage_capacity', 10, 2)->nullable(); // in kg or units
            $table->decimal('current_utilization', 10, 2)->default(0);
            $table->json('storage_zones')->nullable(); // ['cold_storage_1', 'dry_storage_1', ...]
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
        });

        // Warehouse Stock Allocation
        Schema::create('warehouse_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('allocated_quantity', 10, 2)->default(0);
            $table->decimal('available_quantity', 10, 2)->default(0);
            $table->decimal('reserved_quantity', 10, 2)->default(0); // Reserved for pending transfers
            $table->decimal('minimum_quantity', 10, 2)->default(0);
            $table->decimal('maximum_quantity', 10, 2)->nullable();
            $table->string('storage_zone')->nullable();
            $table->timestamps();
            
            $table->unique(['warehouse_id', 'product_id']);
            $table->index('product_id');
        });

        // Inter-Warehouse Transfers (Enhanced)
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('from_warehouse_id')->nullable()->after('from_branch_id')->constrained('warehouses')->onDelete('set null');
            $table->foreignId('to_warehouse_id')->nullable()->after('to_branch_id')->constrained('warehouses')->onDelete('set null');
            $table->enum('transfer_type', ['branch_to_branch', 'warehouse_to_branch', 'branch_to_warehouse', 'warehouse_to_warehouse'])->default('branch_to_branch')->after('status');
            $table->decimal('transfer_cost', 10, 2)->default(0)->after('transfer_type');
            
            // Only add vehicle_details if it doesn't exist
            if (!Schema::hasColumn('stock_transfers', 'vehicle_details')) {
                $table->text('vehicle_details')->nullable()->after('transfer_cost');
            }
            
            // Only add driver_name if it doesn't exist (it was added in an earlier migration)
            if (!Schema::hasColumn('stock_transfers', 'driver_name')) {
                $table->string('driver_name')->nullable()->after('vehicle_details');
            }
            
            // Only add driver_phone if it doesn't exist (it was added in an earlier migration)
            if (!Schema::hasColumn('stock_transfers', 'driver_phone')) {
                $table->string('driver_phone')->nullable()->after('driver_name');
            }
            
            $table->timestamp('estimated_arrival')->nullable()->after('expected_delivery');
            $table->timestamp('actual_arrival')->nullable()->after('estimated_arrival');
        });

        // Transfer Cost Calculation
        Schema::create('transfer_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->decimal('fuel_cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('packaging_cost', 10, 2)->default(0);
            $table->decimal('other_costs', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->text('cost_breakdown')->nullable();
            $table->timestamps();
        });

        // Warehouse Allocation Rules
        Schema::create('warehouse_allocation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('product_category')->nullable();
            $table->foreignId('preferred_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->integer('priority')->default(1); // Higher number = higher priority
            $table->text('conditions')->nullable(); // JSON conditions for allocation
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['product_id', 'is_active']);
            $table->index(['product_category', 'is_active']);
        });

        // Warehouse Proximity to Branches (for optimal selection)
        Schema::create('warehouse_branch_proximity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('distance_km', 8, 2);
            $table->integer('estimated_travel_minutes')->default(0);
            $table->decimal('standard_transfer_cost', 10, 2)->default(0);
            $table->boolean('is_primary_supplier')->default(false); // Primary warehouse for this branch
            $table->timestamps();
            
            $table->unique(['warehouse_id', 'branch_id']);
        });

        // Warehouse Performance Metrics
        Schema::create('warehouse_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->date('metric_date');
            $table->decimal('total_inbound_quantity', 10, 2)->default(0);
            $table->decimal('total_outbound_quantity', 10, 2)->default(0);
            $table->integer('total_transfers_in')->default(0);
            $table->integer('total_transfers_out')->default(0);
            $table->decimal('utilization_percentage', 5, 2)->default(0);
            $table->decimal('total_transfer_cost', 10, 2)->default(0);
            $table->integer('total_products_stored')->default(0);
            $table->json('top_products')->nullable(); // Top 10 products by quantity
            $table->timestamps();
            
            $table->unique(['warehouse_id', 'metric_date']);
        });

        // Add warehouse support to products table
        Schema::table('products', function (Blueprint $table) {
            $table->enum('storage_type', ['ambient', 'cold', 'frozen', 'dry'])->default('ambient')->after('storage_temperature');
            $table->foreignId('preferred_warehouse_id')->nullable()->after('storage_type')->constrained('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['preferred_warehouse_id']);
            $table->dropColumn(['storage_type', 'preferred_warehouse_id']);
        });

        Schema::dropIfExists('warehouse_performance_metrics');
        Schema::dropIfExists('warehouse_branch_proximity');
        Schema::dropIfExists('warehouse_allocation_rules');
        Schema::dropIfExists('transfer_costs');

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['from_warehouse_id']);
            $table->dropForeign(['to_warehouse_id']);
            
            $columnsToDrop = [
                'from_warehouse_id',
                'to_warehouse_id',
                'transfer_type',
                'transfer_cost',
                'estimated_arrival',
                'actual_arrival',
            ];
            
            // Only drop vehicle_details if we added it (check if it exists)
            if (Schema::hasColumn('stock_transfers', 'vehicle_details')) {
                $columnsToDrop[] = 'vehicle_details';
            }
            
            // Don't drop driver_name and driver_phone as they were added by an earlier migration
            
            $table->dropColumn($columnsToDrop);
        });

        Schema::dropIfExists('warehouse_stock');
        Schema::dropIfExists('warehouses');
    }
};
