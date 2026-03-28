<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Auth\Models\User;

final class CashShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function open(User $user): bool
    {
        return $user->hasPermission('finance.cash_shift');
    }

    public function close(User $user): bool
    {
        return $user->hasPermission('finance.cash_shift');
    }

    public function cashOperation(User $user): bool
    {
        return $user->hasPermission('finance.cash_operations');
    }
}
