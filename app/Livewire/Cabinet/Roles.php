<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Auth\Models\Role;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cabinet')]
final class Roles extends Component
{
    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public ?int $deletingId = null;

    public string $deletingName = '';

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'description']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $role = Role::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->slug = $role->slug;
        $this->description = $role->description ?? '';
        $this->showModal = true;
    }

    /**
     * Автогенерация slug из названия.
     */
    public function updatedName(): void
    {
        if (!$this->editingId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash',
            'description' => 'nullable|string|max:2000',
        ]);

        $orgId = auth()->user()->organization_id;

        if ($this->editingId) {
            $role = Role::where('organization_id', $orgId)->findOrFail($this->editingId);
            $role->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description ?: null,
            ]);
        } else {
            Role::create([
                'organization_id' => $orgId,
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description ?: null,
                'is_system' => false,
            ]);
        }

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $role = Role::where('organization_id', auth()->user()->organization_id)
            ->withCount('users')
            ->findOrFail($id);

        $this->deletingId = $role->id;
        $this->deletingName = $role->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!$this->deletingId) {
            return;
        }

        $role = Role::where('organization_id', auth()->user()->organization_id)
            ->withCount('users')
            ->findOrFail($this->deletingId);

        if ($role->users_count > 0) {
            $this->addError('delete', 'Невозможно удалить должность: к ней привязаны сотрудники.');

            return;
        }

        if ($role->is_system) {
            $this->addError('delete', 'Системные должности нельзя удалять.');

            return;
        }

        $role->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $roles = Role::where('organization_id', $orgId)
            ->withCount('users')
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return view('livewire.cabinet.roles', compact('roles'));
    }
}
