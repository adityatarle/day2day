<?php

namespace App\Events;

use App\Models\Order;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $fromStatus;
    public $toStatus;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, string $fromStatus, string $toStatus, ?User $user = null)
    {
        $this->order = $order;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order-status.' . $this->order->branch_id),
            new PrivateChannel('order-status.' . $this->order->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'user_name' => $this->user?->name,
            'timestamp' => now()->toISOString(),
            'branch_id' => $this->order->branch_id
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }
}