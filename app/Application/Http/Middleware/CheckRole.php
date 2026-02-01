<?php

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Validates that user has the required role.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Требуется авторизация.',
            ], 401);
        }

        // Get current branch context
        $branchId = app()->bound('current.branch_id')
            ? app('current.branch_id')
            : null;

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->hasRole($role, $branchId)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'У вас нет необходимой роли для выполнения операции.',
            'required_roles' => $roles,
        ], 403);
    }
}
