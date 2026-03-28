<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class General extends Component
{
    #[Validate('required|string|max:255')]
    public string $organizationName = '';

    #[Validate('required|string|max:255')]
    public string $branchName = '';

    #[Validate('required|string|max:500')]
    public string $address = '';

    #[Validate('required|string|max:20')]
    public string $phone = '';

    #[Validate('required|string|max:10')]
    public string $currency = 'RUB';

    #[Validate('required|string|max:50')]
    public string $timezone = 'Europe/Moscow';

    #[Validate('required|numeric|min:0|max:100')]
    public float $taxRate = 0;

    #[Validate('required|numeric|min:0|max:100')]
    public float $serviceChargePercent = 0;

    public function mount(): void
    {
        $organization = Organization::first();
        $branch = Branch::first();

        if ($organization) {
            $this->organizationName = $organization->name ?? '';
            $this->phone = $organization->phone ?? '';
            $this->currency = $organization->currency ?? 'RUB';
            $this->timezone = $organization->timezone ?? 'Europe/Moscow';
            $this->taxRate = (float) ($organization->tax_rate ?? 0);
            $this->serviceChargePercent = (float) ($organization->service_charge_percent ?? 0);
        }

        if ($branch) {
            $this->branchName = $branch->name ?? '';
            $this->address = $branch->address ?? '';

            if (! $this->phone && $branch->phone) {
                $this->phone = $branch->phone;
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $organization = Organization::first();

        if ($organization) {
            $organization->update([
                'name' => $this->organizationName,
                'phone' => $this->phone,
                'currency' => $this->currency,
                'timezone' => $this->timezone,
                'tax_rate' => $this->taxRate,
                'service_charge_percent' => $this->serviceChargePercent,
            ]);
        } else {
            Organization::create([
                'name' => $this->organizationName,
                'phone' => $this->phone,
                'currency' => $this->currency,
                'timezone' => $this->timezone,
                'tax_rate' => $this->taxRate,
                'service_charge_percent' => $this->serviceChargePercent,
            ]);
        }

        $branch = Branch::first();

        if ($branch) {
            $branch->update([
                'name' => $this->branchName,
                'address' => $this->address,
            ]);
        } else {
            Branch::create([
                'name' => $this->branchName,
                'address' => $this->address,
                'organization_id' => Organization::first()?->id,
            ]);
        }

        session()->flash('success', 'Настройки успешно сохранены.');
    }

    public function render()
    {
        return view('livewire.settings.general');
    }
}
