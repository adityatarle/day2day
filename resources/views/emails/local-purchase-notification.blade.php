<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Purchase Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        .details {
            background-color: white;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .details h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .details dl {
            margin: 0;
        }
        .details dt {
            font-weight: bold;
            color: #555;
            float: left;
            width: 40%;
            padding: 5px 0;
        }
        .details dd {
            margin-left: 45%;
            padding: 5px 0;
        }
        .items-table {
            width: 100%;
            background-color: white;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #34495e;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Day2Day Fruits and Vegetables</h1>
        <h2>Local Purchase Notification</h2>
    </div>

    <div class="content">
        @switch($notification->type)
            @case('created')
                <div class="alert alert-info">
                    <strong>New Local Purchase Created!</strong><br>
                    A new local purchase has been created by {{ $localPurchase->manager->name }} at {{ $localPurchase->branch->name }} branch.
                </div>
                @break
            @case('approved')
                <div class="alert alert-success">
                    <strong>Local Purchase Approved!</strong><br>
                    Your local purchase has been approved by {{ $localPurchase->approvedBy->name }}.
                </div>
                @break
            @case('rejected')
                <div class="alert alert-danger">
                    <strong>Local Purchase Rejected!</strong><br>
                    Your local purchase has been rejected by {{ $localPurchase->approvedBy->name }}.<br>
                    <strong>Reason:</strong> {{ $localPurchase->rejection_reason ?: 'No reason provided' }}
                </div>
                @break
            @case('updated')
                <div class="alert alert-warning">
                    <strong>Local Purchase Updated!</strong><br>
                    The local purchase has been updated by {{ $localPurchase->manager->name }}.
                </div>
                @break
        @endswitch

        <div class="details">
            <h3>Purchase Details</h3>
            <dl>
                <dt>Purchase Number:</dt>
                <dd>{{ $localPurchase->purchase_number }}</dd>

                <dt>Branch:</dt>
                <dd>{{ $localPurchase->branch->name }}</dd>

                <dt>Manager:</dt>
                <dd>{{ $localPurchase->manager->name }}</dd>

                <dt>Purchase Date:</dt>
                <dd>{{ $localPurchase->purchase_date->format('d M Y') }}</dd>

                <dt>Vendor:</dt>
                <dd>{{ $localPurchase->vendor_display_name }}</dd>

                <dt>Payment Method:</dt>
                <dd>{{ ucfirst($localPurchase->payment_method) }}</dd>

                <dt>Total Amount:</dt>
                <dd style="font-weight: bold; color: #27ae60;">₹{{ number_format($localPurchase->total_amount, 2) }}</dd>

                <dt>Status:</dt>
                <dd>
                    @switch($localPurchase->status)
                        @case('pending')
                            <span style="color: #f39c12;">Pending Approval</span>
                            @break
                        @case('approved')
                            <span style="color: #27ae60;">Approved</span>
                            @break
                        @case('rejected')
                            <span style="color: #e74c3c;">Rejected</span>
                            @break
                        @case('completed')
                            <span style="color: #3498db;">Completed</span>
                            @break
                    @endswitch
                </dd>
            </dl>
        </div>

        <div class="details">
            <h3>Purchase Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($localPurchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                        <td>₹{{ number_format($item->unit_price, 2) }}</td>
                        <td>₹{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($localPurchase->notes)
        <div class="details">
            <h3>Notes</h3>
            <p>{{ $localPurchase->notes }}</p>
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ url('/') }}" class="button">View in System</a>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from Day2Day Fruits and Vegetables system.</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>