<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Menu\Models\Product;
use App\Domain\Store\Models\StoreBanner;
use App\Domain\Store\Models\StoreSettings;
use App\Support\Traits\ResolvesLayout;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class StoreSettingsPage extends Component
{
    use ResolvesLayout;

    public ?StoreSettings $storeSettings = null;

    #[Validate('required|string|max:255')]
    public string $storeName = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('nullable|url|max:500')]
    public string $logo = '';

    #[Validate('nullable|url|max:500')]
    public string $coverImage = '';

    #[Validate('required|string|max:7')]
    public string $primaryColor = '#10b981';

    #[Validate('required|string|max:10')]
    public string $currency = 'сум';

    public ?int $drinkOfDayProductId = null;

    public bool $deliveryEnabled = true;

    public bool $pickupEnabled = true;

    public string $minOrderAmount = '0';

    public string $phone = '';

    public string $instagram = '';

    public string $telegram = '';

    public string $workingHoursText = '';

    public bool $isActive = true;

    // Баннеры
    public bool $showBannerModal = false;

    public ?int $editingBannerId = null;

    public string $bannerTitle = '';

    public string $bannerDescription = '';

    public string $bannerImage = '';

    public string $bannerLink = '';

    public bool $bannerIsActive = true;

    public bool $saved = false;

    public function mount(): void
    {
        $organizationId = auth()->user()->organization_id;
        $this->storeSettings = StoreSettings::where('organization_id', $organizationId)->first();

        if ($this->storeSettings) {
            $this->storeName = $this->storeSettings->store_name ?? '';
            $this->description = $this->storeSettings->description ?? '';
            $this->logo = $this->storeSettings->logo ?? '';
            $this->coverImage = $this->storeSettings->cover_image ?? '';
            $this->primaryColor = $this->storeSettings->primary_color;
            $this->currency = $this->storeSettings->currency;
            $this->drinkOfDayProductId = $this->storeSettings->drink_of_day_product_id;
            $this->deliveryEnabled = $this->storeSettings->delivery_enabled;
            $this->pickupEnabled = $this->storeSettings->pickup_enabled;
            $this->minOrderAmount = (string) $this->storeSettings->min_order_amount;
            $this->phone = $this->storeSettings->phone ?? '';
            $this->instagram = $this->storeSettings->instagram ?? '';
            $this->telegram = $this->storeSettings->telegram ?? '';
            $this->workingHoursText = $this->storeSettings->working_hours_text ?? '';
            $this->isActive = $this->storeSettings->is_active;
        } else {
            $org = auth()->user()->organization;
            $this->storeName = $org->name ?? '';
        }
    }

    public function saveSettings(): void
    {
        $this->validate();

        $organizationId = auth()->user()->organization_id;

        $data = [
            'organization_id' => $organizationId,
            'store_name' => $this->storeName,
            'description' => $this->description ?: null,
            'logo' => $this->logo ?: null,
            'cover_image' => $this->coverImage ?: null,
            'primary_color' => $this->primaryColor,
            'currency' => $this->currency,
            'drink_of_day_product_id' => $this->drinkOfDayProductId,
            'delivery_enabled' => $this->deliveryEnabled,
            'pickup_enabled' => $this->pickupEnabled,
            'min_order_amount' => (float) $this->minOrderAmount,
            'phone' => $this->phone ?: null,
            'instagram' => $this->instagram ?: null,
            'telegram' => $this->telegram ?: null,
            'working_hours_text' => $this->workingHoursText ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->storeSettings) {
            $this->storeSettings->update($data);
        } else {
            $data['slug'] = Str::slug($this->storeName) ?: 'store';
            $data['slug'] .= '-'.Str::random(6);
            $this->storeSettings = StoreSettings::create($data);
        }

        $this->saved = true;
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->where('organization_id', auth()->user()->organization_id)
            ->where('is_available', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getBannersProperty()
    {
        return StoreBanner::query()
            ->where('organization_id', auth()->user()->organization_id)
            ->orderBy('sort_order')
            ->get();
    }

    public function createBanner(): void
    {
        $this->resetBannerForm();
        $this->showBannerModal = true;
    }

    public function editBanner(int $id): void
    {
        $banner = StoreBanner::findOrFail($id);
        $this->editingBannerId = $banner->id;
        $this->bannerTitle = $banner->title ?? '';
        $this->bannerDescription = $banner->description ?? '';
        $this->bannerImage = $banner->image;
        $this->bannerLink = $banner->link ?? '';
        $this->bannerIsActive = $banner->is_active;
        $this->showBannerModal = true;
    }

    public function saveBanner(): void
    {
        $this->validate([
            'bannerImage' => 'required|url|max:500',
        ]);

        $data = [
            'organization_id' => auth()->user()->organization_id,
            'title' => $this->bannerTitle ?: null,
            'description' => $this->bannerDescription ?: null,
            'image' => $this->bannerImage,
            'link' => $this->bannerLink ?: null,
            'is_active' => $this->bannerIsActive,
        ];

        if ($this->editingBannerId) {
            StoreBanner::findOrFail($this->editingBannerId)->update($data);
        } else {
            $data['sort_order'] = StoreBanner::where('organization_id', auth()->user()->organization_id)->count();
            StoreBanner::create($data);
        }

        $this->showBannerModal = false;
        $this->resetBannerForm();
    }

    public function deleteBanner(int $id): void
    {
        StoreBanner::findOrFail($id)->delete();
    }

    private function resetBannerForm(): void
    {
        $this->editingBannerId = null;
        $this->bannerTitle = '';
        $this->bannerDescription = '';
        $this->bannerImage = '';
        $this->bannerLink = '';
        $this->bannerIsActive = true;
    }

    public function render()
    {
        return view('livewire.cabinet.store-settings')
            ->layout($this->resolveLayout());
    }
}
