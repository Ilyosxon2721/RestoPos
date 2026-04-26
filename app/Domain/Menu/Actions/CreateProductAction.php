<?php

namespace App\Domain\Menu\Actions;

use App\Domain\Menu\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function execute(array $data, array $prices = [], array $modifierGroups = []): Product
    {
        return DB::transaction(function () use ($data, $prices, $modifierGroups) {
            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSku($data['organization_id']);
            }

            // Set sort order if not provided
            if (!isset($data['sort_order'])) {
                $maxOrder = Product::where('organization_id', $data['organization_id'])
                    ->where('category_id', $data['category_id'])
                    ->max('sort_order');
                $data['sort_order'] = ($maxOrder ?? 0) + 1;
            }

            $product = Product::create($data);

            // Create prices for branches
            foreach ($prices as $priceData) {
                $product->prices()->create([
                    'organization_id' => $data['organization_id'],
                    'branch_id' => $priceData['branch_id'],
                    'price' => $priceData['price'],
                    'old_price' => $priceData['old_price'] ?? null,
                    'is_active' => $priceData['is_active'] ?? true,
                ]);
            }

            // Attach modifier groups
            foreach ($modifierGroups as $index => $groupData) {
                $product->modifierGroups()->attach($groupData['modifier_group_id'], [
                    'is_required' => $groupData['is_required'] ?? false,
                    'sort_order' => $groupData['sort_order'] ?? $index,
                ]);
            }

            return $product->load(['category', 'workshop', 'unit', 'prices', 'modifierGroups']);
        });
    }

    private function generateSku(int $organizationId): string
    {
        $count = Product::where('organization_id', $organizationId)->count();

        return sprintf('SKU-%06d', $count + 1);
    }
}
