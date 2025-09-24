-- Quick fix for stock_movements table missing columns
-- Run this SQL directly in your database to fix the immediate issue

-- Add missing columns to stock_movements table
ALTER TABLE `stock_movements` 
ADD COLUMN IF NOT EXISTS `reference_type` VARCHAR(255) NULL AFTER `user_id`,
ADD COLUMN IF NOT EXISTS `reference_id` BIGINT UNSIGNED NULL AFTER `reference_type`,
ADD COLUMN IF NOT EXISTS `movement_date` TIMESTAMP NULL AFTER `reference_id`;

-- Add index for better performance
ALTER TABLE `stock_movements` 
ADD INDEX IF NOT EXISTS `stock_movements_reference_type_reference_id_index` (`reference_type`, `reference_id`);

-- Update the type enum to include adjustment_positive and adjustment_negative
ALTER TABLE `stock_movements` 
MODIFY COLUMN `type` ENUM(
    'purchase', 'sale', 'adjustment', 'adjustment_positive', 'adjustment_negative', 
    'loss', 'return', 'transfer_in', 'transfer_out', 'wastage', 'complimentary'
) NOT NULL;

-- Check if columns were added successfully
SHOW COLUMNS FROM `stock_movements`;