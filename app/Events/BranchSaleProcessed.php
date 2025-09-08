<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PrivateChannel;

class BranchSaleProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Order $order,
        public int $branchId,
        public array $sessionSummary
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('branch.' . $this->branchId)];
    }

    public function broadcastAs(): string
    {
        return 'sale.processed';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'total_amount' => $this->order->total_amount,
                'payment_method' => $this->order->payment_method,
                'payment_status' => $this->order->payment_status,
                'created_at' => $this->order->created_at,
            ],
            'session' => $this->sessionSummary,
        ];
    }
}

