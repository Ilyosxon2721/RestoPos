<?php

namespace App\Application\Http\Controllers\Api\V1\Menu;

use App\Application\Http\Controllers\Controller;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Actions\CreateCategoryAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List all categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()
            ->when($request->boolean('root_only'), fn($q) => $q->root())
            ->when($request->boolean('active_only'), fn($q) => $q->active())
            ->when($request->has('parent_id'), fn($q) => $q->where('parent_id', $request->input('parent_id')))
            ->with($request->boolean('with_children') ? ['children' => fn($q) => $q->active()->ordered()] : [])
            ->ordered();

        $categories = $request->boolean('paginate')
            ? $query->paginate($request->input('per_page', 20))
            : $query->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get category tree.
     */
    public function tree(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->root()
            ->when($request->boolean('active_only'), fn($q) => $q->active())
            ->with(['descendants' => fn($q) => $q->when($request->boolean('active_only'), fn($sq) => $sq->active())->ordered()])
            ->ordered()
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get single category.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children', 'products' => fn($q) => $q->active()->ordered()->limit(10)]);

        return response()->json([
            'data' => $category,
        ]);
    }

    /**
     * Create new category.
     */
    public function store(Request $request, CreateCategoryAction $action): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $category = $action->execute([
            'organization_id' => $request->user()->organization_id,
            ...$request->only(['name', 'parent_id', 'slug', 'description', 'image', 'color', 'icon', 'sort_order']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Категория успешно создана.',
            'data' => $category,
        ], 201);
    }

    /**
     * Update category.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent setting parent to self or descendants
        if ($request->has('parent_id') && $request->input('parent_id')) {
            $descendantIds = $category->descendants()->pluck('id')->toArray();
            if (in_array($request->input('parent_id'), [...$descendantIds, $category->id])) {
                return response()->json([
                    'message' => 'Нельзя установить категорию как родителя самой себе или своему потомку.',
                ], 422);
            }
        }

        $category->update($request->only([
            'name', 'parent_id', 'slug', 'description', 'image', 'color', 'icon', 'sort_order', 'is_active'
        ]));

        return response()->json([
            'message' => 'Категория успешно обновлена.',
            'data' => $category,
        ]);
    }

    /**
     * Delete category.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has children
        if ($category->hasChildren()) {
            return response()->json([
                'message' => 'Нельзя удалить категорию с подкатегориями.',
            ], 422);
        }

        // Check if category has products
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Нельзя удалить категорию с товарами.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Категория успешно удалена.',
        ]);
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:categories,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->input('items') as $item) {
            Category::where('id', $item['id'])
                ->where('organization_id', $request->user()->organization_id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'message' => 'Порядок категорий обновлён.',
        ]);
    }
}
