<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Domain\Store\Models\StoreSettings;
use Livewire\Component;

final class Favorites extends Component
{
    public StoreSettings $store;

    public function mount(string $slug): void
    {
        $this->store = StoreSettings::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.store.favorites')
            ->layout('components.layouts.store', ['store' => $this->store]);
    }
}
