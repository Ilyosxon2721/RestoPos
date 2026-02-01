<?php

namespace App\Domain\Menu\Actions;

use App\Domain\Menu\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateProductAction
{
    public function execute(Product $product, array $data, ?array $prices = null, ?array $modifierGroups = null): Product
    {
        return DB::transaction(function () use ($product, $data, $prices, $modifierGroups) {
            $product->update($data);

            // Update prices if provided
            if ($prices !== null) {
                // Remove existing prices not in the new list
                $branchIds = collect($prices)->pluck('branch_id')->toArray();
                $product->prices()->whereNotIn('branch_id', $branchIds)->delete();

                // Update or create prices
                foreach ($prices as $priceData) {
                    $product->prices()->updateOrCreate(
                        ['branch_id' => $priceData['branch_id']],
                        [
                            'organization_id' => $product->organization_id,
                            'price' => $priceData['price'],
                            'old_price' => $priceData['old_price'] ?? null,
                            'is_active' => $priceData['is_active'] ?? true,
                        ]
                    );
                }
            }

            // Update modifier groups if provided
            if ($modifierGroups !== null) {
                $syncData = [];
                foreach ($modifierGroups as $index => $groupData) {
                    $syncData[$groupData['modifier_group_id']] = [
                        'is_required' => $groupData['is_required'] ?? false,
                        'sort_order' => $groupData['sort_order'] ?? $index,
                    ];
                }
                $product->modifierGroups()->sync($syncData);
            }

            return $product->fresh(['category', 'workshop', 'unit', 'prices', 'modifierGroups']);
        });
    }
}
