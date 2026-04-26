<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Settings;

use App\Domain\Organization\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Настройки заказов — автопринятие, формат номера, тип/источник по умолчанию, кассовая смена.
 */
#[Layout('components.layouts.cabinet')]
final class Orders extends Component
{
    public bool $autoAccept = false;

    public string $numberFormat = '';

    public string $defaultType = 'dine_in';

    public string $defaultSource = 'pos';

    public bool $requireCashShift = true;

    public function mount(): void
    {
        $org = $this->getOrganization();

        $this->autoAccept = (bool) $org->getSetting('orders.auto_accept', config('forris.order.auto_accept', false));
        $this->numberFormat = (string) $org->getSetting('orders.number_format', config('forris.order.number_format', 'Ymd-{sequence}'));
        $this->defaultType = (string) $org->getSetting('orders.default_type', config('forris.order.default_type', 'dine_in'));
        $this->defaultSource = (string) $org->getSetting('orders.default_source', config('forris.order.default_source', 'pos'));
        $this->requireCashShift = (bool) $org->getSetting('orders.require_cash_shift', config('forris.cash_shift.require_open_shift', true));
    }

    public function save(): void
    {
        $this->validate([
            'numberFormat' => 'required|string|max:100',
            'defaultType' => 'required|in:dine_in,takeaway,delivery',
            'defaultSource' => 'required|in:pos,online,phone',
        ]);

        $org = $this->getOrganization();
        $settings = $org->settings ?? [];

        data_set($settings, 'orders.auto_accept', $this->autoAccept);
        data_set($settings, 'orders.number_format', $this->numberFormat);
        data_set($settings, 'orders.default_type', $this->defaultType);
        data_set($settings, 'orders.default_source', $this->defaultSource);
        data_set($settings, 'orders.require_cash_shift', $this->requireCashShift);

        $org->update(['settings' => $settings]);

        session()->flash('success', 'Настройки сохранены');
    }

    public function render()
    {
        return view('livewire.cabinet.settings.orders');
    }

    private function getOrganization(): Organization
    {
        return auth()->user()->organization;
    }
}
