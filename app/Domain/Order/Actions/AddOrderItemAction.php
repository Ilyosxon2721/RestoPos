<?php

namespace App\Domain\Order\Actions;

use App\Domain\Menu\Models\Product;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Support\Enums\OrderItemStatus;
use Illuminate\Support\Facades\DB;

class AddOrderItemAction
{
    public function execute(Order $order, array $data): OrderItem
    {
        if (!$order->canModify()) {
            throw new \Exception('Заказ нельзя изменить.');
        }

        return DB::transaction(function () use ($order, $data) {
            $product = Product::findOrFail($data['product_id']);

            // Get price for branch
            $price = $data['unit_price'] ?? $product->getSellingPrice($order->branch_id);

            $item = $order->items()->create([
                'product_id' => $product->id,
                'name' => $product->getTranslation('name', app()->getLocale()),
                'quantity' => $data['quantity'] ?? 1,
                'unit_price' => $price,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'status' => OrderItemStatus::PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            // Add modifiers if provided
            if (!empty($data['modifiers'])) {
                foreach ($data['modifiers'] as $modifierData) {
                    $item->modifiers()->create([
                        'modifier_id' => $modifierData['modifier_id'],
                        'name' => $modifierData['name'],
                        'price' => $modifierData['price'],
                        'quantity' => $modifierData['quantity'] ?? 1,
                    ]);
                }
                // Recalculate item total
                $item->calculateTotal();
                $item->save();
            }

            return $item->fresh(['modifiers']);
        });
    }
}
