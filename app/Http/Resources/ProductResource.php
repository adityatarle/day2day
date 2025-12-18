<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $branch = $request->user()?->branch;
        $branchProduct = null;
        
        if ($branch) {
            $branchProduct = $this->branches->firstWhere('id', $branch->id);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'weight_unit' => $this->weight_unit,
            'bill_by' => $this->bill_by ?? 'weight',
            'purchase_price' => (float) $this->purchase_price,
            'mrp' => (float) $this->mrp,
            'selling_price' => (float) ($branchProduct?->pivot?->selling_price ?? $this->selling_price),
            'stock_threshold' => (float) $this->stock_threshold,
            'current_stock' => (float) ($branchProduct?->pivot?->current_stock ?? 0),
            'is_active' => (bool) $this->is_active,
            'is_available_online' => (bool) ($branchProduct?->pivot?->is_available_online ?? true),
            'shelf_life_days' => $this->shelf_life_days,
            'storage_temperature' => $this->storage_temperature,
            'is_perishable' => (bool) $this->is_perishable,
            'is_sold_out' => $branch ? $this->isSoldOut($branch) : false,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
