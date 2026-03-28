<?php

namespace App\Domain\Order\Services;

use App\Domain\Order\Models\Order;

class OrderCalculationService
{
    /**
     * Apply discount to order.
     */
    public function applyDiscount(Order $order, float $amount = 0, float $percent = 0): Order
    {
        if (!$order->canModify()) {
            throw new \Exception('Заказ нельзя изменить.');
        }

        $order->update([
            'discount_amount' => $amount,
            'discount_percent' => $percent,
        ]);

        $order->calculateTotals();

        return $order->fresh();
    }

    /**
     * Apply service charge to order.
     */
    public function applyServiceCharge(Order $order, float $amount): Order
    {
        if (!$order->canModify()) {
            throw new \Exception('Заказ нельзя изменить.');
        }

        $order->update(['service_charge' => $amount]);
        $order->calculateTotals();

        return $order->fresh();
    }

    /**
     * Calculate suggested tip.
     */
    public function getSuggestedTips(Order $order): array
    {
        $subtotal = $order->subtotal;

        return [
            '10%' => round($subtotal * 0.10, 2),
            '15%' => round($subtotal * 0.15, 2),
            '20%' => round($subtotal * 0.20, 2),
        ];
    }

    /**
     * Split order by items.
     */
    public function splitByItems(Order $order, array $itemIds): Order
    {
        if (!$order->canModify()) {
            throw new \Exception('Заказ нельзя изменить.');
        }

        // Create new order with selected items
        $newOrder = $order->replicate(['order_number', 'subtotal', 'total_amount']);
        $newOrder->save();

        // Move items to new order
        $order->items()->whereIn('id', $itemIds)->update(['order_id' => $newOrder->id]);

        // Recalculate both orders
        $order->calculateTotals();
        $newOrder->calculateTotals();

        return $newOrder;
    }

    /**
     * Split order equally by guests.
     */
    public function splitEqually(Order $order, int $guests): array
    {
        $total = $order->total_amount;
        $perPerson = round($total / $guests, 2);
        $remainder = $total - ($perPerson * $guests);

        $splits = [];
        for ($i = 1; $i <= $guests; $i++) {
            $amount = $perPerson;
            if ($i === 1) {
                $amount += $remainder; // First guest pays remainder
            }
            $splits["Гость {$i}"] = $amount;
        }

        return $splits;
    }

    /**
     * Merge orders.
     */
    public function mergeOrders(Order $targetOrder, Order $sourceOrder): Order
    {
        if (!$targetOrder->canModify() || !$sourceOrder->canModify()) {
            throw new \Exception('Один из заказов нельзя изменить.');
        }

        // Move all items from source to target
        $sourceOrder->items()->update(['order_id' => $targetOrder->id]);

        // Move payments if any
        $sourceOrder->payments()->update(['order_id' => $targetOrder->id]);

        // Delete source order
        $sourceOrder->delete();

        // Recalculate target order
        $targetOrder->calculateTotals();
        $targetOrder->updatePaymentStatus();

        return $targetOrder->fresh(['items', 'payments']);
    }

    /**
     * Transfer order to another table.
     */
    public function transferToTable(Order $order, int $tableId): Order
    {
        if (!$order->canModify()) {
            throw new \Exception('Заказ нельзя изменить.');
        }

        // Release old table
        if ($order->table_id) {
            $order->table->release();
        }

        // Update order with new table
        $order->update(['table_id' => $tableId]);

        // Occupy new table
        $order->table->occupy();

        return $order->fresh(['table']);
    }
}
