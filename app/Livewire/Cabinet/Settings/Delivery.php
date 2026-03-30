<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.cabinet')]
final class Delivery extends Component
{
    public function render()
    {
        return view('livewire.cabinet.settings.delivery');
    }
}