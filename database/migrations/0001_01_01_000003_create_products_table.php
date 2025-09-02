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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('category', ['fruit', 'vegetable', 'leafy', 'exotic']);
            $table->enum('weight_unit', ['kg', 'gm', 'pcs']);
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('mrp', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->integer('stock_threshold')->default(1); // in kg/gm/pcs
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('selling_price', 10, 2);
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->boolean('is_available_online')->default(true);
            $table->timestamps();
            
            $table->unique(['product_id', 'branch_id']);
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('gst_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->decimal('supply_price', 10, 2);
            $table->boolean('is_primary_supplier')->default(false);
            $table->timestamps();
            
            $table->unique(['product_id', 'vendor_id']);
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('batch_number')->unique();
            $table->decimal('initial_quantity', 10, 2);
            $table->decimal('current_quantity', 10, 2);
            $table->date('expiry_date')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_price', 10, 2);
            $table->enum('status', ['active', 'expired', 'sold_out'])->default('active');
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['purchase', 'sale', 'adjustment', 'loss', 'return']);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('loss_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('loss_type', ['weight_loss', 'water_loss', 'wastage', 'complimentary']);
            $table->decimal('quantity_lost', 10, 2);
            $table->decimal('financial_loss', 10, 2);
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_tracking');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('product_vendors');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('product_branches');
        Schema::dropIfExists('products');
    }
};