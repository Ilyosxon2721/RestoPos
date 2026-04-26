<?php

namespace App\Application\Http\Controllers\Api\V1\Menu;

use App\Application\Http\Controllers\Controller;
use App\Domain\Menu\Actions\CreateProductAction;
use App\Domain\Menu\Actions\UpdateProductAction;
use App\Domain\Menu\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List all products.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->when($request->has('category_id'), fn ($q) => $q->inCategory($request->input('category_id')))
            ->when($request->has('workshop_id'), fn ($q) => $q->inWorkshop($request->input('workshop_id')))
            ->when($request->has('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->boolean('popular_only'), fn ($q) => $q->popular())
            ->when($request->has('search'), fn ($q) => $q->search($request->input('search')))
            ->with(['category', 'workshop', 'unit'])
            ->ordered();

        // Include prices for specific branch
        if ($request->has('branch_id')) {
            $branchId = $request->input('branch_id');
            $query->with(['prices' => fn ($q) => $q->where('branch_id', $branchId)->where('is_active', true)]);
        }

        $products = $request->boolean('paginate', true)
            ? $query->paginate($request->input('per_page', 20))
            : $query->get();

        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Get single product.
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        $product->load(['category', 'workshop', 'unit', 'prices', 'modifierGroups.modifiers']);

        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Create new product.
     */
    public function store(Request $request, CreateProductAction $action): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'workshop_id' => 'nullable|exists:workshops,id',
            'unit_id' => 'nullable|exists:units,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'type' => 'nullable|string|in:dish,drink,product,service,semi_finished',
            'cost_price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'prep_time' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            // Prices
            'prices' => 'nullable|array',
            'prices.*.branch_id' => 'required|exists:branches,id',
            'prices.*.price' => 'required|numeric|min:0',
            'prices.*.old_price' => 'nullable|numeric|min:0',
            // Modifier groups
            'modifier_groups' => 'nullable|array',
            'modifier_groups.*.modifier_group_id' => 'required|exists:modifier_groups,id',
            'modifier_groups.*.is_required' => 'nullable|boolean',
        ]);

        $product = $action->execute(
            [
                'organization_id' => $request->user()->organization_id,
                ...$request->only([
                    'category_id', 'workshop_id', 'unit_id', 'name', 'sku', 'barcode',
                    'description', 'image', 'type', 'cost_price', 'sort_order', 'prep_time',
                ]),
                'is_active' => $request->boolean('is_active', true),
                'is_popular' => $request->boolean('is_popular', false),
                'is_new' => $request->boolean('is_new', false),
            ],
            $request->input('prices', []),
            $request->input('modifier_groups', [])
        );

        return response()->json([
            'message' => 'Товар успешно создан.',
            'data' => $product,
        ], 201);
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product, UpdateProductAction $action): JsonResponse
    {
        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'workshop_id' => 'nullable|exists:workshops,id',
            'unit_id' => 'nullable|exists:units,id',
            'name' => 'sometimes|string|max:255',
            'sku' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'type' => 'nullable|string|in:dish,drink,product,service,semi_finished',
            'cost_price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'prep_time' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            // Prices
            'prices' => 'nullable|array',
            'prices.*.branch_id' => 'required|exists:branches,id',
            'prices.*.price' => 'required|numeric|min:0',
            'prices.*.old_price' => 'nullable|numeric|min:0',
            // Modifier groups
            'modifier_groups' => 'nullable|array',
            'modifier_groups.*.modifier_group_id' => 'required|exists:modifier_groups,id',
            'modifier_groups.*.is_required' => 'nullable|boolean',
        ]);

        $product = $action->execute(
            $product,
            $request->only([
                'category_id', 'workshop_id', 'unit_id', 'name', 'sku', 'barcode',
                'description', 'image', 'type', 'cost_price', 'sort_order', 'prep_time',
                'is_active', 'is_popular', 'is_new',
            ]),
            $request->has('prices') ? $request->input('prices') : null,
            $request->has('modifier_groups') ? $request->input('modifier_groups') : null
        );

        return response()->json([
            'message' => 'Товар успешно обновлён.',
            'data' => $product,
        ]);
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Товар успешно удалён.',
        ]);
    }

    /**
     * Update product price for branch.
     */
    public function updatePrice(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
        ]);

        $product->prices()->updateOrCreate(
            ['branch_id' => $request->input('branch_id')],
            [
                'organization_id' => $product->organization_id,
                'price' => $request->input('price'),
                'old_price' => $request->input('old_price'),
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'Цена успешно обновлена.',
        ]);
    }

    /**
     * Bulk update products status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'is_active' => 'required|boolean',
        ]);

        Product::whereIn('id', $request->input('ids'))
            ->where('organization_id', $request->user()->organization_id)
            ->update(['is_active' => $request->boolean('is_active')]);

        return response()->json([
            'message' => 'Статус товаров обновлён.',
        ]);
    }
}
