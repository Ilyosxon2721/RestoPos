<?php

declare(strict_types=1);

namespace App\Application\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'hire_date' => $this->hire_date,
            'salary_type' => $this->salary_type,
            'monthly_salary' => $this->monthly_salary,
            'hourly_rate' => $this->hourly_rate,
            'sales_percent' => $this->sales_percent,

            'user' => new UserResource($this->whenLoaded('user')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
        ];
    }
}
