<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'type' => $this->type,
            'customer_type' => $this->customer_type,
            'credit_limit' => (float) ($this->credit_limit ?? 0),
            'credit_days' => (int) ($this->credit_days ?? 0),
            'credit_balance' => (float) $this->getCreditBalance(),
            'is_active' => (bool) $this->is_active,
            'total_orders' => $this->when(isset($this->orders_count), $this->orders_count),
            'total_spent' => $this->when($this->relationLoaded('orders'), function () {
                return (float) $this->orders->sum('total_amount');
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
