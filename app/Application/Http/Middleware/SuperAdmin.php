<?php

declare(strict_types=1);

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('platform')->check()) {
            return redirect()->route('admin.login');
        }

        if (!auth('platform')->user()->is_active) {
            auth('platform')->logout();

            return redirect()->route('admin.login')->with('error', 'Аккаунт деактивирован');
        }

        return $next($request);
    }
}
