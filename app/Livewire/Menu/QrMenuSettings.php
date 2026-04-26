<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Models\QrMenu;
use App\Domain\Organization\Models\Branch;
use App\Support\Traits\ResolvesLayout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class QrMenuSettings extends Component
{
    use ResolvesLayout;

    public Collection $branches;

    public ?QrMenu $qrMenu = null;

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = 'Основное меню';

    #[Validate('nullable|integer|exists:branches,id')]
    public ?int $branchId = null;

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('nullable|url|max:500')]
    public string $logo = '';

    #[Validate('required|string|max:7')]
    public string $backgroundColor = '#ffffff';

    #[Validate('required|string|max:7')]
    public string $primaryColor = '#4f46e5';

    #[Validate('required|string|max:10')]
    public string $currency = 'сум';

    public bool $showImages = true;

    public bool $showDescriptions = true;

    public bool $showCalories = false;

    public bool $isActive = true;

    public function mount(): void
    {
        $this->loadBranches();
        $this->loadQrMenus();
    }

    public function loadBranches(): void
    {
        $organizationId = auth()->user()->organization_id;

        $this->branches = Branch::query()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function loadQrMenus(): void
    {
        // Загрузка не нужна — используем computed property
    }

    public function getQrMenusProperty()
    {
        $organizationId = auth()->user()->organization_id;

        return QrMenu::query()
            ->where('organization_id', $organizationId)
            ->with('branch')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $qrMenu = QrMenu::findOrFail($id);

        $this->editingId = $qrMenu->id;
        $this->name = $qrMenu->name;
        $this->branchId = $qrMenu->branch_id;
        $this->description = $qrMenu->description ?? '';
        $this->logo = $qrMenu->logo ?? '';
        $this->backgroundColor = $qrMenu->background_color;
        $this->primaryColor = $qrMenu->primary_color;
        $this->currency = $qrMenu->currency;
        $this->showImages = $qrMenu->show_images;
        $this->showDescriptions = $qrMenu->show_descriptions;
        $this->showCalories = $qrMenu->show_calories;
        $this->isActive = $qrMenu->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $organizationId = auth()->user()->organization_id;

        $data = [
            'organization_id' => $organizationId,
            'branch_id' => $this->branchId,
            'name' => $this->name,
            'description' => $this->description !== '' ? $this->description : null,
            'logo' => $this->logo !== '' ? $this->logo : null,
            'background_color' => $this->backgroundColor,
            'primary_color' => $this->primaryColor,
            'currency' => $this->currency,
            'show_images' => $this->showImages,
            'show_descriptions' => $this->showDescriptions,
            'show_calories' => $this->showCalories,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            $qrMenu = QrMenu::findOrFail($this->editingId);
            $qrMenu->update($data);
        } else {
            $data['slug'] = $this->generateUniqueSlug($this->name);
            QrMenu::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        QrMenu::findOrFail($id)->delete();
    }

    public function toggleActive(int $id): void
    {
        $qrMenu = QrMenu::findOrFail($id);
        $qrMenu->update(['is_active' => !$qrMenu->is_active]);
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'menu';
        $slug = $base.'-'.Str::random(6);

        while (QrMenu::withoutGlobalScope('organization')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::random(6);
        }

        return $slug;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = 'Основное меню';
        $this->branchId = null;
        $this->description = '';
        $this->logo = '';
        $this->backgroundColor = '#ffffff';
        $this->primaryColor = '#4f46e5';
        $this->currency = 'сум';
        $this->showImages = true;
        $this->showDescriptions = true;
        $this->showCalories = false;
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.menu.qr-menu-settings')
            ->layout($this->resolveLayout());
    }
}
