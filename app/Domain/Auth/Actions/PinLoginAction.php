<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Branch;
use Illuminate\Validation\ValidationException;

class PinLoginAction
{
    /**
     * Attempt to authenticate a user with PIN code.
     * Used for quick POS terminal login.
     *
     * @throws ValidationException
     */
    public function execute(string $pinCode, int $branchId, ?string $ip = null): array
    {
        // Find branch
        $branch = Branch::find($branchId);

        if (!$branch || !$branch->is_active) {
            throw ValidationException::withMessages([
                'branch_id' => ['Филиал не найден или неактивен.'],
            ]);
        }

        // Find user by PIN within the organization
        $user = User::where('organization_id', $branch->organization_id)
            ->where('pin_code', $pinCode)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'pin_code' => ['Неверный PIN-код.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'pin_code' => ['Аккаунт деактивирован.'],
            ]);
        }

        // Check if user can access this branch
        if (!$user->canAccessBranch($branchId)) {
            throw ValidationException::withMessages([
                'pin_code' => ['У вас нет доступа к этому филиалу.'],
            ]);
        }

        // Update last login info
        $user->updateLastLogin($ip);

        // Create token with limited abilities for POS
        $token = $user->createToken('pos-terminal', ['pos:*']);

        return [
            'user' => $user,
            'branch' => $branch,
            'token' => $token->plainTextToken,
            'permissions' => $user->getAllPermissions($branchId),
        ];
    }
}
