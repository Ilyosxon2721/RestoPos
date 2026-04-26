<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:6')]
    public string $password = '';

    public bool $remember = false;

    public string $error = '';

    public function login(): void
    {
        $this->validate();

        if (Auth::guard('platform')->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            session()->regenerate();
            $this->redirect(route('admin.dashboard'));

            return;
        }

        $this->error = 'Неверный email или пароль';
    }

    public function render()
    {
        return view('livewire.admin.login');
    }
}
