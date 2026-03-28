<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'total_price' => $this->total_price,
            'cost_price' => $this->cost_price,
            'course' => $this->course,
            'status' => $this->status,
            'comment' => $this->comment,
            'sent_to_kitchen_at' => $this->sent_to_kitchen_at?->toISOString(),
            'ready_at' => $this->ready_at?->toISOString(),

            'product' => new ProductResource($this->whenLoaded('product')),
            'modifiers' => ProductResource::collection($this->whenLoaded('modifiers')),
        ];
    }
}
