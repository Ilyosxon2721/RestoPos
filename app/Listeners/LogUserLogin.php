<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\Log;

class LogUserLogin
{
    public function handle(UserLoggedIn $event): void
    {
        Log::info('Пользователь вошёл в систему', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip_address' => $event->ipAddress,
        ]);

        // Обновляем информацию о последнем входе
        $event->user->updateLastLogin($event->ipAddress);
    }
}
