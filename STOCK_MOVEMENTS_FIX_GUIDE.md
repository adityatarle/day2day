# Stock Movements Table Fix Guide

## Issue Description
The application is throwing a database error when trying to receive purchase orders:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'reference_type' in 'field list'
```

This error occurs because the `stock_movements` table is missing three columns:
- `reference_type` (string)
- `reference_id` (bigint unsigned)
- `movement_date` (timestamp)

## Root Cause
The original `stock_movements` table creation (in `0001_01_01_000003_create_products_table.php`) doesn't include these columns, but the application code in `PurchaseOrderController@receive` is trying to insert data into these columns when recording stock movements.

There's an enhancement migration (`2025_01_17_000001_enhance_stock_movements_and_add_missing_fields.php`) that adds these columns, but it appears it hasn't been run yet.

## Immediate Fix (Quick Solution)

### Option 1: Run the SQL Script Directly
Execute the provided SQL script in your MySQL database:

```bash
mysql -u root -p day2day < fix_stock_movements_immediate.sql
```

Or manually run these SQL commands:
```sql
ALTER TABLE `stock_movements` 
ADD COLUMN IF NOT EXISTS `reference_type` VARCHAR(255) NULL AFTER `user_id`,
ADD COLUMN IF NOT EXISTS `reference_id` BIGINT UNSIGNED NULL AFTER `reference_type`,
ADD COLUMN IF NOT EXISTS `movement_date` TIMESTAMP NULL AFTER `reference_id`;

ALTER TABLE `stock_movements` 
ADD INDEX IF NOT EXISTS `stock_movements_reference_type_reference_id_index` (`reference_type`, `reference_id`);
```

### Option 2: Run Laravel Migrations
If you have PHP/Artisan available, run:
```bash
php artisan migrate
```

This will run all pending migrations including the enhancement migration that adds these columns.

## Permanent Solution

1. **Ensure all migrations are run**: The system has several migrations that enhance the database schema. Make sure all migrations are executed.

2. **Migration created**: I've created a new migration file `2025_09_16_fix_stock_movements_columns.php` that safely adds these columns if they don't exist.

3. **Code consistency**: The `PurchaseOrderController` correctly uses these columns to track which purchase order a stock movement relates to.

## Verification
After applying the fix, verify the columns exist:
```sql
SHOW COLUMNS FROM stock_movements;
```

You should see:
- `reference_type` - stores the type of reference (e.g., 'purchase_order', 'sales_order')
- `reference_id` - stores the ID of the referenced record
- `movement_date` - stores when the stock movement occurred

## Prevention
1. Always run `php artisan migrate` after pulling code updates
2. Check migration status with `php artisan migrate:status`
3. Use database migrations for all schema changes
4. Keep your local database schema in sync with production

## Related Files
- Original migration: `/database/migrations/0001_01_01_000003_create_products_table.php`
- Enhancement migration: `/database/migrations/2025_01_17_000001_enhance_stock_movements_and_add_missing_fields.php`
- Fix migration: `/database/migrations/2025_09_16_fix_stock_movements_columns.php`
- Controller using these columns: `/app/Http/Controllers/Web/PurchaseOrderController.php`