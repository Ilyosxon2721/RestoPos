<?php

declare(strict_types=1);

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    private const ROLE_ROUTES = [
        'owner' => 'cabinet.dashboard',
        'director' => 'manager.dashboard',
        'admin' => 'manager.dashboard',
        'accountant' => 'manager.reports',
        'head_waiter' => 'manager.dashboard',
        'cashier' => 'cashier.terminal',
        'waiter' => 'waiter.tables',
        'bartender' => 'cashier.terminal',
        'cook' => 'kitchen.display',
        'storekeeper' => 'warehouse-panel.stock',
        'courier' => 'waiter.orders',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Загружаем роли пользователя
        $userRoles = $user->roles()->pluck('slug')->toArray();

        foreach (self::ROLE_ROUTES as $role => $route) {
            if (in_array($role, $userRoles)) {
                try {
                    return redirect()->route($route);
                } catch (\Exception) {
                    // Маршрут не найден, пробуем следующий
                    continue;
                }
            }
        }

        // По умолчанию в dashboard
        return redirect()->route('cabinet.dashboard');
    }
}
