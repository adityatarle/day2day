<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class OrderNotificationService
{
    /**
     * Send notification for order status change
     */
    public function notifyStatusChange(Order $order, string $fromStatus, string $toStatus, ?User $user = null): void
    {
        try {
            // Send real-time notification
            event(new OrderStatusChanged($order, $fromStatus, $toStatus, $user));

            // Send email notifications based on status
            $this->sendEmailNotification($order, $toStatus);

            // Send SMS notifications for critical statuses
            if (in_array($toStatus, ['ready', 'delivered', 'cancelled'])) {
                $this->sendSmsNotification($order, $toStatus);
            }

            // Send push notifications to mobile app users
            $this->sendPushNotification($order, $toStatus);

            // Log notification
            Log::info('Order status notification sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'notified_by' => $user?->name
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmailNotification(Order $order, string $status): void
    {
        if (!$order->customer || !$order->customer->email) {
            return;
        }

        $subject = $this->getEmailSubject($status, $order);
        $template = $this->getEmailTemplate($status);

        try {
            Mail::send($template, [
                'order' => $order,
                'status' => $status,
                'statusInfo' => $order->getWorkflowStatusInfo()
            ], function ($message) use ($order, $subject) {
                $message->to($order->customer->email, $order->customer->name)
                        ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSmsNotification(Order $order, string $status): void
    {
        if (!$order->customer || !$order->customer->phone) {
            return;
        }

        $message = $this->getSmsMessage($status, $order);

        try {
            // Implement SMS service integration here
            // This could be Twilio, AWS SNS, or any other SMS provider
            Log::info('SMS notification would be sent', [
                'order_id' => $order->id,
                'phone' => $order->customer->phone,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send push notification
     */
    protected function sendPushNotification(Order $order, string $status): void
    {
        try {
            // Implement push notification service here
            // This could be Firebase, OneSignal, or any other push service
            Log::info('Push notification would be sent', [
                'order_id' => $order->id,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get email subject based on status
     */
    protected function getEmailSubject(string $status, Order $order): string
    {
        $subjects = [
            'confirmed' => "Order #{$order->order_number} Confirmed",
            'processing' => "Order #{$order->order_number} is Being Prepared",
            'ready' => "Order #{$order->order_number} is Ready for Pickup",
            'delivered' => "Order #{$order->order_number} Delivered Successfully",
            'cancelled' => "Order #{$order->order_number} Cancelled",
            'returned' => "Order #{$order->order_number} Returned"
        ];

        return $subjects[$status] ?? "Order #{$order->order_number} Status Update";
    }

    /**
     * Get email template based on status
     */
    protected function getEmailTemplate(string $status): string
    {
        $templates = [
            'confirmed' => 'emails.order.confirmed',
            'processing' => 'emails.order.processing',
            'ready' => 'emails.order.ready',
            'delivered' => 'emails.order.delivered',
            'cancelled' => 'emails.order.cancelled',
            'returned' => 'emails.order.returned'
        ];

        return $templates[$status] ?? 'emails.order.status-update';
    }

    /**
     * Get SMS message based on status
     */
    protected function getSmsMessage(string $status, Order $order): string
    {
        $messages = [
            'ready' => "Your order #{$order->order_number} is ready for pickup at {$order->branch->name}.",
            'delivered' => "Your order #{$order->order_number} has been delivered successfully. Thank you!",
            'cancelled' => "Your order #{$order->order_number} has been cancelled. Please contact us for more information."
        ];

        return $messages[$status] ?? "Your order #{$order->order_number} status has been updated.";
    }

    /**
     * Notify staff about urgent orders
     */
    public function notifyUrgentOrder(Order $order): void
    {
        try {
            // Get branch staff
            $staff = User::where('branch_id', $order->branch_id)
                        ->whereIn('role', ['branch_manager', 'cashier'])
                        ->get();

            foreach ($staff as $user) {
                // Send real-time notification
                event(new \App\Events\UrgentOrderCreated($order, $user));

                // Send email to staff
                if ($user->email) {
                    Mail::send('emails.staff.urgent-order', [
                        'order' => $order,
                        'user' => $user
                    ], function ($message) use ($user, $order) {
                        $message->to($user->email, $user->name)
                                ->subject("Urgent Order Alert - #{$order->order_number}");
                    });
                }
            }

            Log::info('Urgent order notification sent to staff', [
                'order_id' => $order->id,
                'staff_count' => $staff->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send urgent order notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify about delayed orders
     */
    public function notifyDelayedOrder(Order $order): void
    {
        try {
            // Calculate delay time
            $expectedTime = $order->expected_delivery_time;
            $delayMinutes = $expectedTime ? now()->diffInMinutes($expectedTime, false) : 0;

            if ($delayMinutes > 0) {
                // Notify customer
                if ($order->customer && $order->customer->phone) {
                    $message = "Your order #{$order->order_number} is delayed by {$delayMinutes} minutes. We apologize for the inconvenience.";
                    // Send SMS
                    Log::info('Delay notification would be sent', [
                        'order_id' => $order->id,
                        'delay_minutes' => $delayMinutes,
                        'message' => $message
                    ]);
                }

                // Notify management
                $this->notifyManagementAboutDelay($order, $delayMinutes);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send delay notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify management about delays
     */
    protected function notifyManagementAboutDelay(Order $order, int $delayMinutes): void
    {
        try {
            $managers = User::whereIn('role', ['super_admin', 'admin', 'branch_manager'])
                           ->where('branch_id', $order->branch_id)
                           ->get();

            foreach ($managers as $manager) {
                if ($manager->email) {
                    Mail::send('emails.management.order-delay', [
                        'order' => $order,
                        'delay_minutes' => $delayMinutes,
                        'manager' => $manager
                    ], function ($message) use ($manager, $order) {
                        $message->to($manager->email, $manager->name)
                                ->subject("Order Delay Alert - #{$order->order_number}");
                    });
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send delay notification to management', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send daily workflow summary
     */
    public function sendDailySummary(): void
    {
        try {
            $branches = \App\Models\Branch::all();

            foreach ($branches as $branch) {
                $summary = $this->generateDailySummary($branch);
                
                $managers = User::where('branch_id', $branch->id)
                               ->whereIn('role', ['branch_manager', 'admin'])
                               ->get();

                foreach ($managers as $manager) {
                    if ($manager->email) {
                        Mail::send('emails.management.daily-summary', [
                            'summary' => $summary,
                            'manager' => $manager,
                            'branch' => $branch
                        ], function ($message) use ($manager, $branch) {
                            $message->to($manager->email, $manager->name)
                                    ->subject("Daily Order Summary - {$branch->name}");
                        });
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send daily summary', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate daily summary data
     */
    protected function generateDailySummary(\App\Models\Branch $branch): array
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        $orders = Order::where('branch_id', $branch->id)
                      ->whereBetween('created_at', [$today, $tomorrow])
                      ->get();

        return [
            'total_orders' => $orders->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'processing_orders' => $orders->where('status', 'processing')->count(),
            'ready_orders' => $orders->where('status', 'ready')->count(),
            'delivered_orders' => $orders->where('status', 'delivered')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'total_sales' => $orders->where('status', 'delivered')->sum('total_amount'),
            'average_processing_time' => $orders->where('status', 'delivered')->avg('processing_time_minutes'),
            'urgent_orders' => $orders->where('is_urgent', true)->count(),
            'delayed_orders' => $this->getDelayedOrdersCount($orders)
        ];
    }

    /**
     * Get count of delayed orders
     */
    protected function getDelayedOrdersCount($orders): int
    {
        return $orders->filter(function ($order) {
            if (!$order->expected_delivery_time) {
                return false;
            }
            return now()->isAfter($order->expected_delivery_time) && 
                   !in_array($order->status, ['delivered', 'cancelled']);
        })->count();
    }
}