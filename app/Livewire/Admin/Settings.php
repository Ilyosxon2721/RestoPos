<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Settings extends Component
{
    public string $siteName = 'FORRIS POS';

    public string $supportEmail = '';

    public bool $registrationEnabled = true;

    public int $trialDays = 14;

    public function save(): void
    {
        $this->validate([
            'siteName' => 'required|string|max:255',
            'supportEmail' => 'nullable|email|max:255',
            'trialDays' => 'required|integer|min:0|max:90',
        ]);

        session()->flash('success', 'Настройки сохранены');
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
