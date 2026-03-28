<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'shape' => $this->shape,
            'pos_x' => $this->pos_x,
            'pos_y' => $this->pos_y,
            'width' => $this->width,
            'height' => $this->height,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,

            'hall' => new HallResource($this->whenLoaded('hall')),
            'current_order' => new OrderResource($this->whenLoaded('currentOrder')),
        ];
    }
}
