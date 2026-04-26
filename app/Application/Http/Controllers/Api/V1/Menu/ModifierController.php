<?php

namespace App\Application\Http\Controllers\Api\V1\Menu;

use App\Application\Http\Controllers\Controller;
use App\Domain\Menu\Models\Modifier;
use App\Domain\Menu\Models\ModifierGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModifierController extends Controller
{
    /**
     * List all modifier groups.
     */
    public function index(Request $request): JsonResponse
    {
        $groups = ModifierGroup::query()
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->with(['modifiers' => fn ($q) => $q->when($request->boolean('active_only'), fn ($sq) => $sq->active())->ordered()])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $groups,
        ]);
    }

    /**
     * Get single modifier group.
     */
    public function show(ModifierGroup $modifierGroup): JsonResponse
    {
        $modifierGroup->load(['modifiers', 'products']);

        return response()->json([
            'data' => $modifierGroup,
        ]);
    }

    /**
     * Create modifier group.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_selections' => 'nullable|integer|min:0',
            'max_selections' => 'nullable|integer|min:0',
            'is_multiple' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            // Modifiers
            'modifiers' => 'nullable|array',
            'modifiers.*.name' => 'required|string|max:255',
            'modifiers.*.price' => 'nullable|numeric|min:0',
            'modifiers.*.is_default' => 'nullable|boolean',
        ]);

        $group = ModifierGroup::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $request->input('name'),
            'min_selections' => $request->input('min_selections', 0),
            'max_selections' => $request->input('max_selections', 0),
            'is_multiple' => $request->boolean('is_multiple', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Create modifiers
        foreach ($request->input('modifiers', []) as $index => $modifierData) {
            $group->modifiers()->create([
                'organization_id' => $request->user()->organization_id,
                'name' => $modifierData['name'],
                'price' => $modifierData['price'] ?? 0,
                'sort_order' => $index,
                'is_default' => $modifierData['is_default'] ?? false,
                'is_active' => true,
            ]);
        }

        $group->load('modifiers');

        return response()->json([
            'message' => 'Группа модификаторов создана.',
            'data' => $group,
        ], 201);
    }

    /**
     * Update modifier group.
     */
    public function update(Request $request, ModifierGroup $modifierGroup): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'min_selections' => 'nullable|integer|min:0',
            'max_selections' => 'nullable|integer|min:0',
            'is_multiple' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $modifierGroup->update($request->only([
            'name', 'min_selections', 'max_selections', 'is_multiple', 'is_active',
        ]));

        return response()->json([
            'message' => 'Группа модификаторов обновлена.',
            'data' => $modifierGroup,
        ]);
    }

    /**
     * Delete modifier group.
     */
    public function destroy(ModifierGroup $modifierGroup): JsonResponse
    {
        // Check if group is used by products
        if ($modifierGroup->products()->exists()) {
            return response()->json([
                'message' => 'Нельзя удалить группу модификаторов, привязанную к товарам.',
            ], 422);
        }

        $modifierGroup->modifiers()->delete();
        $modifierGroup->delete();

        return response()->json([
            'message' => 'Группа модификаторов удалена.',
        ]);
    }

    /**
     * Add modifier to group.
     */
    public function addModifier(Request $request, ModifierGroup $modifierGroup): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        $maxOrder = $modifierGroup->modifiers()->max('sort_order');

        $modifier = $modifierGroup->modifiers()->create([
            'organization_id' => $request->user()->organization_id,
            'name' => $request->input('name'),
            'price' => $request->input('price', 0),
            'sort_order' => ($maxOrder ?? 0) + 1,
            'is_default' => $request->boolean('is_default', false),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Модификатор добавлен.',
            'data' => $modifier,
        ], 201);
    }

    /**
     * Update modifier.
     */
    public function updateModifier(Request $request, Modifier $modifier): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $modifier->update($request->only([
            'name', 'price', 'sort_order', 'is_default', 'is_active',
        ]));

        return response()->json([
            'message' => 'Модификатор обновлён.',
            'data' => $modifier,
        ]);
    }

    /**
     * Delete modifier.
     */
    public function destroyModifier(Modifier $modifier): JsonResponse
    {
        $modifier->delete();

        return response()->json([
            'message' => 'Модификатор удалён.',
        ]);
    }
}
