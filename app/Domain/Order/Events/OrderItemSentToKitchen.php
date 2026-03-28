<?php

declare(strict_types=1);

namespace App\Domain\Order\Events;

use App\Domain\Order\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OrderItemSentToKitchen implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly Collection $items,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('kitchen'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.items.sent-to-kitchen';
    }
}
