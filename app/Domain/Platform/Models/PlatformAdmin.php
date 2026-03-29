<?php

declare(strict_types=1);

namespace App\Domain\Platform\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PlatformAdmin extends Authenticatable
{
    use HasFactory, Notifiable, HasUuid, SoftDeletes;

    protected $table = 'platform_admins';
    protected $guard = 'platform';

    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 'avatar', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
