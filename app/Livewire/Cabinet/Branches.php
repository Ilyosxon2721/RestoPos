<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Organization\Models\Branch;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.cabinet')]
class Branches extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $address = '';
    public string $phone = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'address', 'phone']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $branch = Branch::findOrFail($id);
        $this->editingId = $branch->id;
        $this->name = $branch->name;
        $this->address = $branch->address ?? '';
        $this->phone = $branch->phone ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Branch::updateOrCreate(
            ['id' => $this->editingId],
            [
                'organization_id' => auth()->user()->organization_id,
                'name' => $this->name,
                'address' => $this->address ?: null,
                'phone' => $this->phone ?: null,
            ]
        );

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Branch::where('organization_id', auth()->user()->organization_id)->findOrFail($id)->delete();
    }

    public function render()
    {
        $branches = Branch::where('organization_id', auth()->user()->organization_id)->latest()->get();
        return view('livewire.cabinet.branches', compact('branches'));
    }
}
