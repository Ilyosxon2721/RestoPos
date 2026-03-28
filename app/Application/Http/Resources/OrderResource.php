<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'order_number' => $this->order_number,
            'type' => $this->type,
            'source' => $this->source,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'guests_count' => $this->guests_count,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'discount_percent' => $this->discount_percent,
            'service_charge' => $this->service_charge,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'opened_at' => $this->opened_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),

            'table' => new TableResource($this->whenLoaded('table')),
            'waiter' => new UserResource($this->whenLoaded('waiter')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
