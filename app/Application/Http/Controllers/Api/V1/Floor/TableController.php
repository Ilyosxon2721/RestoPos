<?php

namespace App\Application\Http\Controllers\Api\V1\Floor;

use App\Application\Http\Controllers\Controller;
use App\Domain\Floor\Models\Table;
use App\Domain\Floor\Models\Hall;
use App\Support\Enums\TableStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * List all tables.
     */
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $tables = Table::query()
            ->where('branch_id', $branchId)
            ->when($request->has('hall_id'), fn($q) => $q->inHall($request->input('hall_id')))
            ->when($request->boolean('active_only'), fn($q) => $q->active())
            ->when($request->has('status'), fn($q) => $q->where('status', $request->input('status')))
            ->with(['hall', 'currentOrder'])
            ->ordered()
            ->get();

        return response()->json([
            'data' => $tables,
        ]);
    }

    /**
     * Get single table with current order.
     */
    public function show(Table $table): JsonResponse
    {
        $table->load(['hall', 'currentOrder.items.product', 'reservations' => fn($q) => $q->today()->upcoming()]);

        return response()->json([
            'data' => $table,
        ]);
    }

    /**
     * Create new table.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'hall_id' => 'required|exists:halls,id',
            'name' => 'nullable|string|max:255',
            'number' => 'required|integer|min:1',
            'capacity' => 'required|integer|min:1',
            'min_capacity' => 'nullable|integer|min:1',
            'pos_x' => 'nullable|integer',
            'pos_y' => 'nullable|integer',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'shape' => 'nullable|string|in:rectangle,circle,square',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $table = Table::create([
            'organization_id' => $request->user()->organization_id,
            'branch_id' => $request->input('branch_id'),
            'hall_id' => $request->input('hall_id'),
            'name' => $request->input('name'),
            'number' => $request->input('number'),
            'capacity' => $request->input('capacity'),
            'min_capacity' => $request->input('min_capacity', 1),
            'status' => TableStatus::FREE,
            'pos_x' => $request->input('pos_x', 0),
            'pos_y' => $request->input('pos_y', 0),
            'width' => $request->input('width', 80),
            'height' => $request->input('height', 80),
            'shape' => $request->input('shape', 'rectangle'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Стол успешно создан.',
            'data' => $table,
        ], 201);
    }

    /**
     * Update table.
     */
    public function update(Request $request, Table $table): JsonResponse
    {
        $request->validate([
            'hall_id' => 'sometimes|exists:halls,id',
            'name' => 'nullable|string|max:255',
            'number' => 'sometimes|integer|min:1',
            'capacity' => 'sometimes|integer|min:1',
            'min_capacity' => 'nullable|integer|min:1',
            'pos_x' => 'nullable|integer',
            'pos_y' => 'nullable|integer',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'shape' => 'nullable|string|in:rectangle,circle,square',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $table->update($request->only([
            'hall_id', 'name', 'number', 'capacity', 'min_capacity',
            'pos_x', 'pos_y', 'width', 'height', 'shape', 'sort_order', 'is_active'
        ]));

        return response()->json([
            'message' => 'Стол успешно обновлён.',
            'data' => $table,
        ]);
    }

    /**
     * Update table status.
     */
    public function updateStatus(Request $request, Table $table): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:free,occupied,reserved,unavailable',
        ]);

        $table->update(['status' => $request->input('status')]);

        return response()->json([
            'message' => 'Статус стола обновлён.',
            'data' => $table,
        ]);
    }

    /**
     * Update table position (for floor map editor).
     */
    public function updatePosition(Request $request, Table $table): JsonResponse
    {
        $request->validate([
            'pos_x' => 'required|integer',
            'pos_y' => 'required|integer',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
        ]);

        $table->update($request->only(['pos_x', 'pos_y', 'width', 'height']));

        return response()->json([
            'message' => 'Позиция стола обновлена.',
        ]);
    }

    /**
     * Bulk update table positions.
     */
    public function bulkUpdatePositions(Request $request): JsonResponse
    {
        $request->validate([
            'tables' => 'required|array',
            'tables.*.id' => 'required|exists:tables,id',
            'tables.*.pos_x' => 'required|integer',
            'tables.*.pos_y' => 'required|integer',
        ]);

        foreach ($request->input('tables') as $tableData) {
            Table::where('id', $tableData['id'])
                ->where('organization_id', $request->user()->organization_id)
                ->update([
                    'pos_x' => $tableData['pos_x'],
                    'pos_y' => $tableData['pos_y'],
                ]);
        }

        return response()->json([
            'message' => 'Позиции столов обновлены.',
        ]);
    }

    /**
     * Delete table.
     */
    public function destroy(Table $table): JsonResponse
    {
        // Check if table has active order
        if ($table->currentOrder) {
            return response()->json([
                'message' => 'Нельзя удалить стол с активным заказом.',
            ], 422);
        }

        $table->delete();

        return response()->json([
            'message' => 'Стол успешно удалён.',
        ]);
    }
}
