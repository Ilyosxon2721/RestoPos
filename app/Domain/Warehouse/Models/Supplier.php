<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'legal_name',
        'inn',
        'contact_person',
        'phone',
        'email',
        'address',
        'payment_terms',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'is_active' => 'boolean',
    ];

    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class);
    }
}
