<?php

namespace App\Application\Http\Controllers\Api\V1\Order;

use App\Application\Http\Controllers\Controller;
use App\Domain\Order\Actions\AddOrderItemAction;
use App\Domain\Order\Actions\CloseOrderAction;
use App\Domain\Order\Actions\CreateOrderAction;
use App\Domain\Order\Actions\SendToKitchenAction;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Services\OrderCalculationService;
use App\Support\Enums\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * List orders.
     */
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $query = Order::query()
            ->where('branch_id', $branchId)
            ->when($request->boolean('open_only'), fn ($q) => $q->open())
            ->when($request->boolean('today'), fn ($q) => $q->today())
            ->when($request->has('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->has('table_id'), fn ($q) => $q->forTable($request->input('table_id')))
            ->when($request->has('waiter_id'), fn ($q) => $q->where('waiter_id', $request->input('waiter_id')))
            ->with(['table.hall', 'waiter', 'customer'])
            ->latest();

        $orders = $request->boolean('paginate', true)
            ? $query->paginate($request->input('per_page', 20))
            : $query->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    /**
     * Get single order with items.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load([
            'table.hall',
            'waiter',
            'customer',
            'items.product',
            'items.modifiers',
            'payments',
        ]);

        return response()->json([
            'data' => $order,
        ]);
    }

    /**
     * Create new order.
     */
    public function store(Request $request, CreateOrderAction $action): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'table_id' => 'nullable|exists:tables,id',
            'customer_id' => 'nullable|exists:customers,id',
            'type' => 'nullable|string|in:dine_in,takeaway,delivery,preorder',
            'source' => 'nullable|string|in:pos,website,app,aggregator,phone,qr',
            'guests_count' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            $order = $action->execute([
                'waiter_id' => $request->user()->employee?->id,
                ...$request->only(['branch_id', 'table_id', 'customer_id', 'type', 'source', 'guests_count', 'notes']),
            ]);

            $order->load(['table.hall', 'waiter']);

            return response()->json([
                'message' => 'Заказ успешно создан.',
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Add item to order.
     */
    public function addItem(Request $request, Order $order, AddOrderItemAction $action): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|numeric|min:0.001',
            'unit_price' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'modifiers' => 'nullable|array',
            'modifiers.*.modifier_id' => 'required|exists:modifiers,id',
            'modifiers.*.name' => 'required|string',
            'modifiers.*.price' => 'required|numeric|min:0',
            'modifiers.*.quantity' => 'nullable|integer|min:1',
        ]);

        try {
            $item = $action->execute($order, $request->all());

            return response()->json([
                'message' => 'Позиция добавлена.',
                'data' => [
                    'item' => $item,
                    'order' => $order->fresh(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update order item.
     */
    public function updateItem(Request $request, Order $order, int $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'sometimes|numeric|min:0.001',
            'notes' => 'nullable|string',
        ]);

        $item = $order->items()->findOrFail($itemId);

        if (!$item->canCancel() && $request->has('quantity')) {
            return response()->json([
                'message' => 'Нельзя изменить количество отправленной позиции.',
            ], 422);
        }

        $item->update($request->only(['quantity', 'notes']));

        return response()->json([
            'message' => 'Позиция обновлена.',
            'data' => [
                'item' => $item,
                'order' => $order->fresh(),
            ],
        ]);
    }

    /**
     * Remove item from order.
     */
    public function removeItem(Order $order, int $itemId): JsonResponse
    {
        $item = $order->items()->findOrFail($itemId);

        if (!$item->canCancel()) {
            return response()->json([
                'message' => 'Нельзя удалить отправленную позицию.',
            ], 422);
        }

        $item->modifiers()->delete();
        $item->delete();

        return response()->json([
            'message' => 'Позиция удалена.',
            'data' => $order->fresh(),
        ]);
    }

    /**
     * Send items to kitchen.
     */
    public function sendToKitchen(Request $request, Order $order, SendToKitchenAction $action): JsonResponse
    {
        $request->validate([
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'exists:order_items,id',
        ]);

        try {
            $order = $action->execute($order, $request->input('item_ids'));

            return response()->json([
                'message' => 'Заказ отправлен на кухню.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Apply discount to order.
     */
    public function applyDiscount(Request $request, Order $order, OrderCalculationService $service): JsonResponse
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $order = $service->applyDiscount(
                $order,
                $request->input('amount', 0),
                $request->input('percent', 0)
            );

            return response()->json([
                'message' => 'Скидка применена.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Transfer order to another table.
     */
    public function transfer(Request $request, Order $order, OrderCalculationService $service): JsonResponse
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
        ]);

        try {
            $order = $service->transferToTable($order, $request->input('table_id'));

            return response()->json([
                'message' => 'Заказ перенесён.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Close order.
     */
    public function close(Order $order, CloseOrderAction $action): JsonResponse
    {
        try {
            $order = $action->execute($order);

            return response()->json([
                'message' => 'Заказ закрыт.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel order.
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if (!$order->canModify()) {
            return response()->json([
                'message' => 'Заказ нельзя отменить.',
            ], 422);
        }

        $order->transitionTo(OrderStatus::CANCELLED);
        $order->update(['notes' => $request->input('reason')]);

        // Release table
        if ($order->table_id) {
            $order->table->release();
        }

        return response()->json([
            'message' => 'Заказ отменён.',
            'data' => $order,
        ]);
    }
}
