<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Floor\Models\Table;
use App\Domain\Payment\Models\CashShift;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\OrderType;
use App\Support\Enums\OrderSource;
use App\Support\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // Get current cash shift
            $cashShift = CashShift::getCurrentForBranch($data['branch_id']);

            if (!$cashShift) {
                throw new \Exception('Нет открытой кассовой смены.');
            }

            $order = Order::create([
                'branch_id' => $data['branch_id'],
                'table_id' => $data['table_id'] ?? null,
                'waiter_id' => $data['waiter_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'cash_shift_id' => $cashShift->id,
                'type' => $data['type'] ?? OrderType::DINE_IN,
                'source' => $data['source'] ?? OrderSource::POS,
                'status' => OrderStatus::NEW,
                'payment_status' => PaymentStatus::UNPAID,
                'guests_count' => $data['guests_count'] ?? 1,
                'notes' => $data['notes'] ?? null,
            ]);

            // Occupy table if dine-in
            if ($order->table_id && $order->type === OrderType::DINE_IN) {
                $order->table->occupy();
            }

            return $order;
        });
    }
}
