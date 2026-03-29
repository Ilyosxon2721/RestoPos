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
        $subdomain = $this->extractSubdomain($request);

        // No subdomain detected — this is the main domain
        if (! $subdomain) {
            return $next($request);
        }

        $organization = Organization::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (! $organization) {
            abort(404, 'Организация не найдена. Проверьте адрес.');
        }

        // Bind organization to container for the entire request
        app()->instance('tenant', $organization);
        app()->instance('tenant.id', $organization->id);

        // Share with views
        view()->share('tenant', $organization);

        return $next($request);
    }

    /**
     * Check if current request is on a tenant subdomain.
     */
    public static function hasTenant(): bool
    {
        return app()->bound('tenant');
    }

    private function extractSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $baseDomain = config('restopos.base_domain');

        // Match: subdomain.restopos.uz, subdomain.resto.test, etc.
        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);

            // Skip admin subdomain — handled separately
            if ($subdomain === config('restopos.admin_subdomain')) {
                return null;
            }

            return $subdomain ?: null;
        }

        // For local development: support ?tenant=subdomain query parameter
        if (app()->environment('local') && $request->has('tenant')) {
            return $request->query('tenant');
        }

        return null;
    }
}
