<?php

declare(strict_types=1);

namespace App\Domain\Infrastructure\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PlayMobileSmsSender implements SmsSender
{
    public function __construct(
        private readonly string $endpoint,
        private readonly string $username,
        private readonly string $password,
        private readonly string $sender,
    ) {}

    public function send(string $phone, string $message): bool
    {
        $payload = [
            'messages' => [[
                'recipient' => ltrim($phone, '+'),
                'message-id' => bin2hex(random_bytes(8)),
                'sms' => [
                    'originator' => $this->sender,
                    'content' => ['text' => $message],
                ],
            ]],
        ];

        $response = Http::withBasicAuth($this->username, $this->password)
            ->timeout(10)
            ->post($this->endpoint.'/broker-api/send', $payload);

        if (!$response->successful()) {
            Log::error('PlayMobile SMS failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }
}
