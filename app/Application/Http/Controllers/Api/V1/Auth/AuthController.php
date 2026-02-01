<?php

namespace App\Application\Http\Controllers\Api\V1\Auth;

use App\Application\Http\Controllers\Controller;
use App\Domain\Auth\Actions\LoginAction;
use App\Domain\Auth\Actions\LogoutAction;
use App\Domain\Auth\Actions\PinLoginAction;
use App\Domain\Auth\Actions\RegisterOrganizationAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login with email/phone and password.
     */
    public function login(Request $request, LoginAction $action): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        try {
            $result = $action->execute(
                $request->only(['login', 'password', 'device_name']),
                $request->ip()
            );

            return response()->json([
                'message' => 'Успешная авторизация.',
                'data' => [
                    'user' => $this->formatUser($result['user']),
                    'token' => $result['token'],
                    'expires_at' => $result['expires_at'],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка авторизации.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Login with PIN code (for POS terminal).
     */
    public function pinLogin(Request $request, PinLoginAction $action): JsonResponse
    {
        $request->validate([
            'pin_code' => 'required|string|size:4',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        try {
            $result = $action->execute(
                $request->input('pin_code'),
                $request->input('branch_id'),
                $request->ip()
            );

            return response()->json([
                'message' => 'Успешная авторизация.',
                'data' => [
                    'user' => $this->formatUser($result['user']),
                    'branch' => [
                        'id' => $result['branch']->id,
                        'name' => $result['branch']->name,
                    ],
                    'token' => $result['token'],
                    'permissions' => $result['permissions'],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка авторизации.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Register new organization.
     */
    public function register(Request $request, RegisterOrganizationAction $action): JsonResponse
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'timezone' => 'nullable|string|timezone',
            'currency' => 'nullable|string|size:3',
            'locale' => 'nullable|string|in:ru,uz,en',
        ]);

        $result = $action->execute($request->all());

        return response()->json([
            'message' => 'Организация успешно зарегистрирована.',
            'data' => [
                'organization' => [
                    'id' => $result['organization']->id,
                    'uuid' => $result['organization']->uuid,
                    'name' => $result['organization']->name,
                    'subscription_plan' => $result['organization']->subscription_plan,
                    'subscription_ends_at' => $result['organization']->subscription_ends_at,
                ],
                'branch' => [
                    'id' => $result['branch']->id,
                    'name' => $result['branch']->name,
                ],
                'user' => $this->formatUser($result['user']),
                'token' => $result['token'],
            ],
        ], 201);
    }

    /**
     * Logout current user.
     */
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute($request->user(), false);

        return response()->json([
            'message' => 'Вы успешно вышли из системы.',
        ]);
    }

    /**
     * Logout from all devices.
     */
    public function logoutAll(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute($request->user(), true);

        return response()->json([
            'message' => 'Вы вышли со всех устройств.',
        ]);
    }

    /**
     * Get current user info.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['organization', 'roles.permissions']);

        return response()->json([
            'data' => [
                'user' => $this->formatUser($user),
                'organization' => [
                    'id' => $user->organization->id,
                    'uuid' => $user->organization->uuid,
                    'name' => $user->organization->name,
                    'logo' => $user->organization->logo,
                    'subscription_plan' => $user->organization->subscription_plan,
                    'subscription_ends_at' => $user->organization->subscription_ends_at,
                ],
                'roles' => $user->roles->map(fn($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'branch_id' => $role->pivot->branch_id,
                ]),
                'permissions' => $user->getAllPermissions(),
                'accessible_branches' => $user->getAccessibleBranchIds(),
            ],
        ]);
    }

    /**
     * Format user for response.
     */
    private function formatUser($user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'email' => $user->email,
            'phone' => $user->phone,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'initials' => $user->initials,
            'avatar' => $user->avatar,
            'locale' => $user->locale,
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at,
        ];
    }
}
