-- Fix purchase orders to show up on Create Purchase Entry page
-- This script updates existing purchase orders to have the correct status and order_type

-- Update existing purchase orders to have the correct order_type and status
UPDATE purchase_orders 
SET 
    order_type = 'branch_request',
    status = CASE 
        WHEN id = 1 THEN 'approved'
        WHEN id = 2 THEN 'fulfilled'
        ELSE 'approved'
    END,
    received_at = NULL
WHERE id IN (1, 2);

-- Insert additional purchase order for testing
INSERT INTO purchase_orders (
    po_number, vendor_id, branch_id, user_id, status, order_type, 
    payment_terms, subtotal, tax_amount, transport_cost, total_amount, 
    notes, expected_delivery_date, actual_delivery_date, received_at, 
    created_at, updated_at
) VALUES (
    'PO003', 1, 1, 2, 'approved', 'branch_request', 
    '10_days', 2500.00, 0.00, 100.00, 2600.00, 
    'Organic vegetables order', DATE_ADD(NOW(), INTERVAL 3 DAY), NULL, NULL, 
    DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)
);

-- Insert purchase order items for the existing orders
INSERT INTO purchase_order_items (
    purchase_order_id, product_id, quantity, unit_price, total_price, 
    fulfilled_quantity, created_at, updated_at
) VALUES 
-- PO001 items
(1, 1, 20.0, 120.00, 2400.00, 20.0, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 3, 15.0, 30.00, 450.00, 15.0, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 5, 25.0, 35.00, 875.00, 25.0, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
-- PO002 items  
(2, 2, 30.0, 40.00, 1200.00, 30.0, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 4, 20.0, 80.00, 1600.00, 20.0, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 1, 10.0, 120.00, 1200.00, 10.0, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
-- PO003 items
(3, 3, 20.0, 30.00, 600.00, 20.0, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 5, 15.0, 35.00, 525.00, 15.0, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 2, 25.0, 40.00, 1000.00, 25.0, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Verify the data
SELECT 
    po.id,
    po.po_number,
    po.status,
    po.order_type,
    po.branch_id,
    po.received_at,
    v.name as vendor_name,
    COUNT(poi.id) as items_count
FROM purchase_orders po
LEFT JOIN vendors v ON po.vendor_id = v.id
LEFT JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
WHERE po.branch_id = 1 
    AND po.order_type = 'branch_request'
    AND po.status IN ('approved', 'fulfilled')
    AND po.received_at IS NULL
GROUP BY po.id, po.po_number, po.status, po.order_type, po.branch_id, po.received_at, v.name
ORDER BY po.created_at DESC;