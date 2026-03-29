<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.cabinet')]
class Reports extends Component
{
    public function render()
    {
        return view('livewire.cabinet.reports');
    }
}
