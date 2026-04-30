<?php

declare(strict_types=1);

namespace Tests\Feature\Menu;

use App\Domain\Menu\Actions\SaveDishAction;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Tax;
use App\Domain\Menu\Models\Unit;
use App\Domain\Menu\Models\Workshop;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use App\Domain\Warehouse\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DishEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_action_creates_product_tech_card_and_recomputes_cost(): void
    {
        $org = Organization::factory()->create();
        Branch::factory()->create(['organization_id' => $org->id]);

        $unit = Unit::create([
            'organization_id' => $org->id,
            'name' => 'грамм',
            'short_name' => 'г',
            'is_default' => true,
        ]);

        $tax = Tax::create([
            'organization_id' => $org->id,
            'name' => 'НДС 12%',
            'rate' => 12,
            'type' => 'vat',
        ]);

        $category = Category::create([
            'organization_id' => $org->id,
            'name' => 'Кофе',
        ]);

        $workshop = Workshop::create([
            'branch_id' => Branch::where('organization_id', $org->id)->first()->id,
            'name' => 'Бар',
        ]);

        $coffee = Ingredient::create([
            'organization_id' => $org->id,
            'unit_id' => $unit->id,
            'name' => 'Кофе зерно',
            'current_cost' => 300,
        ]);

        $product = (new SaveDishAction)->execute(
            product: null,
            product_attributes: [
                'organization_id' => $org->id,
                'category_id' => $category->id,
                'workshop_id' => $workshop->id,
                'tax_id' => $tax->id,
                'type' => 'dish',
                'name' => 'Americano',
                'price' => 20000,
                'is_weighable' => false,
                'excluded_from_discounts' => false,
                'is_visible' => true,
            ],
            techCard: [
                'output_quantity' => 200,
                'output_unit_id' => $unit->id,
                'is_active' => true,
            ],
            items: [
                ['ingredient_id' => $coffee->id, 'quantity' => 18, 'loss_percent' => 0],
            ],
            modifierGroups: [],
        );

        $this->assertNotNull($product->id);
        $this->assertNotNull($product->techCard);
        $this->assertCount(1, $product->techCard->items);

        // Cost = 18g * 300 = 5400
        $this->assertEqualsWithDelta(5400, (float) $product->cost_price, 0.01);
    }

    public function test_tax_inheritance_falls_back_to_category(): void
    {
        $org = Organization::factory()->create();
        $tax = Tax::create([
            'organization_id' => $org->id,
            'name' => 'Парент',
            'rate' => 10,
            'type' => 'vat',
        ]);

        $parent = Category::create(['organization_id' => $org->id, 'name' => 'Напитки', 'tax_id' => $tax->id]);
        $child = Category::create(['organization_id' => $org->id, 'parent_id' => $parent->id, 'name' => 'Кофе']);

        $this->assertSame($tax->id, $child->effectiveTax()->id);
    }
}
