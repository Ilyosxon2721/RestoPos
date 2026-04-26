<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Marketing;

use App\Domain\Customer\Models\Promotion;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Promotions extends Component
{
    use WithPagination;

    public string $search = '';

    // Модалка создания/редактирования
    public bool $showModal = false;

    public ?int $editingId = null;

    // Поля формы
    public string $name = '';

    public string $description = '';

    public string $type = 'discount';

    public string $discountType = 'percent';

    public string $discountValue = '0';

    public string $startDate = '';

    public string $endDate = '';

    public bool $isActive = true;

    public array $activeDays = [];

    public string $activeHoursFrom = '';

    public string $activeHoursTo = '';

    public string $minOrderAmount = '0';

    public string $maxDiscountAmount = '';

    public string $promoCode = '';

    public string $usageLimit = '';

    // Модалка подтверждения удаления
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    protected array $typeLabels = [
        'discount' => 'Скидка',
        'bonus_multiply' => 'Множитель бонусов',
        'gift' => 'Подарок',
        'combo' => 'Комбо',
        'happy_hour' => 'Счастливые часы',
        'buy_x_get_y' => 'Купи X — получи Y',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset([
            'editingId', 'name', 'description', 'discountValue',
            'startDate', 'endDate', 'activeHoursFrom', 'activeHoursTo',
            'minOrderAmount', 'maxDiscountAmount', 'promoCode', 'usageLimit',
        ]);
        $this->type = 'discount';
        $this->discountType = 'percent';
        $this->isActive = true;
        $this->activeDays = [];
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $promo = Promotion::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        $this->editingId = $promo->id;
        $this->name = $promo->name;
        $this->description = $promo->description ?? '';
        $this->type = $promo->type;
        $this->discountType = $promo->discount_type ?? 'percent';
        $this->discountValue = (string) ($promo->discount_value ?? '0');
        $this->startDate = $promo->start_date ? $promo->start_date->format('Y-m-d') : '';
        $this->endDate = $promo->end_date ? $promo->end_date->format('Y-m-d') : '';
        $this->isActive = $promo->is_active;
        $this->activeDays = $promo->active_days ?? [];
        $this->activeHoursFrom = $promo->active_hours_from ?? '';
        $this->activeHoursTo = $promo->active_hours_to ?? '';
        $this->minOrderAmount = (string) ($promo->min_order_amount ?? '0');
        $this->maxDiscountAmount = (string) ($promo->max_discount_amount ?? '');
        $this->promoCode = $promo->promo_code ?? '';
        $this->usageLimit = (string) ($promo->usage_limit ?? '');

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:discount,bonus_multiply,gift,combo,happy_hour,buy_x_get_y',
            'discountType' => 'nullable|in:percent,fixed',
            'discountValue' => 'nullable|numeric|min:0',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'activeDays' => 'nullable|array',
            'activeHoursFrom' => 'nullable|date_format:H:i',
            'activeHoursTo' => 'nullable|date_format:H:i',
            'minOrderAmount' => 'nullable|numeric|min:0',
            'maxDiscountAmount' => 'nullable|numeric|min:0',
            'promoCode' => 'nullable|string|max:50',
            'usageLimit' => 'nullable|integer|min:0',
        ]);

        $orgId = auth()->user()->organization_id;

        $data = [
            'organization_id' => $orgId,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'type' => $this->type,
            'discount_type' => $this->discountType ?: null,
            'discount_value' => $this->discountValue ? (float) $this->discountValue : null,
            'start_date' => $this->startDate ?: null,
            'end_date' => $this->endDate ?: null,
            'is_active' => $this->isActive,
            'active_days' => !empty($this->activeDays) ? $this->activeDays : null,
            'active_hours_from' => $this->activeHoursFrom ?: null,
            'active_hours_to' => $this->activeHoursTo ?: null,
            'min_order_amount' => $this->minOrderAmount ? (float) $this->minOrderAmount : null,
            'max_discount_amount' => $this->maxDiscountAmount ? (float) $this->maxDiscountAmount : null,
            'promo_code' => $this->promoCode ?: null,
            'usage_limit' => $this->usageLimit ? (int) $this->usageLimit : null,
        ];

        if ($this->editingId) {
            $promo = Promotion::where('organization_id', $orgId)->findOrFail($this->editingId);
            $promo->update($data);
        } else {
            Promotion::create($data);
        }

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $promo = Promotion::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        $this->deletingId = $promo->id;
        $this->deletingName = $promo->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!$this->deletingId) {
            return;
        }

        Promotion::where('organization_id', auth()->user()->organization_id)
            ->where('id', $this->deletingId)
            ->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function toggleActive(int $id): void
    {
        $promo = Promotion::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        $promo->update(['is_active' => !$promo->is_active]);
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $promotions = Promotion::where('organization_id', $orgId)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.marketing.promotions', [
            'promotions' => $promotions,
            'typeLabels' => $this->typeLabels,
        ]);
    }
}
