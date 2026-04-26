<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Marketing;

use App\Domain\Customer\Models\CustomerGroup;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Groups extends Component
{
    use WithPagination;

    public string $search = '';

    // Модалка создания/редактирования
    public bool $showModal = false;

    public ?int $editingId = null;

    // Поля формы
    public string $name = '';

    public string $discountPercent = '0';

    public string $bonusEarnPercent = '0';

    public string $minSpentToJoin = '0';

    public string $color = '#6366f1';

    public string $description = '';

    public bool $isActive = true;

    // Модалка подтверждения удаления
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'discountPercent', 'bonusEarnPercent', 'minSpentToJoin', 'description']);
        $this->color = '#6366f1';
        $this->isActive = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $group = CustomerGroup::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        $this->editingId = $group->id;
        $this->name = $group->name;
        $this->discountPercent = (string) $group->discount_percent;
        $this->bonusEarnPercent = (string) $group->bonus_earn_percent;
        $this->minSpentToJoin = (string) $group->min_spent_to_join;
        $this->color = $group->color ?? '#6366f1';
        $this->description = $group->description ?? '';
        $this->isActive = $group->is_active ?? true;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'discountPercent' => 'required|numeric|min:0|max:100',
            'bonusEarnPercent' => 'required|numeric|min:0|max:100',
            'minSpentToJoin' => 'required|numeric|min:0',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $orgId = auth()->user()->organization_id;

        $data = [
            'organization_id' => $orgId,
            'name' => $this->name,
            'discount_percent' => (float) $this->discountPercent,
            'bonus_earn_percent' => (float) $this->bonusEarnPercent,
            'min_spent_to_join' => (float) $this->minSpentToJoin,
            'color' => $this->color,
            'description' => $this->description ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            $group = CustomerGroup::where('organization_id', $orgId)->findOrFail($this->editingId);
            $group->update($data);
        } else {
            CustomerGroup::create($data);
        }

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $group = CustomerGroup::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($id);

        $this->deletingId = $group->id;
        $this->deletingName = $group->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!$this->deletingId) {
            return;
        }

        $group = CustomerGroup::where('organization_id', auth()->user()->organization_id)
            ->findOrFail($this->deletingId);

        // Убираем клиентов из группы перед удалением
        $group->customers()->update(['customer_group_id' => null]);
        $group->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $groups = CustomerGroup::where('organization_id', $orgId)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('customers')
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.marketing.groups', compact('groups'));
    }
}
