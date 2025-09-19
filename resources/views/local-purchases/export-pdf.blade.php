<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Local Purchases Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-primary {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Day2Day Fruits and Vegetables</h1>
        <p>Local Purchases Report</p>
        <p>Generated on: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Purchase #</th>
                <th style="width: 10%">Date</th>
                <th style="width: 15%">Branch</th>
                <th style="width: 15%">Manager</th>
                <th style="width: 15%">Vendor</th>
                <th style="width: 10%">Payment</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmount = 0;
                $statusCounts = [
                    'pending' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'completed' => 0
                ];
            @endphp
            
            @foreach($localPurchases as $purchase)
            @php
                $totalAmount += $purchase->total_amount;
                $statusCounts[$purchase->status] = ($statusCounts[$purchase->status] ?? 0) + 1;
            @endphp
            <tr>
                <td>{{ $purchase->purchase_number }}</td>
                <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                <td>{{ $purchase->branch->name }}</td>
                <td>{{ $purchase->manager->name }}</td>
                <td>{{ $purchase->vendor_display_name }}</td>
                <td>{{ ucfirst($purchase->payment_method) }}</td>
                <td class="text-center">
                    @switch($purchase->status)
                        @case('pending')
                            <span class="badge badge-warning">Pending</span>
                            @break
                        @case('approved')
                            <span class="badge badge-success">Approved</span>
                            @break
                        @case('rejected')
                            <span class="badge badge-danger">Rejected</span>
                            @break
                        @case('completed')
                            <span class="badge badge-primary">Completed</span>
                            @break
                    @endswitch
                </td>
                <td class="text-right">₹{{ number_format($purchase->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-right">Total Amount:</th>
                <th class="text-right">₹{{ number_format($totalAmount, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <h3>Summary</h3>
        <table style="width: 50%">
            <tr>
                <td><strong>Total Purchases:</strong></td>
                <td>{{ $localPurchases->count() }}</td>
            </tr>
            <tr>
                <td><strong>Total Amount:</strong></td>
                <td>₹{{ number_format($totalAmount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Pending:</strong></td>
                <td>{{ $statusCounts['pending'] ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>Approved:</strong></td>
                <td>{{ $statusCounts['approved'] ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>Rejected:</strong></td>
                <td>{{ $statusCounts['rejected'] ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>Completed:</strong></td>
                <td>{{ $statusCounts['completed'] ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This is a system generated report. For any queries, please contact the admin.</p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
            Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
</body>
</html>