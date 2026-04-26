<?php

namespace App\Application\Http\Controllers\Api\V1\Floor;

use App\Application\Http\Controllers\Controller;
use App\Domain\Floor\Models\Hall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HallController extends Controller
{
    /**
     * List all halls with tables.
     */
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $halls = Hall::query()
            ->where('branch_id', $branchId)
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->with(['tables' => fn ($q) => $q->when($request->boolean('active_only'), fn ($sq) => $sq->active())->ordered()])
            ->ordered()
            ->get();

        // Add statistics
        $halls->each(function ($hall) {
            $hall->statistics = [
                'total_tables' => $hall->activeTables()->count(),
                'free_tables' => $hall->getFreeTablesCount(),
                'occupied_tables' => $hall->getOccupiedTablesCount(),
                'total_capacity' => $hall->getTotalCapacity(),
            ];
        });

        return response()->json([
            'data' => $halls,
        ]);
    }

    /**
     * Get hall with tables for floor map.
     */
    public function show(Hall $hall): JsonResponse
    {
        $hall->load(['tables' => fn ($q) => $q->active()->ordered()]);

        return response()->json([
            'data' => $hall,
        ]);
    }

    /**
     * Create new hall.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'floor' => 'nullable|integer',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $hall = Hall::create([
            'organization_id' => $request->user()->organization_id,
            'branch_id' => $request->input('branch_id'),
            'name' => $request->input('name'),
            'floor' => $request->input('floor', 1),
            'description' => $request->input('description'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Зал успешно создан.',
            'data' => $hall,
        ], 201);
    }

    /**
     * Update hall.
     */
    public function update(Request $request, Hall $hall): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'floor' => 'nullable|integer',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $hall->update($request->only([
            'name', 'floor', 'description', 'sort_order', 'is_active',
        ]));

        return response()->json([
            'message' => 'Зал успешно обновлён.',
            'data' => $hall,
        ]);
    }

    /**
     * Delete hall.
     */
    public function destroy(Hall $hall): JsonResponse
    {
        // Check if hall has active orders
        $hasActiveOrders = $hall->tables()
            ->whereHas('currentOrder')
            ->exists();

        if ($hasActiveOrders) {
            return response()->json([
                'message' => 'Нельзя удалить зал с активными заказами.',
            ], 422);
        }

        $hall->tables()->delete();
        $hall->delete();

        return response()->json([
            'message' => 'Зал успешно удалён.',
        ]);
    }
}
