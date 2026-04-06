<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomerAddress;
use App\Domain\Store\Models\StoreSettings;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Profile extends Component
{
    public StoreSettings $store;

    #[Validate('required|string|max:100')]
    public string $firstName = '';

    #[Validate('nullable|string|max:100')]
    public string $lastName = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    #[Validate('nullable|date')]
    public ?string $birthDate = null;

    public bool $showAddressModal = false;
    public ?int $editingAddressId = null;
    public string $addressLabel = 'Дом';
    public string $addressText = '';
    public string $apartment = '';
    public string $entrance = '';
    public string $floor = '';
    public string $intercom = '';
    public string $addressComment = '';
    public ?float $latitude = null;
    public ?float $longitude = null;

    public bool $saved = false;

    public function mount(string $slug): void
    {
        $this->store = StoreSettings::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $customer = auth('customer')->user();
        $this->firstName = $customer->first_name ?? '';
        $this->lastName = $customer->last_name ?? '';
        $this->email = $customer->email ?? '';
        $this->birthDate = $customer->birth_date?->format('Y-m-d');
    }

    public function saveProfile(): void
    {
        $this->validate();

        /** @var Customer $customer */
        $customer = auth('customer')->user();
        $customer->update([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email ?: null,
            'birth_date' => $this->birthDate,
        ]);

        $this->saved = true;
    }

    public function getAddressesProperty()
    {
        return auth('customer')->user()->addresses()->orderBy('is_default', 'desc')->get();
    }

    public function getOrdersCountProperty(): int
    {
        return auth('customer')->user()->total_orders;
    }

    public function addAddress(): void
    {
        $this->resetAddressForm();
        $this->showAddressModal = true;
    }

    public function editAddress(int $id): void
    {
        $address = CustomerAddress::findOrFail($id);
        $this->editingAddressId = $address->id;
        $this->addressLabel = $address->label;
        $this->addressText = $address->address;
        $this->apartment = $address->apartment ?? '';
        $this->entrance = $address->entrance ?? '';
        $this->floor = $address->floor ?? '';
        $this->intercom = $address->intercom ?? '';
        $this->addressComment = $address->comment ?? '';
        $this->latitude = $address->latitude ? (float) $address->latitude : null;
        $this->longitude = $address->longitude ? (float) $address->longitude : null;
        $this->showAddressModal = true;
    }

    public function saveAddress(): void
    {
        $this->validate([
            'addressText' => 'required|string|max:500',
            'addressLabel' => 'required|string|max:50',
        ]);

        $data = [
            'customer_id' => auth('customer')->id(),
            'label' => $this->addressLabel,
            'address' => $this->addressText,
            'apartment' => $this->apartment ?: null,
            'entrance' => $this->entrance ?: null,
            'floor' => $this->floor ?: null,
            'intercom' => $this->intercom ?: null,
            'comment' => $this->addressComment ?: null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];

        if ($this->editingAddressId) {
            CustomerAddress::findOrFail($this->editingAddressId)->update($data);
        } else {
            // Первый адрес — по умолчанию
            $hasAddresses = auth('customer')->user()->addresses()->exists();
            $data['is_default'] = !$hasAddresses;
            CustomerAddress::create($data);
        }

        $this->showAddressModal = false;
        $this->resetAddressForm();
    }

    public function deleteAddress(int $id): void
    {
        CustomerAddress::where('id', $id)
            ->where('customer_id', auth('customer')->id())
            ->delete();
    }

    public function setDefaultAddress(int $id): void
    {
        $customerId = auth('customer')->id();
        CustomerAddress::where('customer_id', $customerId)->update(['is_default' => false]);
        CustomerAddress::where('id', $id)->where('customer_id', $customerId)->update(['is_default' => true]);
    }

    public function logout(): void
    {
        auth('customer')->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('shop.home', ['slug' => $this->store->slug]));
    }

    private function resetAddressForm(): void
    {
        $this->editingAddressId = null;
        $this->addressLabel = 'Дом';
        $this->addressText = '';
        $this->apartment = '';
        $this->entrance = '';
        $this->floor = '';
        $this->intercom = '';
        $this->addressComment = '';
        $this->latitude = null;
        $this->longitude = null;
    }

    public function render()
    {
        return view('livewire.store.profile')
            ->layout('components.layouts.store', ['store' => $this->store]);
    }
}
