<?php

declare(strict_types=1);

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            // If on tenant subdomain, redirect to tenant login
            if (app()->bound('tenant')) {
                return redirect('/login');
            }

            return redirect()->route('login');
        }

        // Verify user belongs to current tenant
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        if ($tenant && $user->organization_id !== $tenant->id) {
            auth()->logout();

            return redirect('/login');
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Недостаточно прав для доступа к данной панели');
    }
}
