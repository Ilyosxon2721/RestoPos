<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Domain\Auth\Actions\RegisterOrganizationAction;
use App\Domain\Organization\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class Register extends Component
{
    #[Validate('required|string|max:255')]
    public string $organizationName = '';

    #[Validate('required|string|max:100|alpha_dash|unique:organizations,subdomain')]
    public string $subdomain = '';

    #[Validate('required|string|max:255')]
    public string $firstName = '';

    #[Validate('nullable|string|max:255')]
    public string $lastName = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public bool $subdomainManuallyEdited = false;

    public function updatedOrganizationName(): void
    {
        if (! $this->subdomainManuallyEdited) {
            $this->subdomain = Str::slug($this->organizationName);
        }
    }

    public function updatedSubdomain(): void
    {
        $this->subdomainManuallyEdited = true;
        $this->subdomain = Str::slug($this->subdomain);
    }

    public function register(): void
    {
        $this->validate();

        $action = new RegisterOrganizationAction();

        $result = $action->execute([
            'organization_name' => $this->organizationName,
            'subdomain' => $this->subdomain,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'password' => $this->password,
        ]);

        Auth::login($result['user']);
        session()->regenerate();

        // Redirect to their new subdomain
        $baseDomain = config('restopos.base_domain');
        $scheme = request()->isSecure() ? 'https' : 'http';
        $port = request()->getPort();
        $portSuffix = in_array($port, [80, 443]) ? '' : ':' . $port;

        $this->redirect("{$scheme}://{$this->subdomain}.{$baseDomain}{$portSuffix}/cabinet/dashboard");
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.auth.register');
    }
}
