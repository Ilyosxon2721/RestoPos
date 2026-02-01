<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\Models\Order;
use App\Support\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class CloseOrderAction
{
    public function execute(Order $order): Order
    {
        if (!$order->isPaid()) {
            throw new \Exception('Заказ не оплачен.');
        }

        return DB::transaction(function () use ($order) {
            $order->transitionTo(OrderStatus::COMPLETED);

            // Release table if dine-in
            if ($order->table_id) {
                $order->table->release();
            }

            return $order->fresh();
        });
    }
}
