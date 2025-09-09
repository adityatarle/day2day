# Advanced Stock Management System

## Overview

This comprehensive stock management system handles the complete lifecycle of stock transfers from admin/warehouse to sub-branches, including discrepancy management, financial tracking, and automated reconciliation processes.

## Key Features

### 1. Admin Stock Management
- **Stock Transfer Creation**: Create transfers from warehouse to branches
- **Dispatch Management**: Track and manage dispatch processes
- **Query Resolution**: Handle and resolve branch queries about stock discrepancies
- **Financial Tracking**: Monitor transport costs and financial impacts
- **Performance Analytics**: Generate detailed reports and performance metrics

### 2. Branch Stock Receipt Management
- **Receipt Confirmation**: Confirm receipt of stock transfers with actual quantities
- **Query Raising**: Report discrepancies, damages, or quality issues
- **Stock Reconciliation**: Perform weight adjustments and physical stock verification
- **Real-time Tracking**: Track transfer status and expected deliveries

### 3. Query & Issue Management System
- **Automated Query Creation**: System automatically creates queries for significant discrepancies
- **Priority-based Handling**: Queries are prioritized (low, medium, high, critical)
- **Evidence Upload**: Upload photos and documents as evidence
- **Resolution Tracking**: Track query resolution time and outcomes
- **Escalation Process**: Escalate unresolved queries to higher authorities

### 4. Financial Impact Tracking
- **Loss Calculation**: Automatically calculate financial impact of discrepancies
- **Recovery Management**: Track recovered amounts and recovery processes
- **Transport Cost Allocation**: Detailed tracking of transport expenses
- **Cost Analysis**: Analyze cost per transfer and efficiency metrics

## Database Schema

### Core Tables

#### `stock_transfers`
Main table for tracking stock transfers from admin to branches.

**Key Fields:**
- `transfer_number`: Unique identifier (auto-generated)
- `from_branch_id`: Source branch (null for main warehouse)
- `to_branch_id`: Destination branch
- `status`: pending, in_transit, delivered, confirmed, cancelled
- `total_value`: Total value of transfer
- `transport_cost`: Transport expenses
- `dispatch_date`, `expected_delivery`, `delivered_date`, `confirmed_date`

#### `stock_transfer_items`
Individual items within each transfer.

**Key Fields:**
- `quantity_sent`: Quantity dispatched by admin
- `quantity_received`: Quantity received by branch (filled later)
- `unit_price`: Price per unit for financial calculations
- `expiry_date`: For perishable items

#### `stock_transfer_queries`
Queries/issues raised about transfers.

**Key Fields:**
- `query_number`: Unique identifier (auto-generated)
- `query_type`: weight_difference, quantity_shortage, quality_issue, etc.
- `priority`: low, medium, high, critical
- `status`: open, in_progress, resolved, closed, escalated
- `financial_impact`: Calculated financial impact

#### `stock_financial_impacts`
Financial impact records for losses and recoveries.

**Key Fields:**
- `impact_type`: loss_damaged, loss_expired, transport_cost, etc.
- `amount`: Financial impact amount
- `is_recoverable`: Whether the loss can be recovered
- `recovered_amount`: Amount actually recovered

## API Endpoints

### Admin Endpoints

#### Stock Transfer Management
```
GET    /api/admin/stock-transfers           # List all transfers
POST   /api/admin/stock-transfers           # Create new transfer
GET    /api/admin/stock-transfers/{id}      # Get transfer details
POST   /api/admin/stock-transfers/{id}/dispatch  # Dispatch transfer
POST   /api/admin/stock-transfers/{id}/cancel    # Cancel transfer
```

#### Query Management
```
GET    /api/admin/stock-transfers/queries/all        # List all queries
GET    /api/admin/stock-transfers/queries/{id}       # Get query details
POST   /api/admin/stock-transfers/queries/{id}/assign    # Assign query to admin
POST   /api/admin/stock-transfers/queries/{id}/respond   # Add response to query
POST   /api/admin/stock-transfers/queries/{id}/resolve   # Resolve query
```

### Branch Manager Endpoints

#### Stock Receipt Management
```
GET    /api/branch/stock-receipts                    # List incoming transfers
GET    /api/branch/stock-receipts/{id}               # Get transfer details
POST   /api/branch/stock-receipts/{id}/confirm-receipt   # Confirm receipt
```

#### Query Management
```
GET    /api/branch/stock-receipts/queries/all        # List branch queries
POST   /api/branch/stock-receipts/{id}/queries       # Create new query
POST   /api/branch/stock-receipts/queries/{id}/respond   # Add response
POST   /api/branch/stock-receipts/queries/{id}/escalate  # Escalate query
```

### Financial & Analytics Endpoints
```
GET    /api/stock-management/financial-impacts       # Financial impact reports
GET    /api/stock-management/transport-expenses      # Transport expense reports
GET    /api/stock-management/alerts                  # Stock alerts
GET    /api/stock-management/statistics              # Overall statistics
```

## Usage Workflow

### 1. Admin Creates Stock Transfer

```json
POST /api/admin/stock-transfers
{
  "to_branch_id": 1,
  "expected_delivery": "2025-01-20",
  "transport_vendor": "ABC Transport",
  "vehicle_number": "MH12AB1234",
  "driver_name": "John Doe",
  "driver_phone": "9876543210",
  "transport_cost": 500.00,
  "items": [
    {
      "product_id": 1,
      "quantity_sent": 100.5,
      "unit_price": 50.00,
      "unit_of_measurement": "kg",
      "expiry_date": "2025-02-15"
    }
  ]
}
```

### 2. Admin Dispatches Transfer

```json
POST /api/admin/stock-transfers/ST202501001/dispatch
{
  "dispatch_notes": "Dispatched via ABC Transport",
  "vehicle_number": "MH12AB1234"
}
```

### 3. Branch Manager Confirms Receipt

```json
POST /api/branch/stock-receipts/ST202501001/confirm-receipt
{
  "items": [
    {
      "item_id": 1,
      "quantity_received": 98.2,
      "notes": "2.3kg shortage observed"
    }
  ]
}
```

### 4. Branch Manager Raises Query (if discrepancy)

```json
POST /api/branch/stock-receipts/ST202501001/queries
{
  "query_type": "quantity_shortage",
  "priority": "medium",
  "title": "Quantity Shortage in Apples",
  "description": "Expected 100.5kg but received only 98.2kg",
  "expected_quantity": 100.5,
  "actual_quantity": 98.2
}
```

## Key Service Classes

### StockTransferService
Handles all stock transfer operations:
- `createStockTransfer()`: Create new transfers
- `dispatchTransfer()`: Mark as dispatched
- `confirmReceipt()`: Confirm receipt by branch
- `cancelTransfer()`: Cancel transfers
- `getTransferStatistics()`: Generate statistics

### StockQueryService
Manages query/issue tracking:
- `createQuery()`: Create new queries
- `assignQuery()`: Assign to admin
- `resolveQuery()`: Resolve queries
- `escalateQuery()`: Escalate unresolved queries
- `getQueryStatistics()`: Generate query statistics

### StockReconciliationService
Handles stock reconciliation:
- `createReconciliation()`: Create reconciliation records
- `approveReconciliation()`: Approve reconciliations
- `generateAccuracyReport()`: Generate accuracy reports

## Model Relationships

```php
// StockTransfer relationships
StockTransfer::class
├── belongsTo(Branch::class, 'to_branch_id')
├── belongsTo(Branch::class, 'from_branch_id')
├── belongsTo(User::class, 'initiated_by')
├── hasMany(StockTransferItem::class)
├── hasMany(StockTransferQuery::class)
├── hasMany(TransportExpense::class)
└── morphMany(StockFinancialImpact::class)

// StockTransferQuery relationships
StockTransferQuery::class
├── belongsTo(StockTransfer::class)
├── belongsTo(StockTransferItem::class)
├── belongsTo(User::class, 'raised_by')
├── belongsTo(User::class, 'assigned_to')
├── hasMany(StockQueryResponse::class)
└── morphMany(StockFinancialImpact::class)
```

## Financial Tracking Features

### Automatic Impact Calculation
- System automatically calculates financial impact for discrepancies
- Tracks recoverable vs non-recoverable losses
- Maintains detailed expense breakdown

### Transport Cost Management
- Detailed tracking of transport expenses by category
- Cost allocation per transfer
- Performance metrics for transport efficiency

### Recovery Tracking
- Track recovery efforts for losses
- Monitor recovery success rates
- Generate recovery reports

## Alert System

### Automated Alerts
- **Transfer Delays**: Alerts for overdue transfers
- **Critical Queries**: High-priority unresolved queries
- **Financial Impact**: High-value loss alerts
- **Quality Issues**: Product quality concerns

### Alert Types
- `low_stock`: Inventory running low
- `expiry_warning`: Products nearing expiry
- `transfer_delay`: Overdue transfers
- `query_pending`: Unresolved queries
- `reconciliation_required`: Pending reconciliations
- `financial_impact`: Significant financial losses

## Reporting & Analytics

### Transfer Performance Reports
- On-time delivery rates
- Average delivery times
- Cancellation rates
- Cost per transfer analysis

### Query Analytics
- Query resolution times
- Query types and frequencies
- Escalation rates
- Financial impact trends

### Financial Reports
- Total losses by category
- Recovery rates and amounts
- Transport cost analysis
- Cost efficiency metrics

## Security & Access Control

### Role-based Access
- **Super Admin**: Full system access
- **Admin**: Stock transfer management and query resolution
- **Branch Manager**: Receipt confirmation and query raising
- **Delivery Personnel**: Status updates only

### Data Protection
- Sensitive financial data encryption
- Audit trails for all operations
- User activity logging
- Secure file upload for evidence

## Integration Points

### Mobile App Integration
- Real-time transfer status updates
- Photo upload for evidence
- Delivery confirmation
- Push notifications for alerts

### ERP Integration
- Automatic stock level updates
- Financial system integration
- Vendor management system
- Accounting system synchronization

## Best Practices

### For Admins
1. Always verify stock availability before creating transfers
2. Set realistic delivery expectations
3. Monitor transport costs and optimize routes
4. Respond to queries promptly to maintain branch relationships

### For Branch Managers
1. Verify all received items immediately upon delivery
2. Document discrepancies with photographic evidence
3. Raise queries promptly for better resolution
4. Perform regular stock reconciliations

### For System Maintenance
1. Regular cleanup of resolved queries and closed transfers
2. Archive old financial impact records
3. Monitor alert system performance
4. Regular backup of critical data

## Troubleshooting

### Common Issues

#### Transfer Status Not Updating
- Check user permissions
- Verify branch assignments
- Confirm database connectivity

#### Queries Not Resolving
- Check admin assignment
- Verify notification system
- Review escalation rules

#### Financial Calculations Incorrect
- Verify product pricing
- Check unit conversions
- Review calculation formulas

### Performance Optimization
- Index frequently queried fields
- Archive old records regularly
- Optimize database queries
- Use caching for frequently accessed data

## Future Enhancements

### Planned Features
1. **AI-powered Predictive Analytics**: Predict potential issues before they occur
2. **Blockchain Integration**: Immutable record keeping for high-value transfers
3. **IoT Integration**: Real-time temperature and condition monitoring
4. **Advanced Route Optimization**: AI-powered delivery route planning
5. **Automated Reconciliation**: Machine learning for automatic discrepancy detection

### API Versioning
- Current version: v1
- Backward compatibility maintained
- Deprecation notices for old endpoints
- Migration guides for version updates

This comprehensive stock management system provides end-to-end visibility and control over stock transfers, ensuring accountability, reducing losses, and improving operational efficiency across the entire supply chain.