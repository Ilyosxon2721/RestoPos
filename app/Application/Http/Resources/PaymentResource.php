<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'amount' => $this->amount,
            'change_amount' => $this->change_amount,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at?->toISOString(),

            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
