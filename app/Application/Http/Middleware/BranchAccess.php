<?php

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchAccess
{
    /**
     * Handle an incoming request.
     * Validates that user has access to the requested branch.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Требуется авторизация.',
            ], 401);
        }

        // Get branch_id from request (route param, query, or header)
        $branchId = $request->route('branch_id')
            ?? $request->route('branch')?->id
            ?? $request->input('branch_id')
            ?? $request->header('X-Branch-Id');

        if ($branchId && !$user->canAccessBranch((int) $branchId)) {
            return response()->json([
                'message' => 'У вас нет доступа к этому филиалу.',
            ], 403);
        }

        // Set current branch context
        if ($branchId) {
            app()->singleton('current.branch_id', fn() => (int) $branchId);
        }

        return $next($request);
    }
}
