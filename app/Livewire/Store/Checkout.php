<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomerAddress;
use App\Domain\Delivery\Models\DeliveryZone;
use App\Domain\Menu\Models\Product;
use App\Domain\Order\Models\Order;
use App\Domain\Store\Models\StoreSettings;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Checkout extends Component
{
    public StoreSettings $store;

    // Тип заказа
    #[Validate('required|in:delivery,pickup')]
    public string $orderType = 'delivery';

    // Адрес доставки
    public ?int $selectedAddressId = null;

    public bool $showNewAddress = false;

    public string $newAddress = '';

    public string $newApartment = '';

    public string $newEntrance = '';

    public string $newFloor = '';

    public string $newIntercom = '';

    public ?float $newLat = null;

    public ?float $newLng = null;

    // Контактные данные
    public string $contactName = '';

    public string $contactPhone = '';

    public string $comment = '';

    // Оплата
    #[Validate('required|in:cash,card,online')]
    public string $paymentMethod = 'cash';

    // Доставка
    public float $deliveryFee = 0;

    public string $deliveryInfo = '';

    public ?int $deliveryZoneId = null;

    public string $error = '';

    public function mount(string $slug): void
    {
        $this->store = StoreSettings::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        /** @var Customer $customer */
        $customer = auth('customer')->user();
        $this->contactName = $customer->full_name;
        $this->contactPhone = $customer->phone ?? '';

        // Выбираем адрес по умолчанию
        $defaultAddress = $customer->defaultAddress();
        if ($defaultAddress) {
            $this->selectedAddressId = $defaultAddress->id;
            $this->calculateDeliveryFee();
        }

        // Если доставка отключена — самовывоз
        if (!$this->store->delivery_enabled) {
            $this->orderType = 'pickup';
        }
    }

    public function updatedOrderType(): void
    {
        if ($this->orderType === 'pickup') {
            $this->deliveryFee = 0;
            $this->deliveryInfo = '';
        } else {
            $this->calculateDeliveryFee();
        }
    }

    public function updatedSelectedAddressId(): void
    {
        $this->calculateDeliveryFee();
    }

    public function updatedNewLat(): void
    {
        $this->calculateDeliveryFee();
    }

    public function updatedNewLng(): void
    {
        $this->calculateDeliveryFee();
    }

    public function updatedShowNewAddress(): void
    {
        $this->calculateDeliveryFee();
    }

    public function getAddressesProperty()
    {
        return auth('customer')->user()->addresses()->orderBy('is_default', 'desc')->get();
    }

    public function getBranchesProperty()
    {
        return $this->store->organization->branches()
            ->where('is_active', true)
            ->get();
    }

    /**
     * Расчёт стоимости доставки по зоне.
     */
    public function calculateDeliveryFee(): void
    {
        if ($this->orderType !== 'delivery') {
            $this->deliveryFee = 0;
            $this->deliveryInfo = '';
            $this->deliveryZoneId = null;

            return;
        }

        $lat = null;
        $lng = null;

        if ($this->showNewAddress && $this->newLat !== null && $this->newLng !== null) {
            $lat = (float) $this->newLat;
            $lng = (float) $this->newLng;
        } elseif ($this->selectedAddressId) {
            $address = CustomerAddress::find($this->selectedAddressId);
            if ($address && $address->latitude && $address->longitude) {
                $lat = (float) $address->latitude;
                $lng = (float) $address->longitude;
            }
        }

        if ($lat === null || $lng === null) {
            $this->deliveryFee = 0;
            $this->deliveryInfo = 'Укажите адрес на карте для расчёта доставки';
            $this->deliveryZoneId = null;

            return;
        }

        // Ищем зону доставки
        $branches = $this->store->organization->branches()->where('is_active', true)->pluck('id');
        $zones = DeliveryZone::withoutGlobalScope('branch')
            ->whereIn('branch_id', $branches)
            ->where('is_active', true)
            ->get();

        foreach ($zones as $zone) {
            if ($zone->containsPoint($lat, $lng)) {
                $this->deliveryZoneId = $zone->id;
                $this->deliveryFee = $zone->delivery_fee;

                if ($zone->free_delivery_from && $zone->free_delivery_from > 0) {
                    $this->deliveryInfo = 'Бесплатная доставка от '.
                        number_format((float) $zone->free_delivery_from, 0, '.', ' ').
                        " {$this->store->currency}";
                } elseif ($this->deliveryFee == 0) {
                    $this->deliveryInfo = 'Бесплатная доставка';
                } else {
                    $this->deliveryInfo = '';
                }

                if ($zone->estimated_time) {
                    $this->deliveryInfo .= ($this->deliveryInfo ? ' | ' : '').
                        "~{$zone->estimated_time} мин";
                }

                return;
            }
        }

        $this->deliveryFee = 0;
        $this->deliveryInfo = 'Ваш адрес вне зоны доставки';
        $this->deliveryZoneId = null;
    }

    /**
     * Оформление заказа.
     */
    public function placeOrder(): void
    {
        $this->validate([
            'orderType' => 'required|in:delivery,pickup',
            'contactName' => 'required|string|max:100',
            'contactPhone' => 'required|string|max:20',
            'paymentMethod' => 'required|in:cash,card,online',
        ]);

        if ($this->orderType === 'delivery' && !$this->selectedAddressId && !$this->newAddress) {
            $this->error = 'Выберите адрес доставки';

            return;
        }

        $this->error = '';

        // Корзина передаётся из JS через скрытое поле
        // Для серверной обработки получаем из dispatch
        $this->dispatch('get-cart-for-checkout');
    }

    /**
     * Финальное создание заказа (вызывается из JS с данными корзины).
     */
    public function createOrder(array $cartItems): void
    {
        if (empty($cartItems)) {
            $this->error = 'Корзина пуста';

            return;
        }

        try {
            DB::beginTransaction();

            /** @var Customer $customer */
            $customer = auth('customer')->user();
            $branch = $this->store->organization->branches()->where('is_active', true)->first();

            if (!$branch) {
                $this->error = 'Ресторан временно не принимает заказы';
                DB::rollBack();

                return;
            }

            // Сохраняем новый адрес если указан
            $addressId = $this->selectedAddressId;
            if ($this->orderType === 'delivery' && $this->showNewAddress && $this->newAddress) {
                $newAddr = CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'label' => 'Доставка',
                    'address' => $this->newAddress,
                    'apartment' => $this->newApartment ?: null,
                    'entrance' => $this->newEntrance ?: null,
                    'floor' => $this->newFloor ?: null,
                    'latitude' => $this->newLat,
                    'longitude' => $this->newLng,
                ]);
                $addressId = $newAddr->id;
            }

            // Создаём заказ
            $order = Order::create([
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'order_number' => Order::generateOrderNumber($branch->id),
                'type' => $this->orderType === 'delivery' ? 'delivery' : 'takeaway',
                'source' => 'website',
                'status' => 'new',
                'payment_status' => 'unpaid',
                'notes' => $this->comment ?: null,
                'opened_at' => now(),
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);

            // Добавляем позиции
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $product = Product::withoutGlobalScope('organization')->find($item['id']);
                if (!$product || !$product->is_available) {
                    continue;
                }

                $unitPrice = (float) $product->price;
                $qty = max(1, (int) $item['qty']);
                $totalPrice = $unitPrice * $qty;
                $subtotal += $totalPrice;

                $order->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'cost_price' => $product->cost_price ?? 0,
                    'status' => 'pending',
                ]);
            }

            // Обновляем сумму заказа
            $total = $subtotal + $this->deliveryFee;
            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $total,
                'service_charge' => $this->deliveryFee,
            ]);

            // Создаём запись доставки
            if ($this->orderType === 'delivery' && $addressId) {
                $address = CustomerAddress::find($addressId);
                \App\Domain\Delivery\Models\DeliveryOrder::create([
                    'order_id' => $order->id,
                    'delivery_zone_id' => $this->deliveryZoneId,
                    'address' => $address->address ?? $this->newAddress,
                    'address_details' => $address->apartment ?? $this->newApartment,
                    'latitude' => $address->latitude ?? $this->newLat,
                    'longitude' => $address->longitude ?? $this->newLng,
                    'contact_name' => $this->contactName,
                    'contact_phone' => $this->contactPhone,
                    'delivery_fee' => $this->deliveryFee,
                    'status' => 'pending',
                ]);
            }

            // Обновляем статистику клиента
            $customer->increment('total_orders');
            $customer->increment('total_spent', $total);
            $customer->update(['last_visit_at' => now()]);

            DB::commit();

            // Очищаем корзину и перенаправляем
            $this->dispatch('clear-cart-after-order');
            $this->redirect(route('shop.orders', ['slug' => $this->store->slug]));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = 'Ошибка при создании заказа. Попробуйте позже.';
        }
    }

    public function render()
    {
        return view('livewire.store.checkout')
            ->layout('components.layouts.store', ['store' => $this->store]);
    }
}
