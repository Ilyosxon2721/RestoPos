<?php

declare(strict_types=1);

namespace App\Domain\Menu\Actions;

use App\Domain\Menu\Models\PreparationMethod;
use App\Domain\Menu\Models\Tax;
use App\Domain\Menu\Models\Unit;
use App\Domain\Organization\Models\Organization;

/**
 * Seeds taxes, preparation methods, and basic units for a fresh organization.
 * Idempotent — safe to call repeatedly.
 */
final class SeedOrganizationDefaultsAction
{
    public function execute(Organization $organization): void
    {
        $this->seedTaxes($organization);
        $this->seedPreparationMethods($organization);
        $this->seedUnits($organization);
    }

    private function seedTaxes(Organization $organization): void
    {
        $defaults = [
            ['name' => 'Без НДС', 'rate' => 0, 'type' => 'none', 'is_default' => true],
            ['name' => 'НДС 12%', 'rate' => 12, 'type' => 'vat', 'is_default' => false],
            ['name' => 'Налог с оборота', 'rate' => 4, 'type' => 'turnover', 'is_default' => false],
        ];

        foreach ($defaults as $row) {
            Tax::firstOrCreate(
                ['organization_id' => $organization->id, 'name' => $row['name']],
                $row + ['is_active' => true],
            );
        }
    }

    private function seedPreparationMethods(Organization $organization): void
    {
        $defaults = [
            ['name' => 'Жарка', 'default_loss_percent' => 25],
            ['name' => 'Варка', 'default_loss_percent' => 5],
            ['name' => 'Тушение', 'default_loss_percent' => 15],
            ['name' => 'Запекание', 'default_loss_percent' => 18],
            ['name' => 'Гриль', 'default_loss_percent' => 30],
            ['name' => 'На пару', 'default_loss_percent' => 3],
            ['name' => 'Без обработки', 'default_loss_percent' => 0],
        ];

        foreach ($defaults as $row) {
            PreparationMethod::firstOrCreate(
                ['organization_id' => $organization->id, 'name' => $row['name']],
                $row + ['is_active' => true],
            );
        }
    }

    private function seedUnits(Organization $organization): void
    {
        if (Unit::where('organization_id', $organization->id)->exists()) {
            return;
        }

        $defaults = [
            ['name' => 'Грамм', 'short_name' => 'г', 'is_default' => true],
            ['name' => 'Килограмм', 'short_name' => 'кг', 'is_default' => false],
            ['name' => 'Миллилитр', 'short_name' => 'мл', 'is_default' => false],
            ['name' => 'Литр', 'short_name' => 'л', 'is_default' => false],
            ['name' => 'Штука', 'short_name' => 'шт', 'is_default' => false],
            ['name' => 'Порция', 'short_name' => 'порц', 'is_default' => false],
        ];

        foreach ($defaults as $row) {
            Unit::create($row + ['organization_id' => $organization->id]);
        }
    }
}
