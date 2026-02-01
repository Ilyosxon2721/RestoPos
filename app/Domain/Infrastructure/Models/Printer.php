<?php

namespace App\Domain\Infrastructure\Models;

use App\Domain\Organization\Models\Branch;
use App\Support\Traits\BelongsToBranch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Printer extends Model
{
    use HasUuid, BelongsToBranch, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'type',
        'connection_type',
        'ip_address',
        'port',
        'usb_path',
        'paper_width',
        'is_default',
        'print_kitchen_tickets',
        'print_receipts',
        'print_reports',
        'workshop_ids',
        'is_active',
    ];

    protected $casts = [
        'port' => 'integer',
        'paper_width' => 'integer',
        'is_default' => 'boolean',
        'print_kitchen_tickets' => 'boolean',
        'print_receipts' => 'boolean',
        'print_reports' => 'boolean',
        'workshop_ids' => 'array',
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

        return $this->usb_path ?? '';
    }

    public function isPosReceipt(): bool
    {
        return $this->type === 'receipt' && $this->print_receipts;
    }

    public function isKitchenPrinter(): bool
    {
        return $this->type === 'kitchen' && $this->print_kitchen_tickets;
    }

    public function shouldPrintForWorkshop(int $workshopId): bool
    {
        if (!$this->print_kitchen_tickets) {
            return false;
        }

        if (empty($this->workshop_ids)) {
            return true;
        }

        return in_array($workshopId, $this->workshop_ids);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForReceipts($query)
    {
        return $query->where('print_receipts', true);
    }

    public function scopeForKitchen($query)
    {
        return $query->where('print_kitchen_tickets', true);
    }
}
