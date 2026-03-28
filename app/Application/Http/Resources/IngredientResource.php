<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'current_cost' => $this->current_cost,
            'min_stock' => $this->min_stock,
            'unit_id' => $this->unit_id,
        ];
    }
}
