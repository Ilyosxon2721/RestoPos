# Этап 1.3: Модуль Auth

Создай полный модуль аутентификации с ролями и правами.

## Структура модуля

```
app/Domain/Auth/
├── Actions/
│   ├── LoginAction.php
│   ├── LogoutAction.php
│   ├── RegisterAction.php
│   └── PinLoginAction.php
├── DTOs/
│   ├── LoginDTO.php
│   └── RegisterDTO.php
├── Events/
│   ├── UserLoggedIn.php
│   └── UserLoggedOut.php
└── Policies/
    └── UserPolicy.php
```

## Задачи:

### 1. Модель User

Создай `app/Models/User.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'uuid',
        'organization_id',
        'email',
        'phone',
        'password',
        'pin_code',
        'first_name',
        'last_name',
        'avatar',
        'language',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'pin_code',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branches')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function defaultBranch(): ?Branch
    {
        return $this->branches()->wherePivot('is_default', true)->first();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
```

### 2. LoginDTO

Создай `app/Domain/Auth/DTOs/LoginDTO.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTOs;

final readonly class LoginDTO
{
    public function __construct(
        public string $login,
        public string $password,
        public bool $remember = false,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            login: $data['login'],
            password: $data['password'],
            remember: $data['remember'] ?? false,
        );
    }
}
```

### 3. LoginAction

Создай `app/Domain/Auth/Actions/LoginAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\LoginDTO;
use App\Domain\Auth\Events\UserLoggedIn;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginAction
{
    public function execute(LoginDTO $dto): User
    {
        $user = $this->findUser($dto->login);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => [__('auth.inactive')],
            ]);
        }

        Auth::login($user, $dto->remember);

        $user->update(['last_login_at' => now()]);

        event(new UserLoggedIn($user));

        return $user;
    }

    private function findUser(string $login): ?User
    {
        return User::query()
            ->where('email', $login)
            ->orWhere('phone', $login)
            ->first();
    }
}
```

### 4. PinLoginAction

Создай `app/Domain/Auth/Actions/PinLoginAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Events\UserLoggedIn;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class PinLoginAction
{
    public function execute(int $branchId, string $pinCode): User
    {
        $user = User::query()
            ->whereHas('branches', fn($q) => $q->where('branches.id', $branchId))
            ->where('pin_code', $pinCode)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'pin_code' => [__('auth.invalid_pin')],
            ]);
        }

        Auth::login($user);

        $user->update(['last_login_at' => now()]);

        event(new UserLoggedIn($user));

        return $user;
    }
}
```

### 5. Events

Создай `app/Domain/Auth/Events/UserLoggedIn.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Auth\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}
}
```

### 6. Livewire Login Component

Создай `app/Livewire/Auth/Login.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Domain\Auth\Actions\LoginAction;
use App\Domain\Auth\DTOs\LoginDTO;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class Login extends Component
{
    #[Validate('required|string')]
    public string $login = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    public bool $remember = false;

    public function authenticate(LoginAction $action): void
    {
        $this->validate();

        $action->execute(new LoginDTO(
            login: $this->login,
            password: $this->password,
            remember: $this->remember,
        ));

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
```

### 7. Login View

Создай `resources/views/livewire/auth/login.blade.php`:

```blade
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">RestoPOS</h1>
                <p class="text-gray-500 mt-2">Войдите в систему</p>
            </div>

            <form wire:submit="authenticate" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email или телефон
                    </label>
                    <input 
                        type="text" 
                        wire:model="login"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="email@example.com"
                        autofocus
                    >
                    @error('login')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Пароль
                    </label>
                    <input 
                        type="password" 
                        wire:model="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-600">Запомнить меня</span>
                    </label>
                    <a href="#" class="text-sm text-primary-600 hover:underline">Забыли пароль?</a>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-primary-600 text-white py-3 rounded-lg font-medium hover:bg-primary-700 transition-colors"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-wait"
                >
                    <span wire:loading.remove>Войти</span>
                    <span wire:loading>Вход...</span>
                </button>
            </form>
        </div>
    </div>
</div>
```

### 8. Seeder для ролей и прав

Создай `database/seeders/RolesAndPermissionsSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            // Orders
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.discount',
            // Menu
            'menu.view', 'menu.manage',
            // Warehouse
            'warehouse.view', 'warehouse.manage', 'warehouse.supply', 'warehouse.inventory',
            // Finance
            'finance.view', 'finance.manage', 'finance.cash_operations',
            // Staff
            'staff.view', 'staff.manage',
            // Customers
            'customers.view', 'customers.manage',
            // Reports
            'reports.view', 'reports.export',
            // Settings
            'settings.view', 'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Roles
        $owner = Role::create(['name' => 'owner']);
        $owner->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'orders.view', 'orders.create', 'orders.edit', 'orders.discount',
            'menu.view', 'menu.manage',
            'warehouse.view', 'warehouse.manage',
            'finance.view', 'finance.manage',
            'staff.view', 'staff.manage',
            'customers.view', 'customers.manage',
            'reports.view', 'reports.export',
            'settings.view',
        ]);

        $cashier = Role::create(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'orders.view', 'orders.create', 'orders.edit',
            'finance.view', 'finance.cash_operations',
            'customers.view',
        ]);

        $waiter = Role::create(['name' => 'waiter']);
        $waiter->givePermissionTo([
            'orders.view', 'orders.create', 'orders.edit',
            'customers.view',
        ]);

        $cook = Role::create(['name' => 'cook']);
        $cook->givePermissionTo([
            'orders.view',
        ]);
    }
}
```

### 9. Routes

Добавь в `routes/web.php`:

```php
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Logout;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
```

### 10. Guest Layout

Создай `resources/views/components/layouts/guest.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RestoPOS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
```

## Проверка

1. Запусти сидер: `php artisan db:seed --class=RolesAndPermissionsSeeder`
2. Открой `http://127.0.0.1:8001/login`
3. Проверь что форма отображается

Этап 1.3 завершён!
