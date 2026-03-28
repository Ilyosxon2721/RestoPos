<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'bonus_balance' => $this->bonus_balance,
            'discount_percent' => $this->discount_percent,
            'total_orders' => $this->total_orders,
            'total_spent' => $this->total_spent,
            'loyalty_card_number' => $this->loyalty_card_number,
            'tags' => $this->tags,
            'notes' => $this->notes,
            'last_visit_at' => $this->last_visit_at?->toISOString(),

            'group' => new CustomerGroupResource($this->whenLoaded('group')),
        ];
    }
}
