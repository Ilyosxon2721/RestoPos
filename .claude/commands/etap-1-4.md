# Этап 1.4: Модуль Organization

Создай модуль мультитенантности — организации и филиалы.

## Структура

```
app/Domain/Organization/
├── Actions/
│   ├── CreateOrganizationAction.php
│   ├── CreateBranchAction.php
│   └── SwitchBranchAction.php
├── DTOs/
│   ├── OrganizationDTO.php
│   └── BranchDTO.php
├── Middleware/
│   └── SetCurrentBranch.php
└── Services/
    └── TenantService.php
```

## Задачи:

### 1. Модель Organization

Создай `app/Models/Organization.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'legal_name',
        'inn',
        'logo',
        'subdomain',
        'subscription_plan',
        'subscription_expires_at',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'subscription_expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? (string) Str::uuid();
        });
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->subscription_plan === 'trial') {
            return $this->subscription_expires_at?->isFuture() ?? false;
        }
        
        return $this->subscription_expires_at?->isFuture() ?? false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### 2. Модель Branch

Создай `app/Models/Branch.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'organization_id',
        'name',
        'address',
        'city',
        'phone',
        'email',
        'timezone',
        'currency_code',
        'working_hours',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'working_hours' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? (string) Str::uuid();
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branches')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function menuCategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function halls(): HasMany
    {
        return $this->hasMany(Hall::class);
    }

    public function cashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
```

### 3. TenantService

Создай `app/Domain/Organization/Services/TenantService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Organization\Services;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Context;

final class TenantService
{
    private static ?Organization $currentOrganization = null;
    private static ?Branch $currentBranch = null;

    public function setOrganization(Organization $organization): void
    {
        self::$currentOrganization = $organization;
        Context::add('organization_id', $organization->id);
    }

    public function setBranch(Branch $branch): void
    {
        self::$currentBranch = $branch;
        Context::add('branch_id', $branch->id);
    }

    public function setFromUser(User $user): void
    {
        $this->setOrganization($user->organization);
        
        $branch = $user->defaultBranch() ?? $user->branches()->first();
        
        if ($branch) {
            $this->setBranch($branch);
        }
    }

    public static function organization(): ?Organization
    {
        return self::$currentOrganization;
    }

    public static function branch(): ?Branch
    {
        return self::$currentBranch;
    }

    public static function organizationId(): ?int
    {
        return self::$currentOrganization?->id;
    }

    public static function branchId(): ?int
    {
        return self::$currentBranch?->id;
    }
}
```

### 4. Middleware SetCurrentBranch

Создай `app/Domain/Organization/Middleware/SetCurrentBranch.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Organization\Middleware;

use App\Domain\Organization\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetCurrentBranch
{
    public function __construct(
        private readonly TenantService $tenantService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $this->tenantService->setFromUser($user);
            
            // Можно переключить филиал через header или session
            if ($branchId = $request->header('X-Branch-Id') ?? session('current_branch_id')) {
                $branch = $user->branches()->find($branchId);
                if ($branch) {
                    $this->tenantService->setBranch($branch);
                }
            }
        }

        return $next($request);
    }
}
```

### 5. Регистрация Middleware

В `bootstrap/app.php` добавь:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Domain\Organization\Middleware\SetCurrentBranch::class,
    ]);
    
    $middleware->api(append: [
        \App\Domain\Organization\Middleware\SetCurrentBranch::class,
    ]);
})
```

### 6. Global Scopes для моделей

Создай `app/Domain/Organization/Scopes/BranchScope.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Organization\Scopes;

use App\Domain\Organization\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($branchId = TenantService::branchId()) {
            $builder->where($model->getTable() . '.branch_id', $branchId);
        }
    }
}
```

### 7. Trait для моделей

Создай `app/Domain/Organization/Traits/BelongsToBranch.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Organization\Traits;

use App\Domain\Organization\Scopes\BranchScope;
use App\Domain\Organization\Services\TenantService;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        static::addGlobalScope(new BranchScope());
        
        static::creating(function ($model) {
            if (empty($model->branch_id)) {
                $model->branch_id = TenantService::branchId();
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
```

### 8. CreateOrganizationAction

Создай `app/Domain/Organization/Actions/CreateOrganizationAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Organization\Actions;

use App\Domain\Organization\DTOs\OrganizationDTO;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateOrganizationAction
{
    public function execute(OrganizationDTO $dto): Organization
    {
        return DB::transaction(function () use ($dto) {
            // Создаём организацию
            $organization = Organization::create([
                'name' => $dto->name,
                'legal_name' => $dto->legalName,
                'inn' => $dto->inn,
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(14),
            ]);

            // Создаём первый филиал
            $branch = Branch::create([
                'organization_id' => $organization->id,
                'name' => $dto->branchName ?? 'Главный',
                'address' => $dto->address,
                'phone' => $dto->phone,
                'timezone' => 'Asia/Tashkent',
                'currency_code' => 'UZS',
            ]);

            // Создаём владельца
            $user = User::create([
                'organization_id' => $organization->id,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'password' => Hash::make($dto->password),
                'first_name' => $dto->ownerName,
            ]);

            $user->assignRole('owner');
            $user->branches()->attach($branch->id, ['is_default' => true]);

            return $organization;
        });
    }
}
```

### 9. OrganizationDTO

Создай `app/Domain/Organization/DTOs/OrganizationDTO.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Organization\DTOs;

final readonly class OrganizationDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $ownerName,
        public ?string $phone = null,
        public ?string $legalName = null,
        public ?string $inn = null,
        public ?string $branchName = null,
        public ?string $address = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            ownerName: $data['owner_name'],
            phone: $data['phone'] ?? null,
            legalName: $data['legal_name'] ?? null,
            inn: $data['inn'] ?? null,
            branchName: $data['branch_name'] ?? null,
            address: $data['address'] ?? null,
        );
    }
}
```

### 10. Seeder для тестовых данных

Создай `database/seeders/OrganizationSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::create([
            'name' => 'Demo Restaurant',
            'legal_name' => 'ООО "Демо Ресторан"',
            'subscription_plan' => 'business',
            'subscription_expires_at' => now()->addYear(),
        ]);

        $branch = Branch::create([
            'organization_id' => $org->id,
            'name' => 'Центральный филиал',
            'address' => 'г. Ташкент, ул. Навои, 1',
            'city' => 'Ташкент',
            'phone' => '+998901234567',
            'timezone' => 'Asia/Tashkent',
            'currency_code' => 'UZS',
        ]);

        $owner = User::create([
            'organization_id' => $org->id,
            'email' => 'admin@restopos.local',
            'phone' => '+998901234567',
            'password' => Hash::make('password'),
            'first_name' => 'Админ',
            'last_name' => 'Системы',
        ]);

        $owner->assignRole('owner');
        $owner->branches()->attach($branch->id, ['is_default' => true]);

        // Добавим официанта
        $waiter = User::create([
            'organization_id' => $org->id,
            'email' => 'waiter@restopos.local',
            'pin_code' => '1234',
            'password' => Hash::make('password'),
            'first_name' => 'Официант',
            'last_name' => 'Тестовый',
        ]);

        $waiter->assignRole('waiter');
        $waiter->branches()->attach($branch->id, ['is_default' => true]);
    }
}
```

### 11. Обнови DatabaseSeeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            OrganizationSeeder::class,
        ]);
    }
}
```

## Проверка

```bash
php artisan migrate:fresh --seed
```

Теперь можешь логиниться:
- Email: `admin@restopos.local`
- Пароль: `password`

Этап 1.4 завершён!
