<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .po-title {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .po-number {
            font-size: 16px;
            font-weight: bold;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            width: 48%;
        }
        .info-box h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .info-box p {
            margin: 5px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .table .text-right {
            text-align: right;
        }
        .totals {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 5px 10px;
            border: none;
        }
        .total-row {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333 !important;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-confirmed { background-color: #fef3c7; color: #92400e; }
        .status-received { background-color: #dcfce7; color: #166534; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        @media print {
            body { margin: 0; padding: 15px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">🥬 FoodCo Management System</div>
        <div class="po-title">PURCHASE ORDER</div>
        <div class="po-number">{{ $purchaseOrder->po_number }}</div>
        <div style="margin-top: 10px;">
            <span class="status-badge status-{{ $purchaseOrder->status }}">{{ ucfirst($purchaseOrder->status) }}</span>
        </div>
    </div>

    <!-- Order Information -->
    <div class="info-section">
        <div class="info-box">
            <h3>Vendor Information</h3>
            <p><strong>{{ $purchaseOrder->vendor->name }}</strong></p>
            <p>Code: {{ $purchaseOrder->vendor->code }}</p>
            <p>Email: {{ $purchaseOrder->vendor->email }}</p>
            <p>Phone: {{ $purchaseOrder->vendor->phone }}</p>
            <p>Address: {{ $purchaseOrder->vendor->address }}</p>
            @if($purchaseOrder->vendor->gst_number)
                <p>GST: {{ $purchaseOrder->vendor->gst_number }}</p>
            @endif
        </div>
        <div class="info-box">
            <h3>Order Information</h3>
            <p><strong>Order Date:</strong> {{ $purchaseOrder->created_at->format('M d, Y') }}</p>
            <p><strong>Branch:</strong> {{ $purchaseOrder->branch->name }}</p>
            @if($purchaseOrder->branch_request_id && $purchaseOrder->branchRequest)
                <p><strong>Branch Request Ref:</strong> #{{ $purchaseOrder->branchRequest->po_number }} ({{ $purchaseOrder->branchRequest->branch->name ?? 'Branch' }})</p>
            @endif
            <p><strong>Payment Terms:</strong> {{ ucfirst(str_replace('_', ' ', $purchaseOrder->payment_terms)) }}</p>
            <p><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}</p>
            @if($purchaseOrder->actual_delivery_date)
                <p><strong>Actual Delivery:</strong> {{ $purchaseOrder->actual_delivery_date->format('M d, Y') }}</p>
            @endif
            <p><strong>Created By:</strong> {{ $purchaseOrder->user->name }}</p>
            <p><strong>Ship To:</strong> {{ $purchaseOrder->getResolvedDeliveryAddress() }}</p>
        </div>
    </div>

    <!-- Items Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Product</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 15%;">Quantity</th>
                @if($purchaseOrder->isReceived())
                    <th style="width: 15%;">Received</th>
                @endif
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 15%;" class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->purchaseOrderItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small>{{ $item->product->unit }}</small>
                    </td>
                    <td>{{ ucfirst($item->product->category) }}</td>
                    <td>{{ number_format($item->quantity, 2) }} {{ $item->product->unit }}</td>
                    @if($purchaseOrder->isReceived())
                        <td>
                            {{ number_format($item->received_quantity ?? 0, 2) }} {{ $item->product->unit }}
                            @if($item->received_quantity != $item->quantity)
                                <br><small style="color: #ef4444;">
                                    ({{ number_format(abs($item->quantity - ($item->received_quantity ?? 0)), 2) }} {{ $item->received_quantity < $item->quantity ? 'short' : 'excess' }})
                                </small>
                            @endif
                        </td>
                    @endif
                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">₹{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">₹{{ number_format($purchaseOrder->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>GST (18%):</td>
                <td class="text-right">₹{{ number_format($purchaseOrder->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Transport Cost:</td>
                <td class="text-right">₹{{ number_format($purchaseOrder->transport_cost, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Amount:</td>
                <td class="text-right">₹{{ number_format($purchaseOrder->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- Notes -->
    @if($purchaseOrder->notes)
    <div style="margin-top: 30px;">
        <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">Notes:</h3>
        <p style="background-color: #f9fafb; padding: 10px; border-radius: 4px;">{{ $purchaseOrder->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Generated on {{ now()->format('M d, Y H:i') }} | FoodCo Management System</p>
    </div>

    <!-- Print Button (hidden when printing) -->
    <div class="no-print" style="position: fixed; top: 20px; right: 20px;">
        <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            Print / Save as PDF
        </button>
    </div>
</body>
</html>