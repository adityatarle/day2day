<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Local Purchases Report</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #1f2937;
            margin: 0;
            padding: 20px;
            background-color: #f9fafb;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #10b981;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .header .logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .header .date {
            color: #3b82f6;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        th {
            background: linear-gradient(to right, #f3f4f6, #e5e7eb);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4b5563;
        }
        
        td {
            padding: 12px;
            border-top: 1px solid #e5e7eb;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge-primary {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .badge-secondary {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        tfoot th {
            background-color: #f9fafb;
            font-size: 14px;
            padding: 15px 12px;
            border-top: 2px solid #e5e7eb;
        }
        
        .summary {
            margin-top: 40px;
            padding: 25px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 8px;
            border: 1px solid #bfdbfe;
        }
        
        .summary h3 {
            margin: 0 0 20px 0;
            color: #1e40af;
            font-size: 18px;
            font-weight: 600;
        }
        
        .summary table {
            box-shadow: none;
            background-color: transparent;
        }
        
        .summary td {
            padding: 8px 12px;
            border: none;
            background-color: white;
        }
        
        .summary tr {
            margin-bottom: 5px;
        }
        
        .summary tr:first-child td {
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }
        
        .summary tr:last-child td {
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .amount {
            font-weight: 600;
            color: #059669;
        }
        
        .total-row {
            background-color: #ecfdf5;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 20px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        .no-print {
            margin-top: 40px;
            text-align: center;
        }
        
        .no-print button {
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            margin: 0 5px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .btn-print:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .btn-close {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-close:hover {
            background-color: #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <span class="logo">🍃</span>
                Day2Day Fresh
            </h1>
            <p>Local Purchases Report</p>
            <p class="date">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
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
                        'draft' => 0,
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
                            @case('draft')
                                <span class="badge badge-secondary">Draft</span>
                                @break
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
                    <td class="text-right amount">₹{{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <th colspan="7" class="text-right">Total Amount:</th>
                    <th class="text-right amount" style="font-size: 16px;">₹{{ number_format($totalAmount, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="summary">
            <h3>Report Summary</h3>
            <table style="width: 60%">
                <tr>
                    <td style="width: 70%"><strong>Total Purchases:</strong></td>
                    <td style="text-align: right; font-weight: 600;">{{ $localPurchases->count() }}</td>
                </tr>
                <tr>
                    <td><strong>Total Amount:</strong></td>
                    <td style="text-align: right; font-weight: 600; color: #059669;">₹{{ number_format($totalAmount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 10px; font-weight: 600; background-color: #f3f4f6;">Status Breakdown:</td>
                </tr>
                @if($statusCounts['draft'] > 0)
                <tr>
                    <td style="padding-left: 20px;">Draft:</td>
                    <td style="text-align: right;">{{ $statusCounts['draft'] }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding-left: 20px;">Pending:</td>
                    <td style="text-align: right;">{{ $statusCounts['pending'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Approved:</td>
                    <td style="text-align: right;">{{ $statusCounts['approved'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Rejected:</td>
                    <td style="text-align: right;">{{ $statusCounts['rejected'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Completed:</td>
                    <td style="text-align: right;">{{ $statusCounts['completed'] ?? 0 }}</td>
                </tr>
                @if(request()->has('date_from') || request()->has('date_to'))
                <tr>
                    <td colspan="2" style="padding-top: 10px; font-weight: 600; background-color: #f3f4f6;">Date Range:</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">From: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d M Y') : 'Beginning' }}</td>
                    <td style="text-align: right;">To: {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d M Y') : 'Today' }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="footer">
            <p><strong>Day2Day Fresh - Management System</strong></p>
            <p>This is a system generated report. For any queries, please contact the admin.</p>
            <p style="font-size: 10px; color: #9ca3af;">Report ID: LP-{{ now()->format('YmdHis') }}</p>
        </div>

        <div class="no-print">
            <button onclick="window.print()" class="btn-print">
                <span style="margin-right: 8px;">🖨️</span> Print Report
            </button>
            <button onclick="window.close()" class="btn-close">
                Close
            </button>
        </div>
    </div>
</body>
</html>