<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Auth\Models\User;

final class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('staff.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('staff.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('staff.manage');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('staff.manage');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('staff.manage');
    }

    public function manageSalary(User $user): bool
    {
        return $user->hasPermission('staff.salary');
    }
}
