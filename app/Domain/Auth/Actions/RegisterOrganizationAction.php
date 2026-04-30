<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\Role;
use App\Domain\Auth\Models\User;
use App\Domain\Menu\Actions\SeedOrganizationDefaultsAction;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterOrganizationAction
{
    public function __construct(
        private readonly SeedOrganizationDefaultsAction $seedDefaults,
    ) {}

    /**
     * Регистрация новой организации с владельцем.
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Создаём организацию (только существующие колонки)
            $organization = Organization::create([
                'name' => $data['organization_name'],
                'subdomain' => $data['subdomain'] ?? Str::slug($data['organization_name']),
                'legal_name' => $data['legal_name'] ?? null,
                'inn' => $data['tax_id'] ?? null,
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(14),
                'settings' => [
                    'timezone' => $data['timezone'] ?? 'Asia/Tashkent',
                    'currency' => $data['currency'] ?? 'UZS',
                    'locale' => $data['locale'] ?? 'ru',
                ],
                'is_active' => true,
            ]);

            // Создаём филиал по умолчанию
            $branch = Branch::create([
                'organization_id' => $organization->id,
                'name' => $data['branch_name'] ?? 'Главный филиал',
                'is_active' => true,
            ]);

            // Создаём пользователя-владельца
            $user = User::create([
                'organization_id' => $organization->id,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'locale' => $data['locale'] ?? 'ru',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Назначаем роль owner (branch_id = null = доступ ко всем филиалам)
            $ownerRole = Role::withoutGlobalScopes()
                ->where('slug', 'owner')
                ->where(function ($q) use ($organization) {
                    $q->where('organization_id', $organization->id)
                        ->orWhereNull('organization_id');
                })
                ->first();

            if (!$ownerRole) {
                $ownerRole = Role::withoutGlobalScopes()->create([
                    'organization_id' => $organization->id,
                    'name' => 'Владелец',
                    'slug' => 'owner',
                    'description' => 'Полный доступ ко всем функциям системы',
                    'is_system' => true,
                ]);
            }

            $user->roles()->attach($ownerRole->id, ['branch_id' => null]);

            $this->seedDefaults->execute($organization);

            return [
                'organization' => $organization,
                'branch' => $branch,
                'user' => $user,
            ];
        });
    }
}
