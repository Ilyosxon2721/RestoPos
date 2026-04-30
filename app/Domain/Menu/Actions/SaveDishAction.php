<?php

declare(strict_types=1);

namespace App\Domain\Menu\Actions;

use App\Domain\Menu\Models\Product;
use App\Domain\Warehouse\Models\TechCard;
use Illuminate\Support\Facades\DB;

/**
 * Persists a Product + TechCard + composition + modifier-group attachments
 * in a single transaction. Recomputes cost_price from the new composition.
 */
final class SaveDishAction
{
    /**
     * @param  array<string, mixed>  $product  Product columns
     * @param  array<string, mixed>  $techCard  Tech card columns (output_quantity, output_unit_id, etc.)
     * @param  array<int, array<string, mixed>>  $items  Each: ingredient_id|semi_finished_id, unit_id?, preparation_method_id?, quantity, loss_percent
     * @param  array<int, array{group_id: int, is_required: bool, sort_order: int}>  $modifierGroups
     */
    public function execute(
        ?Product $product,
        array $product_attributes,
        array $techCard,
        array $items,
        array $modifierGroups,
    ): Product {
        return DB::transaction(function () use ($product, $product_attributes, $techCard, $items, $modifierGroups) {
            $product = $product
                ? tap($product)->update($product_attributes)
                : Product::create($product_attributes);

            $card = $product->techCard ?: new TechCard(['product_id' => $product->id]);
            $card->fill($techCard);
            $card->save();

            $card->items()->delete();
            foreach ($items as $index => $row) {
                $card->items()->create([
                    'ingredient_id' => $row['ingredient_id'] ?? null,
                    'semi_finished_id' => $row['semi_finished_id'] ?? null,
                    'unit_id' => $row['unit_id'] ?? null,
                    'preparation_method_id' => $row['preparation_method_id'] ?? null,
                    'quantity' => (float) $row['quantity'],
                    'loss_percent' => (float) ($row['loss_percent'] ?? 0),
                    'sort_order' => $index,
                ]);
            }

            $product->update([
                'cost_price' => $card->fresh('items.ingredient', 'items.semiFinished')->total_cost,
            ]);

            $sync = [];
            foreach ($modifierGroups as $row) {
                $sync[$row['group_id']] = [
                    'is_required' => (bool) ($row['is_required'] ?? false),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                ];
            }
            $product->modifierGroups()->sync($sync);

            return $product->fresh(['techCard.items', 'modifierGroups', 'tax', 'category', 'workshop']);
        });
    }
}
