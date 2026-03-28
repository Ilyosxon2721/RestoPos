<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Auth\Models\User;

final class ReportPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('reports.view');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('reports.export');
    }

    public function advanced(User $user): bool
    {
        return $user->hasPermission('reports.advanced');
    }
}
