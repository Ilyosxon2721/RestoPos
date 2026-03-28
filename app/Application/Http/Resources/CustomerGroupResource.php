<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'discount_percent' => $this->discount_percent,
            'bonus_earn_percent' => $this->bonus_earn_percent,
            'min_spent_to_join' => $this->min_spent_to_join,
            'color' => $this->color,
        ];
    }
}
