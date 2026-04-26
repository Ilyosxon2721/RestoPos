<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Settings;

use App\Domain\Infrastructure\Models\ActivityLog;
use App\Domain\Organization\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Настройки безопасности — PIN-код, таймаут сессии, журнал активности, требования к паролю.
 */
#[Layout('components.layouts.cabinet')]
final class Security extends Component
{
    public bool $requirePin = false;

    public int $sessionTimeout = 30;

    public function mount(): void
    {
        $org = $this->getOrganization();

        $this->requirePin = (bool) $org->getSetting('security.require_pin', false);
        $this->sessionTimeout = (int) $org->getSetting('security.session_timeout', 30);
    }

    public function save(): void
    {
        $this->validate([
            'sessionTimeout' => 'required|integer|min:5|max:480',
        ]);

        $org = $this->getOrganization();
        $settings = $org->settings ?? [];

        data_set($settings, 'security.require_pin', $this->requirePin);
        data_set($settings, 'security.session_timeout', $this->sessionTimeout);

        $org->update(['settings' => $settings]);

        session()->flash('success', 'Настройки сохранены');
    }

    public function render()
    {
        // Последние 20 записей входа
        $loginLogs = ActivityLog::where('organization_id', auth()->user()->organization_id)
            ->where('action', 'login')
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('livewire.cabinet.settings.security', [
            'loginLogs' => $loginLogs,
        ]);
    }

    private function getOrganization(): Organization
    {
        return auth()->user()->organization;
    }
}
