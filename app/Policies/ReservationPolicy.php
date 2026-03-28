<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Auth\Models\User;

final class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reservations.view');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('reservations.manage');
    }
}
