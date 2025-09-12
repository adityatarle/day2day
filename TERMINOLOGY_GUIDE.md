# Purchase Order Terminology Guide

This document explains the terminology used in our inventory management system, following Tally conventions.

## Overview

Our system uses two distinct terms to clearly differentiate between outgoing and incoming orders:

### Purchase Order
- **Definition**: Orders sent FROM your main branch TO vendors (outgoing orders)
- **Purpose**: Request materials/goods from suppliers
- **Status Flow**: `draft` → `sent` → `confirmed` → `received`
- **Database Field**: `order_type = 'purchase_order'`
- **Example**: "PO-2025-0001 sent to ABC Supplies for 100 units of Product X"

### Received Order
- **Definition**: Materials/goods received FROM vendors (incoming materials)
- **Purpose**: Track actual receipt of materials that were previously ordered
- **Trigger**: When a Purchase Order status becomes `received`
- **Database Field**: `order_type = 'received_order'` and `is_received_order = true`
- **Example**: "PO-2025-0001 converted to Received Order - 95 units received from ABC Supplies"

## Implementation Details

### Database Structure
The `purchase_orders` table includes these terminology fields:
- `order_type`: ENUM(`purchase_order`, `received_order`) - Explicit type classification
- `is_received_order`: BOOLEAN - Flag indicating materials have been received
- `terminology_notes`: TEXT - Additional notes about order transitions

### Model Methods
The `PurchaseOrder` model provides these methods:
- `isPurchaseOrder()`: Check if this is an outgoing Purchase Order
- `isReceivedOrderType()`: Check if this is a Received Order
- `getTerminologyDisplayText()`: Get appropriate display text
- `markAsReceived()`: Convert Purchase Order to Received Order

### Scopes
- `purchaseOrdersOnly()`: Filter only outgoing Purchase Orders
- `receivedOrdersOnly()`: Filter only Received Orders

## User Interface

### Status Display
- **Purchase Orders**: Show as "Purchase Order (Outgoing to Vendor)"
- **Received Orders**: Show as "Received Order (Materials Received)"

### Action Buttons
- **"Receive Materials"**: Converts Purchase Order to Received Order
- **Status Badges**: Color-coded based on order type and status

## Benefits

1. **Clarity**: Clear distinction between outgoing requests and incoming materials
2. **Tally Compliance**: Follows established Tally terminology conventions
3. **Audit Trail**: Complete tracking from order placement to material receipt
4. **Reporting**: Separate reporting for Purchase Orders vs Received Orders
5. **Inventory Management**: Accurate stock updates when materials are received

## Migration

To apply the terminology changes to existing data:

1. Run the migration: `php artisan migrate`
2. Existing orders will default to `purchase_order` type
3. Orders with `status = 'received'` should be manually updated to `received_order` type if needed

## Support

For questions about the terminology system, refer to:
- Model documentation in `app/Models/PurchaseOrder.php`
- Controller comments in `app/Http/Controllers/Web/PurchaseOrderController.php`
- View implementations in `resources/views/purchase-orders/`