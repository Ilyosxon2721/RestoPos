<?php

namespace App\Application\Http\Controllers\Api\V1\Delivery;

use App\Application\Http\Controllers\Controller;
use App\Domain\Delivery\Models\Courier;
use App\Support\Enums\CourierStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $couriers = Courier::query()
            ->with(['employee', 'activeDeliveries'])
            ->when($request->boolean('active_only'), fn($q) => $q->active())
            ->when($request->boolean('available_only'), fn($q) => $q->available())
            ->when($request->boolean('online_only'), fn($q) => $q->online())
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $couriers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|uuid|exists:employees,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['branch_id'] = $request->user()->current_branch_id;
        $validated['status'] = CourierStatus::Offline;

        $courier = Courier::create($validated);

        return response()->json(['data' => $courier], 201);
    }

    public function show(Courier $courier): JsonResponse
    {
        $courier->load(['employee', 'activeDeliveries.order']);

        return response()->json(['data' => $courier]);
    }

    public function update(Request $request, Courier $courier): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|uuid|exists:employees,id',
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $courier->update($validated);

        return response()->json(['data' => $courier]);
    }

    public function destroy(Courier $courier): JsonResponse
    {
        if ($courier->activeDeliveries()->exists()) {
            return response()->json([
                'message' => 'Невозможно удалить курьера с активными доставками'
            ], 422);
        }

        $courier->delete();

        return response()->json(null, 204);
    }

    public function updateLocation(Request $request, Courier $courier): JsonResponse
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $courier->updateLocation($validated['lat'], $validated['lng']);

        return response()->json(['data' => $courier->fresh()]);
    }

    public function setStatus(Request $request, Courier $courier): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:available,busy,offline',
        ]);

        $courier->update(['status' => CourierStatus::from($validated['status'])]);

        return response()->json(['data' => $courier->fresh()]);
    }

    public function activeDeliveries(Courier $courier): JsonResponse
    {
        $deliveries = $courier->activeDeliveries()
            ->with(['order', 'customer', 'deliveryZone'])
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => $deliveries]);
    }
}
