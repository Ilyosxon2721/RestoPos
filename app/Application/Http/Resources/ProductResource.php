<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'image' => $this->image,
            'type' => $this->type,
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'weight' => $this->weight,
            'cooking_time' => $this->cooking_time,
            'is_visible' => $this->is_visible,
            'is_available' => $this->is_available,
            'in_stop_list' => $this->in_stop_list,
            'sort_order' => $this->sort_order,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'workshop' => $this->whenLoaded('workshop'),
            'unit' => $this->whenLoaded('unit'),
        ];
    }
}
