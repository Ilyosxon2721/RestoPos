<?php

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizationScope
{
    /**
     * Handle an incoming request.
     * Sets the current organization context for the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            // Set organization context globally
            app()->singleton('current.organization', fn () => $user->organization);
            app()->singleton('current.organization_id', fn () => $user->organization_id);
        }

        return $next($request);
    }
}
