<?php

declare(strict_types=1);

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    /**
     * Роли управления — остаются на главном домене pos.forris.uz
     * Менеджер может управлять несколькими ресторанами.
     */
    private const MANAGEMENT_ROUTES = [
        'owner' => '/cabinet/dashboard',
        'director' => '/manager/dashboard',
        'admin' => '/manager/dashboard',
        'accountant' => '/manager/reports',
        'head_waiter' => '/manager/dashboard',
    ];

    /**
     * Операционные роли — работают на субдомене конкретного ресторана.
     */
    private const OPERATIONAL_ROUTES = [
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
        $onSubdomain = app()->bound('tenant');

        // Если уже на субдомене — определяем путь по роли (все роли работают)
        if ($onSubdomain) {
            $targetPath = $this->resolveTargetPath($userRoles);
            return redirect($targetPath);
        }

        // На главном домене: проверяем, управленческая ли роль
        $managementPath = $this->resolveManagementPath($userRoles);

        if ($managementPath) {
            // Управленческая роль — остаётся на главном домене
            return redirect($managementPath);
        }

        // Операционная роль на главном домене — перенаправляем на субдомен
        $operationalPath = $this->resolveOperationalPath($userRoles);

        if ($operationalPath && $this->canRedirectToSubdomain($user)) {
            return $this->redirectToSubdomain($request, $user->organization->subdomain, $operationalPath);
        }

        // Fallback — кабинет на главном домене
        return redirect('/cabinet/dashboard');
    }

    private function resolveManagementPath(array $userRoles): ?string
    {
        foreach (self::MANAGEMENT_ROUTES as $role => $path) {
            if (in_array($role, $userRoles)) {
                return $path;
            }
        }
        return null;
    }

    private function resolveOperationalPath(array $userRoles): ?string
    {
        foreach (self::OPERATIONAL_ROUTES as $role => $path) {
            if (in_array($role, $userRoles)) {
                return $path;
            }
        }
        return null;
    }

    private function resolveTargetPath(array $userRoles): string
    {
        $allRoutes = array_merge(self::MANAGEMENT_ROUTES, self::OPERATIONAL_ROUTES);

        foreach ($allRoutes as $role => $path) {
            if (in_array($role, $userRoles)) {
                return $path;
            }
        }

        return '/cabinet/dashboard';
    }

    private function canRedirectToSubdomain($user): bool
    {
        if (! $user->organization?->subdomain) {
            return false;
        }

        $baseDomain = config('forris.base_domain');

        return ! empty($baseDomain);
    }

    private function redirectToSubdomain(Request $request, string $subdomain, string $targetPath): \Illuminate\Http\RedirectResponse
    {
        $baseDomain = config('forris.base_domain');
        $scheme = $request->isSecure() ? 'https' : 'http';
        $port = $request->getPort();
        $portSuffix = in_array($port, [80, 443]) ? '' : ':' . $port;

        return redirect("{$scheme}://{$subdomain}.{$baseDomain}{$portSuffix}{$targetPath}");
    }
}
