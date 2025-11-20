<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, {{ $notificationType->color }} 0%, {{ $notificationType->color }}dd 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        .content {
            padding: 32px 24px;
        }
        .content h2 {
            color: #1f2937;
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 16px 0;
        }
        .content p {
            color: #6b7280;
            font-size: 16px;
            margin: 0 0 16px 0;
        }
        .content .highlight {
            background-color: #f3f4f6;
            border-left: 4px solid {{ $notificationType->color }};
            padding: 16px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            color: #9ca3af;
            font-size: 14px;
            margin: 0;
        }
        .footer a {
            color: {{ $notificationType->color }};
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: {{ $notificationType->color }};
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin: 16px 0;
        }
        .button:hover {
            opacity: 0.9;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .data-table td {
            color: #6b7280;
        }
        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .priority-high {
            background-color: #fef2f2;
            color: #dc2626;
        }
        .priority-medium {
            background-color: #fef3c7;
            color: #d97706;
        }
        .priority-low {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        .priority-critical {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="icon">
                <i class="{{ $notificationType->icon }}"></i>
            </div>
            <h1>{{ $subject }}</h1>
            <span class="priority-badge priority-{{ strtolower($notificationType->priority_display_name) }}">
                {{ $notificationType->priority_display_name }} Priority
            </span>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Notification Details</h2>
            <p>{{ $body }}</p>

            @if(isset($data) && !empty($data))
                <div class="highlight">
                    <h3 style="margin: 0 0 12px 0; color: #1f2937;">Additional Information</h3>
                    
                    @if(isset($data['user']))
                        <p><strong>User:</strong> {{ $data['user']['name'] }} ({{ $data['user']['email'] }})</p>
                    @endif
                    
                    @if(isset($data['order']))
                        <p><strong>Order:</strong> #{{ $data['order']['order_number'] ?? 'N/A' }}</p>
                        <p><strong>Amount:</strong> ₹{{ number_format($data['order']['total_amount'] ?? 0, 2) }}</p>
                    @endif
                    
                    @if(isset($data['product']))
                        <p><strong>Product:</strong> {{ $data['product']['name'] ?? 'N/A' }}</p>
                        <p><strong>Stock:</strong> {{ $data['product']['current_stock'] ?? 'N/A' }} units</p>
                    @endif
                    
                    @if(isset($data['branch']))
                        <p><strong>Branch:</strong> {{ $data['branch']['name'] ?? 'N/A' }}</p>
                    @endif
                    
                    @if(isset($data['vendor']))
                        <p><strong>Vendor:</strong> {{ $data['vendor']['name'] ?? 'N/A' }}</p>
                    @endif
                    
                    @if(isset($data['payment']))
                        <p><strong>Payment Amount:</strong> ₹{{ number_format($data['payment']['amount'] ?? 0, 2) }}</p>
                        <p><strong>Payment Method:</strong> {{ $data['payment']['method'] ?? 'N/A' }}</p>
                    @endif
                </div>
            @endif

            @if(isset($data['url']) && $data['url'])
                <div style="text-align: center;">
                    <a href="{{ $data['url'] }}" class="button">View Details</a>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                This is an automated notification from <strong>Day2Day Fresh</strong>.<br>
                If you have any questions, please contact our support team.
            </p>
            <p>
                <a href="{{ config('app.url') }}">Visit our website</a> | 
                <a href="{{ config('app.url') }}/notifications/preferences">Manage notification preferences</a>
            </p>
        </div>
    </div>
</body>
</html>




