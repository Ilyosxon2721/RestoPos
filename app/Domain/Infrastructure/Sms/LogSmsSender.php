<?php

declare(strict_types=1);

namespace App\Domain\Infrastructure\Sms;

use Illuminate\Support\Facades\Log;

final class LogSmsSender implements SmsSender
{
    public function send(string $phone, string $message): bool
    {
        Log::channel(config('logging.default'))->info('SMS (log driver)', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return true;
    }
}
