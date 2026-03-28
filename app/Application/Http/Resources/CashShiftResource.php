<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'opening_cash' => $this->opening_cash,
            'closing_cash' => $this->closing_cash,
            'cash_difference' => $this->cash_difference,
            'total_revenue' => $this->total_revenue,
            'total_cash_payments' => $this->total_cash_payments,
            'total_card_payments' => $this->total_card_payments,
            'total_orders' => $this->total_orders,
            'opened_at' => $this->opened_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'notes' => $this->notes,

            'opened_by_user' => new UserResource($this->whenLoaded('openedByUser')),
            'closed_by_user' => new UserResource($this->whenLoaded('closedByUser')),
        ];
    }
}
