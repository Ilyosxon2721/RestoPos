<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Models\Tax;
use App\Support\Traits\ResolvesLayout;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Taxes extends Component
{
    use ResolvesLayout;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $rate = '0';

    public string $type = 'vat';

    public bool $isDefault = false;

    public bool $isActive = true;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'rate', 'type', 'isDefault', 'isActive']);
        $this->rate = '0';
        $this->type = 'vat';
        $this->isActive = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $tax = Tax::findOrFail($id);
        $this->editingId = $tax->id;
        $this->name = $tax->name;
        $this->rate = (string) $tax->rate;
        $this->type = $tax->type;
        $this->isDefault = (bool) $tax->is_default;
        $this->isActive = (bool) $tax->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:vat,turnover,none',
        ]);

        $orgId = auth()->user()->organization_id;

        if ($this->isDefault) {
            Tax::where('organization_id', $orgId)->update(['is_default' => false]);
        }

        $payload = [
            'organization_id' => $orgId,
            'name' => $this->name,
            'rate' => (float) $this->rate,
            'type' => $this->type,
            'is_default' => $this->isDefault,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            Tax::where('id', $this->editingId)->update($payload);
        } else {
            Tax::create($payload);
        }

        $this->showModal = false;
        session()->flash('success', 'Налог сохранён');
    }

    public function delete(int $id): void
    {
        Tax::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->delete();
    }

    #[Computed]
    public function taxes()
    {
        return Tax::where('organization_id', auth()->user()->organization_id)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.menu.taxes')->layout($this->resolveLayout());
    }
}
