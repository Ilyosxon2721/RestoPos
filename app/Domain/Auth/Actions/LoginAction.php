<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    /**
     * Attempt to authenticate a user with email/phone and password.
     *
     * @throws ValidationException
     */
    public function execute(array $credentials, ?string $ip = null): array
    {
        $login = $credentials['login'] ?? $credentials['email'] ?? $credentials['phone'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$login || !$password) {
            throw ValidationException::withMessages([
                'login' => ['Укажите email/телефон и пароль.'],
            ]);
        }

        // Find user by email or phone
        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Неверные учётные данные.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Аккаунт деактивирован. Обратитесь к администратору.'],
            ]);
        }

        // Check if organization is active
        if (!$user->organization->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Организация деактивирована.'],
            ]);
        }

        // Update last login info
        $user->updateLastLogin($ip);

        // Create token
        $tokenName = $credentials['device_name'] ?? 'api-token';
        $token = $user->createToken($tokenName);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ];
    }
}
