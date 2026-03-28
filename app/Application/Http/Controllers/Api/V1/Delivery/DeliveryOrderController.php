<?php

namespace App\Application\Http\Controllers\Api\V1\Delivery;

use App\Application\Http\Controllers\Controller;
use App\Domain\Delivery\Models\Courier;
use App\Domain\Delivery\Models\DeliveryOrder;
use App\Domain\Delivery\Models\DeliveryZone;
use App\Support\Enums\DeliveryStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $deliveries = DeliveryOrder::query()
            ->with(['order', 'customer', 'courier', 'deliveryZone'])
            ->when($request->input('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->boolean('active_only'), fn($q) => $q->active())
            ->when($request->boolean('pending_only'), fn($q) => $q->pending())
            ->when($request->input('courier_id'), fn($q, $id) => $q->forCourier($id))
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($deliveries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|uuid|exists:orders,id',
            'customer_id' => 'nullable|uuid|exists:customers,id',
            'address' => 'required|string|max:500',
            'address_details' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'scheduled_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['status'] = DeliveryStatus::PENDING;

        // Find delivery zone
        $zones = DeliveryZone::active()->get();
        foreach ($zones as $zone) {
            if ($zone->containsPoint($validated['latitude'], $validated['longitude'])) {
                $validated['delivery_zone_id'] = $zone->id;
                $validated['delivery_fee'] = $zone->delivery_price;
                break;
            }
        }

        $delivery = DeliveryOrder::create($validated);

        return response()->json(['data' => $delivery->load(['order', 'customer', 'deliveryZone'])], 201);
    }

    public function show(DeliveryOrder $deliveryOrder): JsonResponse
    {
        $deliveryOrder->load(['order.items', 'customer', 'courier', 'deliveryZone']);

        return response()->json(['data' => $deliveryOrder]);
    }

    public function update(Request $request, DeliveryOrder $deliveryOrder): JsonResponse
    {
        $validated = $request->validate([
            'address' => 'sometimes|string|max:500',
            'address_details' => 'nullable|string|max:255',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'contact_name' => 'sometimes|string|max:255',
            'contact_phone' => 'sometimes|string|max:20',
            'scheduled_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $deliveryOrder->update($validated);

        return response()->json(['data' => $deliveryOrder->fresh()]);
    }

    public function assignCourier(Request $request, DeliveryOrder $deliveryOrder): JsonResponse
    {
        $validated = $request->validate([
            'courier_id' => 'required|uuid|exists:couriers,id',
        ]);

        $courier = Courier::findOrFail($validated['courier_id']);
        $deliveryOrder->assignCourier($courier);

        return response()->json(['data' => $deliveryOrder->fresh(['courier'])]);
    }

    public function pickUp(DeliveryOrder $deliveryOrder): JsonResponse
    {
        if (!$deliveryOrder->courier_id) {
            return response()->json([
                'message' => 'Сначала назначьте курьера'
            ], 422);
        }

        $deliveryOrder->markPickedUp();

        return response()->json(['data' => $deliveryOrder->fresh()]);
    }

    public function deliver(DeliveryOrder $deliveryOrder): JsonResponse
    {
        if ($deliveryOrder->status !== DeliveryStatus::PICKED_UP &&
            $deliveryOrder->status !== DeliveryStatus::IN_TRANSIT) {
            return response()->json([
                'message' => 'Заказ должен быть забран перед доставкой'
            ], 422);
        }

        $deliveryOrder->markDelivered();

        return response()->json(['data' => $deliveryOrder->fresh()]);
    }

    public function cancel(Request $request, DeliveryOrder $deliveryOrder): JsonResponse
    {
        if ($deliveryOrder->status === DeliveryStatus::DELIVERED) {
            return response()->json([
                'message' => 'Нельзя отменить доставленный заказ'
            ], 422);
        }

        $reason = $request->input('reason');
        $deliveryOrder->cancel($reason);

        return response()->json(['data' => $deliveryOrder->fresh()]);
    }

    public function rate(Request $request, DeliveryOrder $deliveryOrder): JsonResponse
    {
        if ($deliveryOrder->status !== DeliveryStatus::DELIVERED) {
            return response()->json([
                'message' => 'Можно оценить только доставленный заказ'
            ], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $deliveryOrder->setRating($validated['rating'], $validated['comment'] ?? null);

        return response()->json(['data' => $deliveryOrder->fresh()]);
    }
}
