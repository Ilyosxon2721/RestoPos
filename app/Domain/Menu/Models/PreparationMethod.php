<?php

declare(strict_types=1);

namespace App\Domain\Menu\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreparationMethod extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'default_loss_percent',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_loss_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
