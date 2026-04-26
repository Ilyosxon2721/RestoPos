<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Marketing;

use App\Domain\Customer\Models\BonusTransaction;
use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cabinet')]
final class Loyalty extends Component
{
    // Настройки программы лояльности
    public bool $loyaltyEnabled = false;

    public string $earnRate = '5';

    public string $maxPayPercent = '50';

    public string $welcomeBonus = '0';

    public string $bonusExpireDays = '0';

    public bool $saved = false;

    public function mount(): void
    {
        $org = Organization::find(auth()->user()->organization_id);

        $this->loyaltyEnabled = (bool) $org->getSetting('loyalty.enabled', false);
        $this->earnRate = (string) $org->getSetting('loyalty.earn_rate', 5);
        $this->maxPayPercent = (string) $org->getSetting('loyalty.max_pay_percent', 50);
        $this->welcomeBonus = (string) $org->getSetting('loyalty.welcome_bonus', 0);
        $this->bonusExpireDays = (string) $org->getSetting('loyalty.bonus_expire_days', 0);
    }

    public function save(): void
    {
        $this->validate([
            'earnRate' => 'required|numeric|min:0|max:100',
            'maxPayPercent' => 'required|numeric|min:0|max:100',
            'welcomeBonus' => 'required|numeric|min:0',
            'bonusExpireDays' => 'required|integer|min:0',
        ]);

        $org = Organization::find(auth()->user()->organization_id);

        $org->setSetting('loyalty.enabled', $this->loyaltyEnabled);
        $org->setSetting('loyalty.earn_rate', (float) $this->earnRate);
        $org->setSetting('loyalty.max_pay_percent', (float) $this->maxPayPercent);
        $org->setSetting('loyalty.welcome_bonus', (float) $this->welcomeBonus);
        $org->setSetting('loyalty.bonus_expire_days', (int) $this->bonusExpireDays);

        $this->saved = true;
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        // Статистика по бонусам
        $totalIssued = BonusTransaction::whereHas('customer', fn ($q) => $q->where('organization_id', $orgId))
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalSpent = abs((float) BonusTransaction::whereHas('customer', fn ($q) => $q->where('organization_id', $orgId))
            ->where('amount', '<', 0)
            ->sum('amount'));

        $totalBalance = Customer::where('organization_id', $orgId)->sum('bonus_balance');

        $customersWithBonuses = Customer::where('organization_id', $orgId)
            ->where('bonus_balance', '>', 0)
            ->count();

        return view('livewire.cabinet.marketing.loyalty', compact(
            'totalIssued',
            'totalSpent',
            'totalBalance',
            'customersWithBonuses',
        ));
    }
}
