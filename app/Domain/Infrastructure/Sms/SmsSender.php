<?php

declare(strict_types=1);

namespace App\Domain\Infrastructure\Sms;

interface SmsSender
{
    /**
     * Send an SMS message.
     *
     * @param  string  $phone  Phone in E.164 (e.g. +998901234567).
     * @return bool True on accepted-by-gateway delivery.
     */
    public function send(string $phone, string $message): bool;
}
