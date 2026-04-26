<?php

declare(strict_types=1);

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('customer')->check()) {
            $slug = $request->route('slug');

            return redirect()->route('shop.login', ['slug' => $slug]);
        }

        return $next($request);
    }
}
