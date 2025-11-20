<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationType;
use App\Models\NotificationTemplate;

class NotificationSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createNotificationTypes();
        $this->createNotificationTemplates();
    }

    private function createNotificationTypes(): void
    {
        $notificationTypes = [
            // Order Notifications
            [
                'name' => 'order_created',
                'display_name' => 'New Order Created',
                'description' => 'A new order has been placed',
                'icon' => 'fas fa-shopping-cart',
                'color' => '#10B981',
                'channels' => ['database', 'mail', 'push'],
                'priority' => 2,
            ],
            [
                'name' => 'order_updated',
                'display_name' => 'Order Updated',
                'description' => 'An order has been updated',
                'icon' => 'fas fa-edit',
                'color' => '#3B82F6',
                'channels' => ['database', 'mail'],
                'priority' => 2,
            ],
            [
                'name' => 'order_cancelled',
                'display_name' => 'Order Cancelled',
                'description' => 'An order has been cancelled',
                'icon' => 'fas fa-times-circle',
                'color' => '#EF4444',
                'channels' => ['database', 'mail', 'sms'],
                'priority' => 3,
            ],
            [
                'name' => 'order_delivered',
                'display_name' => 'Order Delivered',
                'description' => 'An order has been delivered',
                'icon' => 'fas fa-truck',
                'color' => '#10B981',
                'channels' => ['database', 'mail', 'whatsapp'],
                'priority' => 2,
            ],

            // Inventory Notifications
            [
                'name' => 'stock_low',
                'display_name' => 'Low Stock Alert',
                'description' => 'Product stock is running low',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => '#F59E0B',
                'channels' => ['database', 'mail', 'sms'],
                'priority' => 3,
            ],
            [
                'name' => 'stock_critical',
                'display_name' => 'Critical Stock Alert',
                'description' => 'Product stock is critically low',
                'icon' => 'fas fa-exclamation-circle',
                'color' => '#EF4444',
                'channels' => ['database', 'mail', 'sms', 'whatsapp'],
                'priority' => 4,
            ],
            [
                'name' => 'stock_out',
                'display_name' => 'Out of Stock',
                'description' => 'Product is out of stock',
                'icon' => 'fas fa-times',
                'color' => '#DC2626',
                'channels' => ['database', 'mail', 'sms'],
                'priority' => 4,
            ],

            // Purchase Order Notifications
            [
                'name' => 'purchase_order_created',
                'display_name' => 'Purchase Order Created',
                'description' => 'A new purchase order has been created',
                'icon' => 'fas fa-file-invoice',
                'color' => '#8B5CF6',
                'channels' => ['database', 'mail'],
                'priority' => 2,
            ],
            [
                'name' => 'purchase_order_approved',
                'display_name' => 'Purchase Order Approved',
                'description' => 'A purchase order has been approved',
                'icon' => 'fas fa-check-circle',
                'color' => '#10B981',
                'channels' => ['database', 'mail'],
                'priority' => 2,
            ],
            [
                'name' => 'purchase_order_received',
                'display_name' => 'Purchase Order Received',
                'description' => 'Purchase order items have been received',
                'icon' => 'fas fa-box',
                'color' => '#10B981',
                'channels' => ['database', 'mail'],
                'priority' => 2,
            ],

            // Payment Notifications
            [
                'name' => 'payment_received',
                'display_name' => 'Payment Received',
                'description' => 'A payment has been received',
                'icon' => 'fas fa-credit-card',
                'color' => '#10B981',
                'channels' => ['database', 'mail', 'sms'],
                'priority' => 3,
            ],
            [
                'name' => 'payment_failed',
                'display_name' => 'Payment Failed',
                'description' => 'A payment has failed',
                'icon' => 'fas fa-times-circle',
                'color' => '#EF4444',
                'channels' => ['database', 'mail', 'sms'],
                'priority' => 3,
            ],

            // Customer Notifications
            [
                'name' => 'customer_registered',
                'display_name' => 'New Customer Registered',
                'description' => 'A new customer has registered',
                'icon' => 'fas fa-user-plus',
                'color' => '#3B82F6',
                'channels' => ['database', 'mail'],
                'priority' => 1,
            ],
            [
                'name' => 'customer_order_confirmation',
                'display_name' => 'Order Confirmation',
                'description' => 'Order confirmation for customer',
                'icon' => 'fas fa-check',
                'color' => '#10B981',
                'channels' => ['mail', 'whatsapp'],
                'priority' => 2,
            ],

            // System Notifications
            [
                'name' => 'system_maintenance',
                'display_name' => 'System Maintenance',
                'description' => 'System maintenance notification',
                'icon' => 'fas fa-tools',
                'color' => '#6B7280',
                'channels' => ['database', 'mail'],
                'priority' => 2,
            ],
            [
                'name' => 'security_alert',
                'display_name' => 'Security Alert',
                'description' => 'Security alert notification',
                'icon' => 'fas fa-shield-alt',
                'color' => '#EF4444',
                'channels' => ['database', 'mail', 'sms'],
                'priority' => 4,
            ],

            // Branch Notifications
            [
                'name' => 'branch_request_created',
                'display_name' => 'Branch Request Created',
                'description' => 'A branch has created a new request',
                'icon' => 'fas fa-building',
                'color' => '#8B5CF6',
                'channels' => ['database', 'mail'],
                'priority' => 2,
            ],
            [
                'name' => 'branch_request_approved',
                'display_name' => 'Branch Request Approved',
                'description' => 'A branch request has been approved',
                'icon' => 'fas fa-check-circle',
                'color' => '#10B981',
                'channels' => ['database', 'mail'],
                'priority' => 2,
            ],
        ];

        foreach ($notificationTypes as $type) {
            NotificationType::create($type);
        }
    }

    private function createNotificationTemplates(): void
    {
        $templates = [
            // Order Created Templates
            [
                'notification_type_name' => 'order_created',
                'channel' => 'database',
                'subject' => null,
                'body' => 'New order #{{order.order_number}} has been placed by {{customer.name}} for ₹{{order.total_amount}}',
                'variables' => ['order.order_number', 'customer.name', 'order.total_amount'],
            ],
            [
                'notification_type_name' => 'order_created',
                'channel' => 'mail',
                'subject' => 'New Order #{{order.order_number}} - Day2Day Fresh',
                'body' => 'A new order has been placed by {{customer.name}}.\n\nOrder Details:\n- Order Number: {{order.order_number}}\n- Total Amount: ₹{{order.total_amount}}\n- Items: {{order.item_count}} items\n\nPlease process this order as soon as possible.',
                'variables' => ['order.order_number', 'customer.name', 'order.total_amount', 'order.item_count'],
            ],
            [
                'notification_type_name' => 'order_created',
                'channel' => 'push',
                'subject' => 'New Order #{{order.order_number}}',
                'body' => 'New order from {{customer.name}} for ₹{{order.total_amount}}',
                'variables' => ['order.order_number', 'customer.name', 'order.total_amount'],
            ],

            // Stock Low Templates
            [
                'notification_type_name' => 'stock_low',
                'channel' => 'database',
                'subject' => null,
                'body' => 'Low stock alert: {{product.name}} has only {{stock.quantity}} units remaining',
                'variables' => ['product.name', 'stock.quantity'],
            ],
            [
                'notification_type_name' => 'stock_low',
                'channel' => 'mail',
                'subject' => 'Low Stock Alert - {{product.name}}',
                'body' => 'Product {{product.name}} is running low on stock.\n\nCurrent Stock: {{stock.quantity}} units\nMinimum Threshold: {{product.min_stock_threshold}} units\nBranch: {{branch.name}}\n\nPlease consider restocking this product.',
                'variables' => ['product.name', 'stock.quantity', 'product.min_stock_threshold', 'branch.name'],
            ],
            [
                'notification_type_name' => 'stock_low',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'LOW STOCK: {{product.name}} - {{stock.quantity}} units left at {{branch.name}}',
                'variables' => ['product.name', 'stock.quantity', 'branch.name'],
            ],

            // Payment Received Templates
            [
                'notification_type_name' => 'payment_received',
                'channel' => 'database',
                'subject' => null,
                'body' => 'Payment of ₹{{payment.amount}} received for order #{{order.order_number}}',
                'variables' => ['payment.amount', 'order.order_number'],
            ],
            [
                'notification_type_name' => 'payment_received',
                'channel' => 'mail',
                'subject' => 'Payment Received - Order #{{order.order_number}}',
                'body' => 'Payment has been received for order #{{order.order_number}}.\n\nPayment Details:\n- Amount: ₹{{payment.amount}}\n- Method: {{payment.method}}\n- Transaction ID: {{payment.transaction_id}}\n- Customer: {{customer.name}}',
                'variables' => ['order.order_number', 'payment.amount', 'payment.method', 'payment.transaction_id', 'customer.name'],
            ],
            [
                'notification_type_name' => 'payment_received',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Payment received: ₹{{payment.amount}} for order #{{order.order_number}}',
                'variables' => ['payment.amount', 'order.order_number'],
            ],

            // Customer Order Confirmation Templates
            [
                'notification_type_name' => 'customer_order_confirmation',
                'channel' => 'mail',
                'subject' => 'Order Confirmation #{{order.order_number}} - Day2Day Fresh',
                'body' => 'Thank you for your order, {{customer.name}}!\n\nOrder Details:\n- Order Number: {{order.order_number}}\n- Total Amount: ₹{{order.total_amount}}\n- Delivery Address: {{delivery.address}}\n- Estimated Delivery: {{delivery.estimated_date}}\n\nWe will notify you when your order is ready for delivery.',
                'variables' => ['customer.name', 'order.order_number', 'order.total_amount', 'delivery.address', 'delivery.estimated_date'],
            ],
            [
                'notification_type_name' => 'customer_order_confirmation',
                'channel' => 'whatsapp',
                'subject' => null,
                'body' => 'Hi {{customer.name}}! 👋\n\nYour order #{{order.order_number}} has been confirmed!\n\n💰 Total: ₹{{order.total_amount}}\n📍 Delivery: {{delivery.address}}\n🚚 Estimated: {{delivery.estimated_date}}\n\nWe\'ll notify you when it\'s ready! 🛒',
                'variables' => ['customer.name', 'order.order_number', 'order.total_amount', 'delivery.address', 'delivery.estimated_date'],
            ],

            // Order Delivered Templates
            [
                'notification_type_name' => 'order_delivered',
                'channel' => 'database',
                'subject' => null,
                'body' => 'Order #{{order.order_number}} has been delivered to {{customer.name}}',
                'variables' => ['order.order_number', 'customer.name'],
            ],
            [
                'notification_type_name' => 'order_delivered',
                'channel' => 'mail',
                'subject' => 'Order Delivered #{{order.order_number}} - Day2Day Fresh',
                'body' => 'Your order has been successfully delivered!\n\nOrder Details:\n- Order Number: {{order.order_number}}\n- Delivered To: {{delivery.address}}\n- Delivery Time: {{delivery.delivered_at}}\n- Delivery Person: {{delivery.delivery_person}}\n\nThank you for choosing Day2Day Fresh!',
                'variables' => ['order.order_number', 'delivery.address', 'delivery.delivered_at', 'delivery.delivery_person'],
            ],
            [
                'notification_type_name' => 'order_delivered',
                'channel' => 'whatsapp',
                'subject' => null,
                'body' => '🎉 Your order #{{order.order_number}} has been delivered!\n\n📍 Delivered to: {{delivery.address}}\n⏰ Time: {{delivery.delivered_at}}\n👤 Delivered by: {{delivery.delivery_person}}\n\nThank you for choosing Day2Day Fresh! 🥬🍎',
                'variables' => ['order.order_number', 'delivery.address', 'delivery.delivered_at', 'delivery.delivery_person'],
            ],
        ];

        foreach ($templates as $template) {
            $notificationType = NotificationType::where('name', $template['notification_type_name'])->first();
            
            if ($notificationType) {
                NotificationTemplate::create([
                    'notification_type_id' => $notificationType->id,
                    'channel' => $template['channel'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'variables' => $template['variables'],
                ]);
            }
        }
    }
}




