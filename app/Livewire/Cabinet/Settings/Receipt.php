<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Settings;

use App\Domain\Organization\Models\Organization;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Настройки чека — заголовок, подвал, логотип, автопечать чека и кухонного тикета.
 */
#[Layout('components.layouts.cabinet')]
final class Receipt extends Component
{
    public string $headerText = '';
    public string $footerText = '';
    public bool $showLogo = true;
    public bool $autoPrintReceipt = false;
    public bool $autoPrintKitchenTicket = true;

    public function mount(): void
    {
        $org = $this->getOrganization();

        $this->headerText = (string) $org->getSetting('receipt.header_text', $org->name ?? '');
        $this->footerText = (string) $org->getSetting('receipt.footer_text', 'Спасибо за визит!');
        $this->showLogo = (bool) $org->getSetting('receipt.show_logo', true);
        $this->autoPrintReceipt = (bool) $org->getSetting('receipt.auto_print_receipt', config('forris.printing.auto_print_receipt', false));
        $this->autoPrintKitchenTicket = (bool) $org->getSetting('receipt.auto_print_kitchen_ticket', config('forris.printing.auto_print_kitchen', true));
    }

    public function save(): void
    {
        $this->validate([
            'headerText' => 'nullable|string|max:500',
            'footerText' => 'nullable|string|max:500',
        ]);

        $org = $this->getOrganization();
        $settings = $org->settings ?? [];

        data_set($settings, 'receipt.header_text', $this->headerText);
        data_set($settings, 'receipt.footer_text', $this->footerText);
        data_set($settings, 'receipt.show_logo', $this->showLogo);
        data_set($settings, 'receipt.auto_print_receipt', $this->autoPrintReceipt);
        data_set($settings, 'receipt.auto_print_kitchen_ticket', $this->autoPrintKitchenTicket);

        $org->update(['settings' => $settings]);

        session()->flash('success', 'Настройки сохранены');
    }

    public function render()
    {
        return view('livewire.cabinet.settings.receipt');
    }

    private function getOrganization(): Organization
    {
        return auth()->user()->organization;
    }
}
