<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'organization' => \App\Application\Http\Middleware\OrganizationScope::class,
            'branch.access' => \App\Application\Http\Middleware\BranchAccess::class,
            'permission' => \App\Application\Http\Middleware\CheckPermission::class,
            'role' => \App\Application\Http\Middleware\CheckRole::class,
            'super_admin' => \App\Application\Http\Middleware\SuperAdmin::class,
            'client_role' => \App\Application\Http\Middleware\ClientRole::class,
            'tenant' => \App\Application\Http\Middleware\ResolveSubdomain::class,
            'customer.auth' => \App\Application\Http\Middleware\CustomerAuth::class,
        ]);

        // Resolve tenant (organization) from subdomain on every web request
        $middleware->web(append: [
            \App\Application\Http\Middleware\ResolveSubdomain::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
