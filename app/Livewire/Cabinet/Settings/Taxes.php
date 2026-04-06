<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Settings;

use App\Domain\Organization\Models\Organization;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Настройки налогов — НДС, ставка, включение в цену, сервисный сбор.
 */
#[Layout('components.layouts.cabinet')]
final class Taxes extends Component
{
    public bool $taxEnabled = false;
    public float $taxRate = 0;
    public bool $taxIncludedInPrice = true;
    public bool $serviceChargeEnabled = false;
    public float $serviceChargePercent = 10;

    public function mount(): void
    {
        $org = $this->getOrganization();

        $this->taxEnabled = (bool) $org->getSetting('tax.enabled', config('forris.tax.enabled', false));
        $this->taxRate = (float) $org->getSetting('tax.rate', config('forris.tax.rate', 0));
        $this->taxIncludedInPrice = (bool) $org->getSetting('tax.included_in_price', config('forris.tax.included_in_price', true));
        $this->serviceChargeEnabled = (bool) $org->getSetting('service_charge.enabled', config('forris.service_charge.enabled', false));
        $this->serviceChargePercent = (float) $org->getSetting('service_charge.percent', config('forris.service_charge.percent', 10));
    }

    public function save(): void
    {
        $this->validate([
            'taxRate' => 'required|numeric|min:0|max:100',
            'serviceChargePercent' => 'required|numeric|min:0|max:100',
        ]);

        $org = $this->getOrganization();
        $settings = $org->settings ?? [];

        data_set($settings, 'tax.enabled', $this->taxEnabled);
        data_set($settings, 'tax.rate', $this->taxRate);
        data_set($settings, 'tax.included_in_price', $this->taxIncludedInPrice);
        data_set($settings, 'service_charge.enabled', $this->serviceChargeEnabled);
        data_set($settings, 'service_charge.percent', $this->serviceChargePercent);

        $org->update(['settings' => $settings]);

        session()->flash('success', 'Настройки сохранены');
    }

    public function render()
    {
        return view('livewire.cabinet.settings.taxes');
    }

    private function getOrganization(): Organization
    {
        return auth()->user()->organization;
    }
}
