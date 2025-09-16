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
        // First, update any existing records that might have invalid status values
        DB::table('purchase_orders')
            ->where('status', 'approved')
            ->update(['status' => 'sent']);
            
        DB::table('purchase_orders')
            ->where('status', 'fulfilled')
            ->update(['status' => 'received']);

        // For SQLite, we need to recreate the table with the new enum values
        // Since SQLite doesn't support ALTER COLUMN for enum changes
        if (DB::getDriverName() === 'sqlite') {
            // Create a new table with the correct structure
            DB::statement("CREATE TABLE purchase_orders_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                po_number VARCHAR(255) UNIQUE NOT NULL,
                vendor_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                branch_request_id INTEGER NULL,
                user_id INTEGER NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'approved', 'confirmed', 'fulfilled', 'received', 'cancelled')),
                order_type VARCHAR(50) NOT NULL DEFAULT 'purchase_order',
                delivery_address_type VARCHAR(20) DEFAULT 'admin_main',
                ship_to_branch_id INTEGER NULL,
                delivery_address TEXT NULL,
                is_received_order BOOLEAN DEFAULT 0,
                payment_terms VARCHAR(20) NOT NULL DEFAULT 'immediate',
                subtotal DECIMAL(10,2) NOT NULL,
                tax_amount DECIMAL(10,2) DEFAULT 0,
                transport_cost DECIMAL(10,2) DEFAULT 0,
                total_amount DECIMAL(10,2) NOT NULL,
                notes TEXT NULL,
                terminology_notes TEXT NULL,
                expected_delivery_date DATE NOT NULL,
                actual_delivery_date DATE NULL,
                priority VARCHAR(10) DEFAULT 'medium',
                approved_by INTEGER NULL,
                approved_at TIMESTAMP NULL,
                fulfilled_by INTEGER NULL,
                fulfilled_at TIMESTAMP NULL,
                received_by INTEGER NULL,
                received_at TIMESTAMP NULL,
                cancelled_by INTEGER NULL,
                cancelled_at TIMESTAMP NULL,
                delivery_notes TEXT NULL,
                delivery_person VARCHAR(255) NULL,
                delivery_vehicle VARCHAR(255) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
                FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
                FOREIGN KEY (branch_request_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (ship_to_branch_id) REFERENCES branches(id) ON DELETE SET NULL,
                FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (fulfilled_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL
            )");

            // Copy data from old table to new table
            DB::statement("INSERT INTO purchase_orders_new SELECT * FROM purchase_orders");

            // Drop old table and rename new table
            DB::statement("DROP TABLE purchase_orders");
            DB::statement("ALTER TABLE purchase_orders_new RENAME TO purchase_orders");
        } else {
            // For MySQL, use the original MODIFY COLUMN approach
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft', 'sent', 'approved', 'confirmed', 'fulfilled', 'received', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, recreate table with original enum values
            DB::statement("CREATE TABLE purchase_orders_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                po_number VARCHAR(255) UNIQUE NOT NULL,
                vendor_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                branch_request_id INTEGER NULL,
                user_id INTEGER NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'confirmed', 'received', 'cancelled')),
                order_type VARCHAR(50) NOT NULL DEFAULT 'purchase_order',
                delivery_address_type VARCHAR(20) DEFAULT 'admin_main',
                ship_to_branch_id INTEGER NULL,
                delivery_address TEXT NULL,
                is_received_order BOOLEAN DEFAULT 0,
                payment_terms VARCHAR(20) NOT NULL DEFAULT 'immediate',
                subtotal DECIMAL(10,2) NOT NULL,
                tax_amount DECIMAL(10,2) DEFAULT 0,
                transport_cost DECIMAL(10,2) DEFAULT 0,
                total_amount DECIMAL(10,2) NOT NULL,
                notes TEXT NULL,
                terminology_notes TEXT NULL,
                expected_delivery_date DATE NOT NULL,
                actual_delivery_date DATE NULL,
                priority VARCHAR(10) DEFAULT 'medium',
                approved_by INTEGER NULL,
                approved_at TIMESTAMP NULL,
                fulfilled_by INTEGER NULL,
                fulfilled_at TIMESTAMP NULL,
                received_by INTEGER NULL,
                received_at TIMESTAMP NULL,
                cancelled_by INTEGER NULL,
                cancelled_at TIMESTAMP NULL,
                delivery_notes TEXT NULL,
                delivery_person VARCHAR(255) NULL,
                delivery_vehicle VARCHAR(255) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
                FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
                FOREIGN KEY (branch_request_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (ship_to_branch_id) REFERENCES branches(id) ON DELETE SET NULL,
                FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (fulfilled_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL
            )");

            // Copy data from current table to old table (filtering out invalid statuses)
            DB::statement("INSERT INTO purchase_orders_old SELECT * FROM purchase_orders WHERE status IN ('draft', 'sent', 'confirmed', 'received', 'cancelled')");

            // Drop current table and rename old table
            DB::statement("DROP TABLE purchase_orders");
            DB::statement("ALTER TABLE purchase_orders_old RENAME TO purchase_orders");
        } else {
            // For MySQL, use the original MODIFY COLUMN approach
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft', 'sent', 'confirmed', 'received', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};