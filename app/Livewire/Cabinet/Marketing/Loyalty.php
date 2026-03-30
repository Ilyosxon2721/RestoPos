<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Marketing;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.cabinet')]
final class Loyalty extends Component
{
    public function render()
    {
        return view('livewire.cabinet.marketing.loyalty');
    }
}