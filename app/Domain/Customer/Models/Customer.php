<?php

declare(strict_types=1);

namespace App\Domain\Customer\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory, HasUuid, BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'customer_group_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'verification_code',
        'verification_code_sent_at',
        'is_registered',
        'avatar',
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

    protected $hidden = [
        'password',
        'verification_code',
        'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'password' => 'hashed',
        'verification_code_sent_at' => 'datetime',
        'is_registered' => 'boolean',
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

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\Menu\Models\Product::class,
            'customer_favorites',
            'customer_id',
            'product_id'
        )->withTimestamps();
    }

    public function defaultAddress(): ?CustomerAddress
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    /**
     * Генерация и отправка кода верификации.
     */
    public function generateVerificationCode(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'verification_code' => $code,
            'verification_code_sent_at' => now(),
        ]);

        return $code;
    }

    /**
     * Проверка кода верификации.
     */
    public function verifyCode(string $code): bool
    {
        if ($this->verification_code !== $code) {
            return false;
        }

        // Код действителен 5 минут
        if ($this->verification_code_sent_at->diffInMinutes(now()) > 5) {
            return false;
        }

        $this->update([
            'verification_code' => null,
            'verification_code_sent_at' => null,
            'is_registered' => true,
        ]);

        return true;
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
