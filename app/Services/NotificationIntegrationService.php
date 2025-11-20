<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Payment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class NotificationIntegrationService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send notification when order is created
     */
    public function notifyOrderCreated(Order $order): void
    {
        try {
            // Notify branch manager and cashiers
            $branchUsers = User::where('branch_id', $order->branch_id)
                ->whereHas('role', function ($query) {
                    $query->whereIn('name', ['branch_manager', 'cashier']);
                })
                ->get();

            foreach ($branchUsers as $user) {
                $this->notificationService->sendNotification(
                    $user,
                    'order_created',
                    [
                        'order' => [
                            'order_number' => $order->order_number,
                            'total_amount' => $order->total_amount,
                            'item_count' => $order->orderItems->count(),
                        ],
                        'customer' => [
                            'name' => $order->customer->name ?? 'Walk-in Customer',
                            'email' => $order->customer->email ?? '',
                        ],
                        'branch' => [
                            'name' => $order->branch->name,
                        ],
                    ],
                    [
                        [
                            'type' => 'view',
                            'label' => 'View Order',
                            'url' => "/orders/{$order->id}",
                            'primary' => true,
                        ],
                        [
                            'type' => 'approve',
                            'label' => 'Approve Order',
                            'url' => "/orders/{$order->id}/approve",
                            'primary' => false,
                        ],
                    ]
                );
            }

            // Send order confirmation to customer
            if ($order->customer && $order->customer->email) {
                $this->notificationService->sendNotification(
                    $order->customer,
                    'customer_order_confirmation',
                    [
                        'customer' => [
                            'name' => $order->customer->name,
                            'email' => $order->customer->email,
                        ],
                        'order' => [
                            'order_number' => $order->order_number,
                            'total_amount' => $order->total_amount,
                        ],
                        'delivery' => [
                            'address' => $order->delivery_address ?? 'Store Pickup',
                            'estimated_date' => $order->estimated_delivery_date ?? 'Same Day',
                        ],
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to send order created notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification when stock is low
     */
    public function notifyLowStock(Product $product, int $branchId, int $currentStock): void
    {
        try {
            // Get branch manager and admin users
            $users = User::where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->whereHas('role', function ($q) {
                          $q->where('name', 'branch_manager');
                      });
            })->orWhereHas('role', function ($query) {
                $query->whereIn('name', ['admin', 'super_admin']);
            })->get();

            $notificationType = $currentStock <= 0 ? 'stock_out' : 
                               ($currentStock <= 5 ? 'stock_critical' : 'stock_low');

            foreach ($users as $user) {
                $this->notificationService->sendNotification(
                    $user,
                    $notificationType,
                    [
                        'product' => [
                            'name' => $product->name,
                            'current_stock' => $currentStock,
                            'min_stock_threshold' => $product->min_stock_threshold,
                        ],
                        'branch' => [
                            'name' => $user->branch->name ?? 'Main Branch',
                        ],
                    ],
                    [
                        [
                            'type' => 'view',
                            'label' => 'View Product',
                            'url' => "/products/{$product->id}",
                            'primary' => true,
                        ],
                        [
                            'type' => 'edit',
                            'label' => 'Update Stock',
                            'url' => "/inventory/{$product->id}/add-stock",
                            'primary' => false,
                        ],
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to send low stock notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification when payment is received
     */
    public function notifyPaymentReceived(Payment $payment): void
    {
        try {
            $order = $payment->order;
            
            // Notify branch manager and cashiers
            $branchUsers = User::where('branch_id', $order->branch_id)
                ->whereHas('role', function ($query) {
                    $query->whereIn('name', ['branch_manager', 'cashier']);
                })
                ->get();

            foreach ($branchUsers as $user) {
                $this->notificationService->sendNotification(
                    $user,
                    'payment_received',
                    [
                        'payment' => [
                            'amount' => $payment->amount,
                            'method' => $payment->payment_method,
                            'transaction_id' => $payment->transaction_id,
                        ],
                        'order' => [
                            'order_number' => $order->order_number,
                        ],
                        'customer' => [
                            'name' => $order->customer->name ?? 'Walk-in Customer',
                        ],
                    ],
                    [
                        [
                            'type' => 'view',
                            'label' => 'View Payment',
                            'url' => "/payments/{$payment->id}",
                            'primary' => true,
                        ],
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to send payment received notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification when purchase order is created
     */
    public function notifyPurchaseOrderCreated(PurchaseOrder $purchaseOrder): void
    {
        try {
            // Notify admin users
            $adminUsers = User::whereHas('role', function ($query) {
                $query->whereIn('name', ['admin', 'super_admin']);
            })->get();

            foreach ($adminUsers as $user) {
                $this->notificationService->sendNotification(
                    $user,
                    'purchase_order_created',
                    [
                        'purchase_order' => [
                            'po_number' => $purchaseOrder->po_number,
                            'total_amount' => $purchaseOrder->total_amount,
                            'vendor_name' => $purchaseOrder->vendor->name,
                        ],
                        'branch' => [
                            'name' => $purchaseOrder->branch->name,
                        ],
                    ],
                    [
                        [
                            'type' => 'view',
                            'label' => 'View Purchase Order',
                            'url' => "/purchase-orders/{$purchaseOrder->id}",
                            'primary' => true,
                        ],
                        [
                            'type' => 'approve',
                            'label' => 'Approve PO',
                            'url' => "/purchase-orders/{$purchaseOrder->id}/approve",
                            'primary' => false,
                        ],
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to send purchase order created notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification when order is delivered
     */
    public function notifyOrderDelivered(Order $order): void
    {
        try {
            // Notify customer
            if ($order->customer && $order->customer->email) {
                $this->notificationService->sendNotification(
                    $order->customer,
                    'order_delivered',
                    [
                        'customer' => [
                            'name' => $order->customer->name,
                        ],
                        'order' => [
                            'order_number' => $order->order_number,
                        ],
                        'delivery' => [
                            'address' => $order->delivery_address,
                            'delivered_at' => now()->format('Y-m-d H:i:s'),
                            'delivery_person' => $order->delivery->delivery_person ?? 'Delivery Team',
                        ],
                    ]
                );
            }

            // Notify branch manager
            $branchManager = User::where('branch_id', $order->branch_id)
                ->whereHas('role', function ($query) {
                    $query->where('name', 'branch_manager');
                })
                ->first();

            if ($branchManager) {
                $this->notificationService->sendNotification(
                    $branchManager,
                    'order_delivered',
                    [
                        'order' => [
                            'order_number' => $order->order_number,
                        ],
                        'customer' => [
                            'name' => $order->customer->name ?? 'Walk-in Customer',
                        ],
                        'delivery' => [
                            'delivered_at' => now()->format('Y-m-d H:i:s'),
                        ],
                    ],
                    [
                        [
                            'type' => 'view',
                            'label' => 'View Order',
                            'url' => "/orders/{$order->id}",
                            'primary' => true,
                        ],
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to send order delivered notification: " . $e->getMessage());
        }
    }

    /**
     * Send daily digest to users who prefer it
     */
    public function sendDailyDigest(): void
    {
        try {
            // This would typically be called by a scheduled command
            // Implementation would gather all digest notifications and send them
            Log::info("Daily digest processing would be implemented here");
        } catch (\Exception $e) {
            Log::error("Failed to send daily digest: " . $e->getMessage());
        }
    }
}




