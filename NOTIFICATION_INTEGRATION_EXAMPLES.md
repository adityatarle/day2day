# 🔔 Notification System Integration Examples

## 📋 Overview

This document provides practical examples of how to integrate the notification system with your existing business logic in the Day2Day Fresh application.

## 🛒 Order Management Integration

### Order Created Notification

Add to your `OrderController@store` method:

```php
use App\Services\NotificationIntegrationService;

public function store(Request $request)
{
    // ... existing order creation logic ...
    
    $order = Order::create($orderData);
    
    // Send notification
    $notificationService = app(NotificationIntegrationService::class);
    $notificationService->notifyOrderCreated($order);
    
    return response()->json(['success' => true, 'order' => $order]);
}
```

### Order Status Update Notification

Add to your `OrderController@updateStatus` method:

```php
public function updateStatus(Request $request, Order $order)
{
    $oldStatus = $order->status;
    $order->update(['status' => $request->status]);
    
    // Send appropriate notification based on status change
    $notificationService = app(NotificationIntegrationService::class);
    
    switch ($request->status) {
        case 'delivered':
            $notificationService->notifyOrderDelivered($order);
            break;
        case 'cancelled':
            $this->notifyOrderCancelled($order);
            break;
    }
    
    return response()->json(['success' => true]);
}
```

## 📦 Inventory Management Integration

### Stock Update Notification

Add to your `InventoryController@updateStock` method:

```php
use App\Services\NotificationIntegrationService;

public function updateStock(Request $request, Product $product)
{
    $oldStock = $product->current_stock;
    $newStock = $request->stock_quantity;
    
    $product->update(['current_stock' => $newStock]);
    
    // Check if stock is low and send notification
    if ($newStock <= $product->min_stock_threshold) {
        $notificationService = app(NotificationIntegrationService::class);
        $notificationService->notifyLowStock($product, auth()->user()->branch_id, $newStock);
    }
    
    return response()->json(['success' => true]);
}
```

### Automatic Stock Monitoring

Create a scheduled command to check stock levels:

```php
// app/Console/Commands/CheckStockLevels.php
use App\Models\Product;
use App\Services\NotificationIntegrationService;

public function handle()
{
    $products = Product::where('current_stock', '<=', DB::raw('min_stock_threshold'))->get();
    
    $notificationService = app(NotificationIntegrationService::class);
    
    foreach ($products as $product) {
        $notificationService->notifyLowStock(
            $product, 
            $product->branch_id, 
            $product->current_stock
        );
    }
}
```

## 💰 Payment Processing Integration

### Payment Received Notification

Add to your `PaymentController@store` method:

```php
use App\Services\NotificationIntegrationService;

public function store(Request $request)
{
    // ... existing payment processing logic ...
    
    $payment = Payment::create($paymentData);
    
    // Send notification
    $notificationService = app(NotificationIntegrationService::class);
    $notificationService->notifyPaymentReceived($payment);
    
    return response()->json(['success' => true, 'payment' => $payment]);
}
```

## 🛍️ Purchase Order Integration

### Purchase Order Created Notification

Add to your `PurchaseOrderController@store` method:

```php
use App\Services\NotificationIntegrationService;

public function store(Request $request)
{
    // ... existing purchase order creation logic ...
    
    $purchaseOrder = PurchaseOrder::create($poData);
    
    // Send notification
    $notificationService = app(NotificationIntegrationService::class);
    $notificationService->notifyPurchaseOrderCreated($purchaseOrder);
    
    return response()->json(['success' => true, 'purchase_order' => $purchaseOrder]);
}
```

## 🎯 Event-Driven Notifications

### Using Laravel Events

Create events for automatic notification triggering:

```php
// app/Events/OrderCreated.php
class OrderCreated
{
    public $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}

// app/Listeners/SendOrderCreatedNotification.php
class SendOrderCreatedNotification
{
    protected $notificationService;
    
    public function __construct(NotificationIntegrationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    
    public function handle(OrderCreated $event)
    {
        $this->notificationService->notifyOrderCreated($event->order);
    }
}
```

### Register Event Listeners

In `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    OrderCreated::class => [
        SendOrderCreatedNotification::class,
    ],
    StockLow::class => [
        SendLowStockNotification::class,
    ],
    PaymentReceived::class => [
        SendPaymentReceivedNotification::class,
    ],
];
```

## 🔔 Custom Notification Examples

### Branch Request Notification

```php
public function createBranchRequest(Request $request)
{
    $request = BranchRequest::create($requestData);
    
    // Notify admin users
    $notificationService = app(NotificationService::class);
    $notificationService->sendNotificationToRole(
        'admin',
        'branch_request_created',
        [
            'request' => [
                'type' => $request->type,
                'description' => $request->description,
                'amount' => $request->amount,
            ],
            'branch' => [
                'name' => $request->branch->name,
            ],
            'requester' => [
                'name' => $request->user->name,
            ],
        ],
        [
            [
                'type' => 'view',
                'label' => 'View Request',
                'url' => "/branch-requests/{$request->id}",
                'primary' => true,
            ],
            [
                'type' => 'approve',
                'label' => 'Approve',
                'url' => "/branch-requests/{$request->id}/approve",
                'primary' => false,
            ],
        ]
    );
}
```

### Security Alert Notification

```php
public function detectSuspiciousActivity(User $user, string $activity)
{
    $notificationService = app(NotificationService::class);
    
    // Notify admin users
    $notificationService->sendNotificationToRole(
        'admin',
        'security_alert',
        [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'activity' => $activity,
            'timestamp' => now()->toISOString(),
        ],
        [
            [
                'type' => 'view',
                'label' => 'View User',
                'url' => "/users/{$user->id}",
                'primary' => true,
            ],
        ]
    );
}
```

## 📱 Frontend Integration

### Notification Bell Component

Add to your main layout:

```html
<!-- resources/views/layouts/app.blade.php -->
<div class="relative">
    <button onclick="toggleNotificationDropdown()" 
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
        <i class="fas fa-bell text-xl"></i>
        <span id="notification-badge" 
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">
            0
        </span>
    </button>
    
    <!-- Notification Dropdown -->
    <div id="notification-dropdown" 
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-50">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
        </div>
        <div id="notification-list" class="max-h-96 overflow-y-auto">
            <!-- Notifications will be loaded here -->
        </div>
        <div class="p-4 border-t border-gray-200">
            <a href="/notifications" class="text-blue-600 hover:text-blue-800 text-sm">
                View All Notifications
            </a>
        </div>
    </div>
</div>
```

### JavaScript for Real-time Updates

```javascript
// Load notification count and recent notifications
function loadNotifications() {
    // Load unread count
    fetch('/api/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-badge');
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });

    // Load recent notifications
    fetch('/api/notifications/recent?limit=5')
        .then(response => response.json())
        .then(data => {
            updateNotificationDropdown(data.notifications);
        });
}

// Update notification dropdown
function updateNotificationDropdown(notifications) {
    const container = document.getElementById('notification-list');
    
    if (notifications.length === 0) {
        container.innerHTML = '<div class="p-4 text-center text-gray-500">No notifications</div>';
        return;
    }
    
    container.innerHTML = notifications.map(notification => `
        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer" 
             onclick="handleNotificationClick(${notification.id})">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                         style="background-color: ${notification.color}20; color: ${notification.color}">
                        <i class="${notification.icon}"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                    <p class="text-sm text-gray-600">${notification.body}</p>
                    <p class="text-xs text-gray-500 mt-1">${notification.created_at}</p>
                </div>
                ${!notification.is_read ? '<div class="w-2 h-2 bg-blue-500 rounded-full"></div>' : ''}
            </div>
        </div>
    `).join('');
}

// Handle notification click
function handleNotificationClick(notificationId) {
    // Mark as read
    fetch('/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    }).then(() => {
        loadNotifications(); // Refresh notifications
    });
}

// Auto-refresh every 30 seconds
setInterval(loadNotifications, 30000);

// Load notifications on page load
document.addEventListener('DOMContentLoaded', loadNotifications);
```

## 🎨 Customizing Notifications

### Creating Custom Notification Types

```php
// Create new notification type
$notificationType = NotificationType::create([
    'name' => 'custom_business_alert',
    'display_name' => 'Custom Business Alert',
    'description' => 'Custom alert for business operations',
    'icon' => 'fas fa-exclamation-triangle',
    'color' => '#FF6B6B',
    'channels' => ['database', 'mail', 'sms'],
    'priority' => 3
]);

// Create templates for each channel
NotificationTemplate::create([
    'notification_type_id' => $notificationType->id,
    'channel' => 'database',
    'body' => 'Custom alert: {{message}} - {{timestamp}}',
    'variables' => ['message', 'timestamp']
]);

NotificationTemplate::create([
    'notification_type_id' => $notificationType->id,
    'channel' => 'mail',
    'subject' => 'Custom Business Alert - {{title}}',
    'body' => 'Custom business alert received:\n\n{{message}}\n\nTime: {{timestamp}}\n\nPlease take appropriate action.',
    'variables' => ['title', 'message', 'timestamp']
]);
```

### Using Custom Notifications

```php
$notificationService = app(NotificationService::class);

$notificationService->sendNotification(
    $user,
    'custom_business_alert',
    [
        'title' => 'Inventory Audit Required',
        'message' => 'Monthly inventory audit is due for completion',
        'timestamp' => now()->format('Y-m-d H:i:s'),
    ],
    [
        [
            'type' => 'view',
            'label' => 'Start Audit',
            'url' => '/inventory/audit',
            'primary' => true,
        ],
    ]
);
```

## 📊 Monitoring and Analytics

### Notification Statistics

```php
// Get notification statistics
$stats = [
    'total_sent' => NotificationHistory::count(),
    'unread_count' => NotificationHistory::whereNull('read_at')->count(),
    'delivery_rate' => NotificationHistory::where('status', 'delivered')->count() / NotificationHistory::count() * 100,
    'channel_breakdown' => NotificationHistory::groupBy('channel')->selectRaw('channel, count(*) as count')->get(),
];

// Get user engagement metrics
$engagement = [
    'read_rate' => NotificationHistory::whereNotNull('read_at')->count() / NotificationHistory::count() * 100,
    'action_rate' => NotificationReadStatus::count() / NotificationHistory::count() * 100,
];
```

## 🚀 Best Practices

### 1. Use Appropriate Channels
- **Database**: All notifications (in-app)
- **Email**: Important updates, confirmations
- **SMS**: Critical alerts only (stock critical, security)
- **WhatsApp**: Customer communications
- **Push**: Real-time updates for mobile users

### 2. Set Proper Priorities
- **Low (1)**: Informational updates
- **Medium (2)**: Regular business activities
- **High (3)**: Important alerts requiring attention
- **Critical (4)**: Urgent issues requiring immediate action

### 3. Include Action Buttons
Always provide relevant actions users can take:
- View details
- Approve/Reject
- Edit/Update
- Dismiss

### 4. Use Templates
Create reusable templates for consistency and easy maintenance.

### 5. Monitor Performance
Track delivery rates, user engagement, and system performance.

---

*These examples demonstrate how to seamlessly integrate the notification system with your existing business logic, providing timely and relevant notifications to keep your team informed and your business running smoothly.*




