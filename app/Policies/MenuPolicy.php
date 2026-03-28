<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Auth\Models\User;

final class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('menu.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('menu.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('menu.manage');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('menu.manage');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('menu.manage');
    }

    public function manageStopList(User $user): bool
    {
        return $user->hasPermission('menu.stop_list');
    }
}
