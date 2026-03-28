<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Auth\Models\User;

final class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('orders.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('orders.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('orders.create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('orders.edit');
    }

    public function cancel(User $user): bool
    {
        return $user->hasPermission('orders.cancel');
    }

    public function applyDiscount(User $user): bool
    {
        return $user->hasPermission('orders.discount');
    }

    public function refund(User $user): bool
    {
        return $user->hasPermission('orders.refund');
    }
}
