<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Auth\Models\User;

final class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customers.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('customers.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('customers.manage');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('customers.manage');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('customers.manage');
    }

    public function manageBonus(User $user): bool
    {
        return $user->hasPermission('customers.bonuses');
    }
}
