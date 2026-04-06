<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Settings;

use App\Domain\Organization\Models\Organization;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Настройки доставки — включение, мин. сумма, время, стоимость, порог бесплатной доставки.
 */
#[Layout('components.layouts.cabinet')]
final class Delivery extends Component
{
    public bool $enabled = false;
    public float $minimumOrderAmount = 0;
    public int $defaultDeliveryTime = 60;
    public float $deliveryFee = 0;
    public float $freeDeliveryThreshold = 0;

    public function mount(): void
    {
        $org = $this->getOrganization();

        $this->enabled = (bool) $org->getSetting('delivery.enabled', config('forris.features.delivery', true));
        $this->minimumOrderAmount = (float) $org->getSetting('delivery.minimum_order_amount', 0);
        $this->defaultDeliveryTime = (int) $org->getSetting('delivery.default_delivery_time', 60);
        $this->deliveryFee = (float) $org->getSetting('delivery.delivery_fee', 0);
        $this->freeDeliveryThreshold = (float) $org->getSetting('delivery.free_delivery_threshold', 0);
    }

    public function save(): void
    {
        $this->validate([
            'minimumOrderAmount' => 'required|numeric|min:0',
            'defaultDeliveryTime' => 'required|integer|min:1|max:1440',
            'deliveryFee' => 'required|numeric|min:0',
            'freeDeliveryThreshold' => 'required|numeric|min:0',
        ]);

        $org = $this->getOrganization();
        $settings = $org->settings ?? [];

        data_set($settings, 'delivery.enabled', $this->enabled);
        data_set($settings, 'delivery.minimum_order_amount', $this->minimumOrderAmount);
        data_set($settings, 'delivery.default_delivery_time', $this->defaultDeliveryTime);
        data_set($settings, 'delivery.delivery_fee', $this->deliveryFee);
        data_set($settings, 'delivery.free_delivery_threshold', $this->freeDeliveryThreshold);

        $org->update(['settings' => $settings]);

        session()->flash('success', 'Настройки сохранены');
    }

    public function render()
    {
        return view('livewire.cabinet.settings.delivery');
    }

    private function getOrganization(): Organization
    {
        return auth()->user()->organization;
    }
}
