<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\Role;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
class Staff extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::where('organization_id', auth()->user()->organization_id)->findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);
    }

    public function render()
    {
        $users = User::where('organization_id', auth()->user()->organization_id)
            ->when($this->search, fn($q) => $q->where(fn($q2) => $q2->where('first_name', 'like', "%{$this->search}%")->orWhere('last_name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")))
            ->with('roles')
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.staff', compact('users'));
    }
}
