<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\Role;
use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterOrganizationAction
{
    /**
     * Register a new organization with owner user.
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Create organization
            $organization = Organization::create([
                'name' => $data['organization_name'],
                'slug' => $data['organization_slug'] ?? \Str::slug($data['organization_name']),
                'legal_name' => $data['legal_name'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'phone' => $data['organization_phone'] ?? null,
                'email' => $data['organization_email'] ?? null,
                'timezone' => $data['timezone'] ?? 'Asia/Tashkent',
                'currency' => $data['currency'] ?? 'UZS',
                'locale' => $data['locale'] ?? 'ru',
                'is_active' => true,
                'subscription_plan' => 'trial',
                'subscription_ends_at' => now()->addDays(14),
            ]);

            // Create default branch
            $branch = Branch::create([
                'organization_id' => $organization->id,
                'name' => $data['branch_name'] ?? 'Главный филиал',
                'code' => 'MAIN',
                'is_active' => true,
            ]);

            // Create owner user
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

            // Assign owner role (null branch_id = access to all branches)
            $ownerRole = Role::where('organization_id', $organization->id)
                ->where('slug', 'owner')
                ->first();

            if (!$ownerRole) {
                // Create owner role if it doesn't exist
                $ownerRole = Role::create([
                    'organization_id' => $organization->id,
                    'name' => 'Владелец',
                    'slug' => 'owner',
                    'description' => 'Полный доступ ко всем функциям системы',
                    'is_system' => true,
                ]);
            }

            $user->roles()->attach($ownerRole->id, ['branch_id' => null]);

            // Create token
            $token = $user->createToken('api-token');

            return [
                'organization' => $organization,
                'branch' => $branch,
                'user' => $user,
                'token' => $token->plainTextToken,
            ];
        });
    }
}
