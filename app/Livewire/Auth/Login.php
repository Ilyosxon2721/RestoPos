<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:4')]
    public string $password = '';

    public bool $remember = false;

    public bool $showPinLogin = false;

    #[Validate('required|digits:4')]
    public string $pin = '';

    public ?string $tenantName = null;

    public function mount(): void
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $this->tenantName = $tenant?->name;
    }

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:4'],
        ]);

        $tenant = app()->bound('tenant') ? app('tenant') : null;

        if ($tenant) {
            // Субдоменный логин: только пользователи этой организации
            $user = User::where('email', $this->email)
                ->where('organization_id', $tenant->id)
                ->where('is_active', true)
                ->first();

            if (!$user || !Hash::check($this->password, $user->password)) {
                $this->addError('email', 'Неверный email или пароль.');

                return;
            }

            Auth::login($user, $this->remember);
        } else {
            // Главный домен: ищем по email среди всех организаций
            if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
                $this->addError('email', 'Неверный email или пароль.');

                return;
            }
        }

        session()->regenerate();

        // RedirectByRole определит куда (включая субдомен если нужно)
        $this->redirect('/redirect', navigate: true);
    }

    public function pinLoginDirect(string $pinCode): void
    {
        if (mb_strlen($pinCode) !== 4) {
            return;
        }

        $tenant = app()->bound('tenant') ? app('tenant') : null;

        $query = User::where('pin_code', $pinCode)->where('is_active', true);

        if ($tenant) {
            $query->where('organization_id', $tenant->id);
        }

        $user = $query->first();

        if (!$user) {
            throw new \Exception('invalid_pin');
        }

        Auth::login($user, remember: true);
        session()->regenerate();

        $this->redirect('/redirect', navigate: true);
    }

    public function togglePinLogin(): void
    {
        $this->showPinLogin = !$this->showPinLogin;
        $this->resetErrorBag();
        $this->pin = '';
    }

    public function appendPin(string $digit): void
    {
        if (mb_strlen($this->pin) < 4) {
            $this->pin .= $digit;
        }

        if (mb_strlen($this->pin) === 4) {
            $this->pinLogin();
        }
    }

    public function clearPin(): void
    {
        $this->pin = '';
        $this->resetErrorBag('pin');
    }

    public function backspacePin(): void
    {
        $this->pin = mb_substr($this->pin, 0, -1);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.auth.login');
    }
}
