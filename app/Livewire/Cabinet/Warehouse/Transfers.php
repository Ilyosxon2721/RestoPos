<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.cabinet')]
final class Transfers extends Component
{
    public function render()
    {
        return view('livewire.cabinet.warehouse.transfers');
    }
}