<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Domain\Customer\Models\Customer;
use App\Domain\Infrastructure\Sms\SmsSender;
use App\Domain\Store\Models\StoreSettings;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Login extends Component
{
    public StoreSettings $store;

    public string $step = 'phone'; // phone, code, name

    #[Validate('required|string|min:9|max:20')]
    public string $phone = '';

    #[Validate('required|string|size:6')]
    public string $code = '';

    public string $firstName = '';

    public string $lastName = '';

    public ?int $customerId = null;

    public string $error = '';

    public function mount(string $slug): void
    {
        $this->store = StoreSettings::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (auth('customer')->check()) {
            return;
        }
    }

    /**
     * Шаг 1: Отправка кода на телефон.
     */
    public function sendCode(): void
    {
        $this->validateOnly('phone');
        $this->error = '';

        $phone = preg_replace('/[^0-9+]/', '', $this->phone);

        // Находим или создаём клиента
        $customer = Customer::withoutGlobalScope('organization')
            ->where('organization_id', $this->store->organization_id)
            ->where('phone', $phone)
            ->first();

        if (!$customer) {
            $customer = Customer::withoutGlobalScope('organization')->create([
                'organization_id' => $this->store->organization_id,
                'phone' => $phone,
                'first_name' => '',
                'is_registered' => false,
            ]);
        }

        $this->customerId = $customer->id;
        $code = $customer->generateVerificationCode();

        app(SmsSender::class)->send(
            $phone,
            __('Код для входа: :code', ['code' => $code])
        );

        $this->step = 'code';
    }

    /**
     * Шаг 2: Проверка кода.
     */
    public function verifyCode(): void
    {
        $this->validateOnly('code');
        $this->error = '';

        $customer = Customer::withoutGlobalScope('organization')
            ->findOrFail($this->customerId);

        if (!$customer->verifyCode($this->code)) {
            $this->error = 'Неверный или просроченный код';

            return;
        }

        // Если клиент новый — запрашиваем имя
        if (empty($customer->first_name)) {
            $this->step = 'name';

            return;
        }

        // Авторизуем
        auth('customer')->login($customer, true);

        $this->redirect(route('shop.home', ['slug' => $this->store->slug]));
    }

    /**
     * Шаг 3: Заполнение имени (для новых клиентов).
     */
    public function completeName(): void
    {
        $this->validate([
            'firstName' => 'required|string|max:100',
        ]);

        $customer = Customer::withoutGlobalScope('organization')
            ->findOrFail($this->customerId);

        $customer->update([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'is_registered' => true,
        ]);

        auth('customer')->login($customer, true);

        $this->redirect(route('shop.home', ['slug' => $this->store->slug]));
    }

    /**
     * Повторная отправка кода.
     */
    public function resendCode(): void
    {
        $customer = Customer::withoutGlobalScope('organization')
            ->findOrFail($this->customerId);

        // Ограничение: не чаще 1 раза в минуту
        if ($customer->verification_code_sent_at && $customer->verification_code_sent_at->diffInSeconds(now()) < 60) {
            $this->error = 'Подождите минуту перед повторной отправкой';

            return;
        }

        $code = $customer->generateVerificationCode();

        app(SmsSender::class)->send(
            $customer->phone,
            __('Код для входа: :code', ['code' => $code])
        );

        $this->error = '';
    }

    public function render()
    {
        return view('livewire.store.login')
            ->layout('components.layouts.store', ['store' => $this->store]);
    }
}
