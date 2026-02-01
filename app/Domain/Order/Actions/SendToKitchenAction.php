<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\OrderItemStatus;
use Illuminate\Support\Facades\DB;

class SendToKitchenAction
{
    public function execute(Order $order, ?array $itemIds = null): Order
    {
        return DB::transaction(function () use ($order, $itemIds) {
            $query = $order->items()->where('status', OrderItemStatus::PENDING);

            if ($itemIds) {
                $query->whereIn('id', $itemIds);
            }

            $items = $query->get();

            foreach ($items as $item) {
                $item->sendToKitchen();
            }

            // Update order status if needed
            if ($order->status === OrderStatus::NEW) {
                $order->transitionTo(OrderStatus::ACCEPTED);
            }

            // Broadcast to KDS (event will be dispatched later)
            // event(new OrderItemsSentToKitchen($order, $items));

            return $order->fresh(['items']);
        });
    }
}
