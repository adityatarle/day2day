<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order' => $this->whenLoaded('order', function () {
                return new OrderResource($this->order);
            }),
            'delivery_boy_id' => $this->delivery_boy_id,
            'delivery_boy' => $this->whenLoaded('deliveryBoy', function () {
                return [
                    'id' => $this->deliveryBoy->id,
                    'name' => $this->deliveryBoy->name,
                    'phone' => $this->deliveryBoy->phone,
                ];
            }),
            'status' => $this->status,
            'delivery_address' => $this->delivery_address,
            'delivery_phone' => $this->delivery_phone,
            'current_latitude' => $this->current_latitude,
            'current_longitude' => $this->current_longitude,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'picked_up_at' => $this->picked_up_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'return_reason' => $this->return_reason,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
