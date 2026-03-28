<?php

declare(strict_types=1);

use App\Domain\Auth\Models\Role;
use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Создаёт пользователя с организацией, филиалом и ролью admin.
 *
 * @return array{user: User, organization: Organization, branch: Branch, role: Role}
 */
function createAuthenticatedUser(string $roleSlug = 'admin'): array
{
    $organization = Organization::factory()->create();

    $branch = Branch::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $user = User::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $role = Role::create([
        'organization_id' => $organization->id,
        'name' => ucfirst($roleSlug),
        'slug' => $roleSlug,
        'is_system' => true,
    ]);

    $user->roles()->attach($role->id, ['branch_id' => $branch->id]);

    return compact('user', 'organization', 'branch', 'role');
}
