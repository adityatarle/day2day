<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'code' => $this->product->code,
                    'category' => $this->product->category,
                    'weight_unit' => $this->product->weight_unit,
                ];
            }),
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'total_price' => (float) $this->total_price,
            'actual_weight' => $this->actual_weight ? (float) $this->actual_weight : null,
            'billed_weight' => $this->billed_weight ? (float) $this->billed_weight : null,
            'adjustment_weight' => $this->adjustment_weight ? (float) $this->adjustment_weight : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
