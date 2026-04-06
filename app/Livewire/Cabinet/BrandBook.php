<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cabinet')]
final class BrandBook extends Component
{
    public function render()
    {
        return view('livewire.cabinet.brand-book');
    }
}
