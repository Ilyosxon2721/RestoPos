<?php

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Validates that user has the required permission.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
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

        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission, $branchId)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Недостаточно прав для выполнения операции.',
            'required_permissions' => $permissions,
        ], 403);
    }
}
