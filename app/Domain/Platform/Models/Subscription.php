<?php

declare(strict_types=1);

namespace App\Domain\Platform\Models;

use App\Domain\Organization\Models\Organization;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'organization_id', 'plan_id', 'status', 'trial_ends_at', 'starts_at',
        'ends_at', 'cancelled_at', 'payment_method', 'last_payment_at', 'next_payment_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_payment_at' => 'datetime',
            'next_payment_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || ($this->status === 'trial' && $this->trial_ends_at?->isFuture());
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'active')
                ->orWhere(fn ($q2) => $q2->where('status', 'trial')->where('trial_ends_at', '>', now()));
        });
    }
}
