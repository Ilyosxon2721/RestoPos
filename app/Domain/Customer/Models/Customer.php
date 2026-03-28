<?php

declare(strict_types=1);

namespace App\Domain\Customer\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, HasUuid, BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'customer_group_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'birth_date',
        'gender',
        'loyalty_card_number',
        'bonus_balance',
        'total_spent',
        'total_orders',
        'discount_percent',
        'notes',
        'tags',
        'last_visit_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'bonus_balance' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'total_orders' => 'integer',
        'discount_percent' => 'decimal:2',
        'tags' => 'array',
        'last_visit_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function bonusTransactions(): HasMany
    {
        return $this->hasMany(BonusTransaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(\App\Domain\Order\Models\Order::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function addBonus(float $amount, string $type, ?string $description = null): BonusTransaction
    {
        $this->increment('bonus_balance', $amount);

        return $this->bonusTransactions()->create([
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'balance_after' => $this->bonus_balance,
        ]);
    }

    public function useBonus(float $amount, ?string $description = null): BonusTransaction
    {
        if ($amount > $this->bonus_balance) {
            throw new \Exception('Недостаточно бонусов.');
        }

        $this->decrement('bonus_balance', $amount);

        return $this->bonusTransactions()->create([
            'amount' => -$amount,
            'type' => 'spend',
            'description' => $description,
            'balance_after' => $this->bonus_balance,
        ]);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('phone', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    protected static function newFactory(): \Database\Factories\CustomerFactory
    {
        return \Database\Factories\CustomerFactory::new();
    }

}
