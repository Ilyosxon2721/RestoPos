<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Models\PreparationMethod;
use App\Support\Traits\ResolvesLayout;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class PreparationMethods extends Component
{
    use ResolvesLayout;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $defaultLossPercent = '0';

    public bool $isActive = true;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'defaultLossPercent', 'isActive']);
        $this->defaultLossPercent = '0';
        $this->isActive = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $pm = PreparationMethod::findOrFail($id);
        $this->editingId = $pm->id;
        $this->name = $pm->name;
        $this->defaultLossPercent = (string) $pm->default_loss_percent;
        $this->isActive = (bool) $pm->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'defaultLossPercent' => 'required|numeric|min:0|max:100',
        ]);

        $payload = [
            'organization_id' => auth()->user()->organization_id,
            'name' => $this->name,
            'default_loss_percent' => (float) $this->defaultLossPercent,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            PreparationMethod::where('id', $this->editingId)->update($payload);
        } else {
            PreparationMethod::create($payload);
        }

        $this->showModal = false;
        session()->flash('success', 'Метод приготовления сохранён');
    }

    public function delete(int $id): void
    {
        PreparationMethod::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->delete();
    }

    #[Computed]
    public function methods()
    {
        return PreparationMethod::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.menu.preparation-methods')->layout($this->resolveLayout());
    }
}
