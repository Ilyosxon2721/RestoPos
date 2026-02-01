<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\User;

class LogoutAction
{
    /**
     * Logout user by revoking current token.
     */
    public function execute(User $user, bool $allDevices = false): void
    {
        if ($allDevices) {
            // Revoke all tokens
            $user->tokens()->delete();
        } else {
            // Revoke only current token
            $user->currentAccessToken()->delete();
        }
    }
}
