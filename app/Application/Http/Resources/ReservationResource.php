<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'guests_count' => $this->guests_count,
            'reservation_date' => $this->reservation_date,
            'reservation_time' => $this->reservation_time,
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'deposit_amount' => $this->deposit_amount,
            'deposit_paid' => $this->deposit_paid,
            'special_requests' => $this->special_requests,
            'notes' => $this->notes,

            'table' => new TableResource($this->whenLoaded('table')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
        ];
    }
}
