<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domain\Auth\Models\User;
use App\Domain\Menu\Actions\SeedOrganizationDefaultsAction;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Tax;
use App\Domain\Order\Actions\AddOrderItemAction;
use App\Domain\Order\Models\Order;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use App\Domain\Payment\Models\CashShift;
use App\Support\Enums\OrderStatus;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTaxCalculationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;

    private Branch $branch;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create();
        $this->branch = Branch::factory()->create(['organization_id' => $this->org->id]);
        (new SeedOrganizationDefaultsAction)->execute($this->org);

        $user = User::factory()->create(['organization_id' => $this->org->id]);
        CashShift::create([
            'branch_id' => $this->branch->id,
            'opened_by' => $user->id,
            'opened_at' => now(),
            'opening_cash' => 0,
            'status' => 'open',
        ]);

        $this->order = Order::create([
            'branch_id' => $this->branch->id,
            'order_number' => 'T-001',
            'status' => OrderStatus::NEW,
            'opened_at' => now(),
        ]);
    }

    public function test_vat_is_extracted_from_price_and_does_not_inflate_total(): void
    {
        $vat = Tax::where('organization_id', $this->org->id)->where('rate', 12)->first();
        $product = ProductFactory::new()->create([
            'organization_id' => $this->org->id,
            'tax_id' => $vat->id,
            'price' => 11200, // 10000 net + 12% VAT
        ]);

        (new AddOrderItemAction)->execute($this->order, [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 11200,
        ]);

        $this->order->refresh();

        $this->assertEqualsWithDelta(11200, (float) $this->order->subtotal, 0.01);
        $this->assertEqualsWithDelta(1200, (float) $this->order->tax_amount, 0.01); // VAT extracted
        $this->assertEqualsWithDelta(11200, (float) $this->order->total_amount, 0.01); // VAT included → total unchanged
    }

    public function test_turnover_tax_is_added_on_top_of_total(): void
    {
        $turnover = Tax::create([
            'organization_id' => $this->org->id,
            'name' => 'Налог 4%',
            'rate' => 4,
            'type' => 'turnover',
            'is_default' => false,
            'is_active' => true,
        ]);

        $product = ProductFactory::new()->create([
            'organization_id' => $this->org->id,
            'tax_id' => $turnover->id,
            'price' => 10000,
        ]);

        (new AddOrderItemAction)->execute($this->order, [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 10000,
        ]);

        $this->order->refresh();

        $this->assertEqualsWithDelta(10000, (float) $this->order->subtotal, 0.01);
        $this->assertEqualsWithDelta(400, (float) $this->order->tax_amount, 0.01);
        $this->assertEqualsWithDelta(10400, (float) $this->order->total_amount, 0.01); // 10000 + 4%
    }

    public function test_no_tax_when_product_has_none_type(): void
    {
        $none = Tax::where('organization_id', $this->org->id)->where('type', 'none')->first();
        $product = ProductFactory::new()->create([
            'organization_id' => $this->org->id,
            'tax_id' => $none->id,
            'price' => 5000,
        ]);

        (new AddOrderItemAction)->execute($this->order, [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 5000,
        ]);

        $this->order->refresh();

        $this->assertEqualsWithDelta(0, (float) $this->order->tax_amount, 0.01);
        $this->assertEqualsWithDelta(10000, (float) $this->order->total_amount, 0.01);
    }

    public function test_tax_inherited_from_category_when_product_has_none(): void
    {
        $vat = Tax::where('organization_id', $this->org->id)->where('rate', 12)->first();
        $cat = Category::create([
            'organization_id' => $this->org->id,
            'name' => 'Кофе',
            'tax_id' => $vat->id,
        ]);

        $product = ProductFactory::new()->create([
            'organization_id' => $this->org->id,
            'category_id' => $cat->id,
            'tax_id' => null,
            'price' => 11200,
        ]);

        (new AddOrderItemAction)->execute($this->order, [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 11200,
        ]);

        $this->order->refresh();

        $this->assertEqualsWithDelta(1200, (float) $this->order->tax_amount, 0.01);
    }
}
