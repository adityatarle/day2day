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
        // Enhance stock_movements table
        Schema::table('stock_movements', function (Blueprint $table) {
            // Add missing fields if they don't exist
            if (!Schema::hasColumn('stock_movements', 'movement_type')) {
                $table->string('movement_type')->after('type')->nullable();
            }
            if (!Schema::hasColumn('stock_movements', 'reference_type')) {
                $table->string('reference_type')->after('user_id')->nullable();
            }
            if (!Schema::hasColumn('stock_movements', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->after('reference_type')->nullable();
            }
            if (!Schema::hasColumn('stock_movements', 'movement_date')) {
                $table->timestamp('movement_date')->after('reference_id')->nullable();
            }
        });

        // Update the type enum to include more movement types
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->enum('type', [
                'purchase', 'sale', 'adjustment', 'loss', 'return', 
                'transfer_in', 'transfer_out', 'wastage', 'complimentary'
            ])->after('batch_id');
        });

        // Ensure batches table has the correct status enum
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('batches', function (Blueprint $table) {
            $table->enum('status', ['active', 'expired', 'sold_out', 'finished'])->default('active')->after('purchase_price');
        });

        // Add HSN code to products table if missing
        if (!Schema::hasColumn('products', 'hsn_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('hsn_code')->nullable()->after('code');
            });
        }

        // Create GST rates table if it doesn't exist
        if (!Schema::hasTable('gst_rates')) {
            Schema::create('gst_rates', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g., 'GST 5%', 'GST 12%', 'GST 18%'
                $table->decimal('rate', 5, 2); // GST rate percentage
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Create product_gst_rates pivot table if it doesn't exist
        if (!Schema::hasTable('product_gst_rates')) {
            Schema::create('product_gst_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->foreignId('gst_rate_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['product_id', 'gst_rate_id']);
            });
        }

        // Create payments table if it doesn't exist
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('customer_payment'); // customer_payment, vendor_payment, etc.
                $table->morphs('payable'); // polymorphic relationship
                $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->enum('payment_method', ['cash', 'upi', 'card', 'bank_transfer', 'credit']);
                $table->enum('payment_type', ['order_payment', 'advance_payment', 'refund', 'adjustment']);
                $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
                $table->string('reference_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('payment_date');
                $table->timestamps();
            });
        }

        // Add created_by field to orders if missing
        if (!Schema::hasColumn('orders', 'created_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('delivery_date');
            });
        }

        // Update order status enum to include 'completed'
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'ready', 'completed', 'delivered', 
                'cancelled', 'returned'
            ])->default('pending')->after('order_type');
        });

        // Update payment_status enum to include 'partial'
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', [
                'pending', 'partial', 'paid', 'failed', 'refunded'
            ])->default('pending')->after('payment_method');
        });

        // Update order_type enum to include 'pos'
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_type');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_type', [
                'online', 'on_shop', 'wholesale', 'pos'
            ])->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_gst_rates');
        Schema::dropIfExists('gst_rates');
        Schema::dropIfExists('payments');

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['movement_type', 'reference_type', 'reference_id', 'movement_date']);
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'hsn_code')) {
                $table->dropColumn('hsn_code');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};