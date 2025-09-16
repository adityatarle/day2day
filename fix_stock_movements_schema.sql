-- Fix stock_movements table schema by adding missing columns
-- This script adds the reference_type and reference_id columns that are missing

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
-- First, we need to modify the existing enum
ALTER TABLE stock_movements 
MODIFY COLUMN type ENUM(
    'purchase', 'sale', 'adjustment', 'loss', 'return', 
    'transfer_in', 'transfer_out', 'wastage', 'complimentary'
) NOT NULL;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_stock_movements_reference ON stock_movements(reference_type, reference_id);
CREATE INDEX IF NOT EXISTS idx_stock_movements_movement_date ON stock_movements(movement_date);