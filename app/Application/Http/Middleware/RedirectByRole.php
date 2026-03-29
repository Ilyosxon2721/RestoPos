<?php

declare(strict_types=1);

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    private const ROLE_ROUTES = [
        'owner' => '/cabinet/dashboard',
        'director' => '/manager/dashboard',
        'admin' => '/manager/dashboard',
        'accountant' => '/manager/reports',
        'head_waiter' => '/manager/dashboard',
        'cashier' => '/cashier/terminal',
        'waiter' => '/waiter/tables',
        'bartender' => '/cashier/terminal',
        'cook' => '/kitchen',
        'storekeeper' => '/warehouse-panel/stock',
        'courier' => '/waiter/orders',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        $userRoles = $user->roles()->pluck('slug')->toArray();

        // Determine target path based on role
        $targetPath = '/cabinet/dashboard'; // default
        foreach (self::ROLE_ROUTES as $role => $path) {
            if (in_array($role, $userRoles)) {
                $targetPath = $path;
                break;
            }
        }

        // If user is on main domain and has a subdomain, redirect to subdomain
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        if (! $tenant && $user->organization?->subdomain) {
            $baseDomain = config('restopos.base_domain');
            $scheme = $request->isSecure() ? 'https' : 'http';
            $port = $request->getPort();
            $portSuffix = in_array($port, [80, 443]) ? '' : ':' . $port;

            return redirect("{$scheme}://{$user->organization->subdomain}.{$baseDomain}{$portSuffix}{$targetPath}");
        }

        return redirect($targetPath);
    }
}
