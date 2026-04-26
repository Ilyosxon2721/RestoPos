<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cabinet')]
class Settings extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public function mount(): void
    {
        $org = auth()->user()->organization;
        $this->name = $org->name ?? '';
        $this->phone = $org->phone ?? '';
        $this->email = $org->email ?? '';
        $this->address = $org->address ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        auth()->user()->organization->update([
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'address' => $this->address ?: null,
        ]);

        session()->flash('success', 'Настройки сохранены');
    }

    public function render()
    {
        return view('livewire.cabinet.settings');
    }
}
