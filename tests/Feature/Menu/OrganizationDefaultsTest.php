<?php

declare(strict_types=1);

namespace Tests\Feature\Menu;

use App\Domain\Menu\Actions\SeedOrganizationDefaultsAction;
use App\Domain\Menu\Models\PreparationMethod;
use App\Domain\Menu\Models\Tax;
use App\Domain\Menu\Models\Unit;
use App\Domain\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_taxes_preparation_methods_and_units(): void
    {
        $org = Organization::factory()->create();

        (new SeedOrganizationDefaultsAction)->execute($org);

        $this->assertSame(3, Tax::where('organization_id', $org->id)->count());
        $this->assertSame(7, PreparationMethod::where('organization_id', $org->id)->count());
        $this->assertGreaterThan(0, Unit::where('organization_id', $org->id)->count());

        $this->assertTrue(Tax::where('organization_id', $org->id)
            ->where('name', 'Без НДС')->where('is_default', true)->exists());
    }

    public function test_seeding_is_idempotent(): void
    {
        $org = Organization::factory()->create();
        $action = new SeedOrganizationDefaultsAction;

        $action->execute($org);
        $action->execute($org);

        $this->assertSame(3, Tax::where('organization_id', $org->id)->count());
        $this->assertSame(7, PreparationMethod::where('organization_id', $org->id)->count());
    }
}
