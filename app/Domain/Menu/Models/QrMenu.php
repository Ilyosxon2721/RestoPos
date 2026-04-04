<?php

declare(strict_types=1);

namespace App\Domain\Menu\Models;

use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrMenu extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'name',
        'slug',
        'description',
        'logo',
        'background_color',
        'primary_color',
        'currency',
        'show_images',
        'show_descriptions',
        'show_calories',
        'is_active',
    ];

    protected $casts = [
        'show_images' => 'boolean',
        'show_descriptions' => 'boolean',
        'show_calories' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Получить URL публичного меню.
     */
    public function getPublicUrl(): string
    {
        return url("/qr-menu/{$this->slug}");
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
