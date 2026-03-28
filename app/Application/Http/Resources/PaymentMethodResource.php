<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'is_fiscal' => $this->is_fiscal,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
