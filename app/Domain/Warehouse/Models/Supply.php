<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supply extends Model
{
    protected $fillable = [
        'warehouse_id',
        'supplier_id',
        'user_id',
        'number',
        'document_number',
        'document_date',
        'total_amount',
        'status',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'document_date' => 'date',
            'received_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplyItem::class);
    }

    public function calculateTotal(): void
    {
        $this->total_amount = $this->items()->sum(\DB::raw('quantity * price'));
        $this->save();
    }
}
