<?php

declare(strict_types=1);

namespace App\Domain\Auth\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedIn implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $ip,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('auth'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.logged-in';
    }
}
