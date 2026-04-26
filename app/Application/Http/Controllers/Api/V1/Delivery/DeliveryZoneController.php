<?php

namespace App\Application\Http\Controllers\Api\V1\Delivery;

use App\Application\Http\Controllers\Controller;
use App\Domain\Delivery\Models\DeliveryZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $zones = DeliveryZone::query()
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $zones]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'polygon' => 'required|array|min:3',
            'polygon.*.lat' => 'required|numeric',
            'polygon.*.lng' => 'required|numeric',
            'min_order_amount' => 'nullable|numeric|min:0',
            'delivery_price' => 'required|numeric|min:0',
            'free_delivery_from' => 'nullable|numeric|min:0',
            'estimated_time_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['branch_id'] = $request->user()->current_branch_id;

        $zone = DeliveryZone::create($validated);

        return response()->json(['data' => $zone], 201);
    }

    public function show(DeliveryZone $deliveryZone): JsonResponse
    {
        return response()->json(['data' => $deliveryZone]);
    }

    public function update(Request $request, DeliveryZone $deliveryZone): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'polygon' => 'sometimes|array|min:3',
            'polygon.*.lat' => 'required_with:polygon|numeric',
            'polygon.*.lng' => 'required_with:polygon|numeric',
            'min_order_amount' => 'nullable|numeric|min:0',
            'delivery_price' => 'sometimes|numeric|min:0',
            'free_delivery_from' => 'nullable|numeric|min:0',
            'estimated_time_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $deliveryZone->update($validated);

        return response()->json(['data' => $deliveryZone]);
    }

    public function destroy(DeliveryZone $deliveryZone): JsonResponse
    {
        $deliveryZone->delete();

        return response()->json(null, 204);
    }

    public function checkPoint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $zones = DeliveryZone::active()->get();

        foreach ($zones as $zone) {
            if ($zone->containsPoint($validated['lat'], $validated['lng'])) {
                return response()->json([
                    'data' => [
                        'zone' => $zone,
                        'available' => true,
                    ],
                ]);
            }
        }

        return response()->json([
            'data' => [
                'zone' => null,
                'available' => false,
            ],
        ]);
    }
}
