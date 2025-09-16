# Stock Movements Database Schema Fix

## Problem
The application is throwing a database error: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'reference_type' in 'field list'` when trying to create stock movements during purchase order receiving.

## Root Cause
The `stock_movements` table is missing the `reference_type` and `reference_id` columns that were supposed to be added by the migration `2025_01_17_000001_enhance_stock_movements_and_add_missing_fields.php`, but this migration hasn't been run yet.

## Solution

### 1. Database Schema Fix
Run the SQL script `fix_stock_movements_schema.sql` to add the missing columns:

```sql
-- Add reference_type column if it doesn't exist
ALTER TABLE stock_movements 
ADD COLUMN IF NOT EXISTS reference_type VARCHAR(255) NULL 
AFTER user_id;

-- Add reference_id column if it doesn't exist  
ALTER TABLE stock_movements 
ADD COLUMN IF NOT EXISTS reference_id BIGINT UNSIGNED NULL 
AFTER reference_type;

-- Add movement_type column if it doesn't exist
ALTER TABLE stock_movements 
ADD COLUMN IF NOT EXISTS movement_type VARCHAR(255) NULL 
AFTER type;

-- Add movement_date column if it doesn't exist
ALTER TABLE stock_movements 
ADD COLUMN IF NOT EXISTS movement_date TIMESTAMP NULL 
AFTER reference_id;

-- Update the type enum to include more movement types
ALTER TABLE stock_movements 
MODIFY COLUMN type ENUM(
    'purchase', 'sale', 'adjustment', 'loss', 'return', 
    'transfer_in', 'transfer_out', 'wastage', 'complimentary'
) NOT NULL;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_stock_movements_reference ON stock_movements(reference_type, reference_id);
CREATE INDEX IF NOT EXISTS idx_stock_movements_movement_date ON stock_movements(movement_date);
```

### 2. Code Fix
The `PurchaseOrderController.php` has been updated to include all required fields when creating stock movements:

- Added `unit_price` field (using purchase order item's unit price)
- Added `user_id` field (current authenticated user)
- Added `movement_date` field (current timestamp)

### 3. Alternative: Run Laravel Migration
If you have access to PHP/Laravel commands, you can run:

```bash
php artisan migrate
```

This will run the pending migration `2025_01_17_000001_enhance_stock_movements_and_add_missing_fields.php` which should add the missing columns.

## Files Modified
1. `app/Http/Controllers/Web/PurchaseOrderController.php` - Updated StockMovement::create() call
2. `fix_stock_movements_schema.sql` - SQL script to fix database schema

## Testing
After applying the fix:
1. Try to receive a purchase order again
2. The stock movement should be created successfully
3. Check that the `stock_movements` table has the new columns populated correctly

## Verification
You can verify the fix by checking the database schema:

```sql
DESCRIBE stock_movements;
```

The table should now include:
- `reference_type` (VARCHAR)
- `reference_id` (BIGINT UNSIGNED)
- `movement_type` (VARCHAR)
- `movement_date` (TIMESTAMP)