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

    /**
     * Вызывается как action из маршрута GET /redirect
     */
    public function __invoke(Request $request): Response
    {
        return $this->performRedirect($request);
    }

    /**
     * Вызывается как middleware
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $this->performRedirect($request);
    }

    private function performRedirect(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect('/login');
        }

        $userRoles = $user->roles()->pluck('slug')->toArray();

        // Определяем путь по роли
        $targetPath = '/cabinet/dashboard';
        foreach (self::ROLE_ROUTES as $role => $path) {
            if (in_array($role, $userRoles)) {
                $targetPath = $path;
                break;
            }
        }

        // Если на главном домене и субдомены активны — редирект на субдомен
        if ($this->shouldRedirectToSubdomain($request, $user)) {
            return $this->redirectToSubdomain($request, $user->organization->subdomain, $targetPath);
        }

        return redirect($targetPath);
    }

    private function shouldRedirectToSubdomain(Request $request, $user): bool
    {
        // Уже на субдомене — не нужно
        if (app()->bound('tenant')) {
            return false;
        }

        // У организации нет субдомена
        if (! $user->organization?->subdomain) {
            return false;
        }

        // Проверяем что текущий хост заканчивается на base_domain
        $baseDomain = config('restopos.base_domain');
        $host = $request->getHost();

        // Только если мы на base_domain (restopos.uz), а не на forge/другом хостинге
        return $host === $baseDomain || str_ends_with($host, '.' . $baseDomain);
    }

    private function redirectToSubdomain(Request $request, string $subdomain, string $targetPath): \Illuminate\Http\RedirectResponse
    {
        $baseDomain = config('restopos.base_domain');
        $scheme = $request->isSecure() ? 'https' : 'http';
        $port = $request->getPort();
        $portSuffix = in_array($port, [80, 443]) ? '' : ':' . $port;

        return redirect("{$scheme}://{$subdomain}.{$baseDomain}{$portSuffix}{$targetPath}");
    }
}
