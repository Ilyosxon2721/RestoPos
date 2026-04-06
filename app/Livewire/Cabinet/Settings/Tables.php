<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Settings;

use App\Domain\Organization\Models\Organization;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Настройки столов — бронирование, автоосвобождение, длительность брони, обязательный выбор стола.
 */
#[Layout('components.layouts.cabinet')]
final class Tables extends Component
{
    public bool $reservationsEnabled = false;
    public int $autoReleaseMinutes = 120;
    public int $defaultReservationDuration = 90;
    public bool $requireTableForDineIn = true;

    public function mount(): void
    {
        $org = $this->getOrganization();

        $this->reservationsEnabled = (bool) $org->getSetting('tables.reservations_enabled', config('forris.features.reservations', true));
        $this->autoReleaseMinutes = (int) $org->getSetting('tables.auto_release_minutes', 120);
        $this->defaultReservationDuration = (int) $org->getSetting('tables.default_reservation_duration', 90);
        $this->requireTableForDineIn = (bool) $org->getSetting('tables.require_table_for_dine_in', true);
    }

    public function save(): void
    {
        $this->validate([
            'autoReleaseMinutes' => 'required|integer|min:5|max:1440',
            'defaultReservationDuration' => 'required|integer|min:15|max:480',
        ]);

        $org = $this->getOrganization();
        $settings = $org->settings ?? [];

        data_set($settings, 'tables.reservations_enabled', $this->reservationsEnabled);
        data_set($settings, 'tables.auto_release_minutes', $this->autoReleaseMinutes);
        data_set($settings, 'tables.default_reservation_duration', $this->defaultReservationDuration);
        data_set($settings, 'tables.require_table_for_dine_in', $this->requireTableForDineIn);

        $org->update(['settings' => $settings]);

        session()->flash('success', 'Настройки сохранены');
    }

    public function render()
    {
        return view('livewire.cabinet.settings.tables');
    }

    private function getOrganization(): Organization
    {
        return auth()->user()->organization;
    }
}
