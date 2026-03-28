<?php

declare(strict_types=1);

namespace App\Domain\Infrastructure\Models;

use App\Domain\Organization\Models\Branch;
use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Printer extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'type',
        'connection_type',
        'ip_address',
        'port',
        'paper_width',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'port' => 'integer',
        'paper_width' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getConnectionString(): string
    {
        if ($this->connection_type === 'network') {
            return "tcp://{$this->ip_address}:{$this->port}";
        }

        return '';
    }

    /**
     * Check if this is a receipt printer.
     */
    public function isReceiptPrinter(): bool
    {
        return $this->type === 'receipt';
    }

    /**
     * Check if this is a kitchen printer.
     */
    public function isKitchenPrinter(): bool
    {
        return $this->type === 'kitchen';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
