<?php

namespace App\Application\Http\Controllers\Api\V1\Kds;

use App\Application\Http\Controllers\Controller;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Support\Enums\OrderItemStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    public function orders(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');
        $workshopId = $request->input('workshop_id');

        $orders = Order::where('branch_id', $branchId)
            ->open()
            ->whereHas('items', function ($q) use ($workshopId) {
                $q->whereIn('status', [
                    OrderItemStatus::SENT,
                    OrderItemStatus::PREPARING,
                    OrderItemStatus::READY,
                ]);
                if ($workshopId) {
                    $q->whereHas('product', fn($pq) => $pq->where('workshop_id', $workshopId));
                }
            })
            ->with(['table', 'items' => function ($q) use ($workshopId) {
                $q->whereIn('status', [
                    OrderItemStatus::SENT,
                    OrderItemStatus::PREPARING,
                    OrderItemStatus::READY,
                ]);
                if ($workshopId) {
                    $q->whereHas('product', fn($pq) => $pq->where('workshop_id', $workshopId));
                }
                $q->with(['product.workshop', 'modifiers']);
            }])
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function startPreparing(OrderItem $item): JsonResponse
    {
        if ($item->status !== OrderItemStatus::SENT) {
            return response()->json(['message' => 'Позиция не может быть взята в работу.'], 422);
        }

        $item->markPreparing();

        return response()->json(['message' => 'Позиция взята в работу.', 'data' => $item]);
    }

    public function markReady(OrderItem $item): JsonResponse
    {
        if (!in_array($item->status, [OrderItemStatus::SENT, OrderItemStatus::PREPARING])) {
            return response()->json(['message' => 'Позиция не может быть отмечена готовой.'], 422);
        }

        $item->markReady();

        return response()->json(['message' => 'Позиция готова.', 'data' => $item]);
    }

    public function markServed(OrderItem $item): JsonResponse
    {
        if ($item->status !== OrderItemStatus::READY) {
            return response()->json(['message' => 'Позиция не готова.'], 422);
        }

        $item->markServed();

        return response()->json(['message' => 'Позиция подана.', 'data' => $item]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $stats = [
            'pending' => OrderItem::whereHas('order', fn($q) => $q->where('branch_id', $branchId)->open())
                ->where('status', OrderItemStatus::SENT)->count(),
            'preparing' => OrderItem::whereHas('order', fn($q) => $q->where('branch_id', $branchId)->open())
                ->where('status', OrderItemStatus::PREPARING)->count(),
            'ready' => OrderItem::whereHas('order', fn($q) => $q->where('branch_id', $branchId)->open())
                ->where('status', OrderItemStatus::READY)->count(),
            'avg_prep_time' => OrderItem::whereHas('order', fn($q) => $q->where('branch_id', $branchId))
                ->whereNotNull('prepared_at')
                ->whereDate('created_at', today())
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, sent_to_kitchen_at, prepared_at)) as avg_time')
                ->value('avg_time') ?? 0,
        ];

        return response()->json(['data' => $stats]);
    }
}
