<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
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

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:4'],
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Неверный email или пароль.');

            return;
        }

        session()->regenerate();

        $this->redirect('/dashboard', navigate: true);
    }

    public function pinLogin(): void
    {
        $this->validate([
            'pin' => ['required', 'digits:4'],
        ]);

        $user = \App\Models\User::where('pin_code', $this->pin)->first();

        if (! $user) {
            $this->addError('pin', 'Неверный PIN-код.');
            $this->pin = '';

            return;
        }

        Auth::login($user, remember: true);

        session()->regenerate();

        $this->redirect('/dashboard', navigate: true);
    }

    public function togglePinLogin(): void
    {
        $this->showPinLogin = ! $this->showPinLogin;
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
