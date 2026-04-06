<?php

declare(strict_types=1);

namespace App\Application\Http\Middleware;

use App\Domain\Organization\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $subdomain = $this->extractSubdomain($request);

            if (! $subdomain) {
                return $next($request);
            }

            $organization = Organization::where('subdomain', $subdomain)->first();

            if (! $organization) {
                return response()->view('errors.organization-not-found', [], 404);
            }

            if (! $organization->is_active) {
                return response()->view('errors.organization-inactive', [
                    'organizationName' => $organization->name,
                ], 403);
            }

            app()->instance('tenant', $organization);
            app()->instance('tenant.id', $organization->id);

            view()->share('tenant', $organization);
        } catch (\Throwable $e) {
            // При любой ошибке (БД недоступна, конфиг не найден и т.д.)
            // пропускаем определение tenant — пусть работает как главный домен
            report($e);
        }

        return $next($request);
    }

    public static function hasTenant(): bool
    {
        return app()->bound('tenant');
    }

    private function extractSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $baseDomain = config('forris.base_domain');

        // Если конфиг не настроен — пропускаем
        if (empty($baseDomain)) {
            return null;
        }

        // Match: subdomain.pos.forris.uz, subdomain.pos.forris.test, etc.
        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);

            if ($subdomain === config('forris.admin_subdomain')) {
                return null;
            }

            return $subdomain ?: null;
        }

        // Для локальной разработки: ?tenant=subdomain
        if (app()->environment('local') && $request->has('tenant')) {
            return $request->query('tenant');
        }

        return null;
    }
}
