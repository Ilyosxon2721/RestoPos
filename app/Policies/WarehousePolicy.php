<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Auth\Models\User;

final class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('warehouse.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('warehouse.view');
    }

    public function supply(User $user): bool
    {
        return $user->hasPermission('warehouse.supply');
    }

    public function writeOff(User $user): bool
    {
        return $user->hasPermission('warehouse.write_off');
    }

    public function inventory(User $user): bool
    {
        return $user->hasPermission('warehouse.inventory');
    }
}
