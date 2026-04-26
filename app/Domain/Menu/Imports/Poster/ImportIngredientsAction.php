<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

use App\Domain\Menu\Models\Unit;
use App\Domain\Warehouse\Models\Ingredient;
use Illuminate\Support\Facades\DB;

class ImportIngredientsAction
{
    private const SOURCE = 'poster';

    public function execute(string $csvPath, int $organizationId, bool $dryRun = false): ImportResult
    {
        $reader = new CsvReader($csvPath);
        $result = new ImportResult();

        $callback = function () use ($reader, $organizationId, $result): void {
            foreach ($reader->rows() as $line => $row) {
                $this->importRow($row, $line, $organizationId, $result);
            }
        };

        if ($dryRun) {
            try {
                DB::transaction(function () use ($callback): void {
                    $callback();
                    throw new RollbackForDryRun();
                });
            } catch (RollbackForDryRun) {
                // expected — rolls back changes
            }
        } else {
            DB::transaction($callback);
        }

        return $result;
    }

    /**
     * @param array<string, string> $row
     */
    private function importRow(array $row, int $line, int $organizationId, ImportResult $result): void
    {
        $name = HeaderMap::value($row, HeaderMap::INGREDIENTS, 'name');
        if ($name === null || $name === '') {
            $result->skipped++;
            $result->error($line, 'Пустое название ингредиента');
            return;
        }

        try {
            $externalId = HeaderMap::value($row, HeaderMap::INGREDIENTS, 'external_id');
            $sku        = HeaderMap::value($row, HeaderMap::INGREDIENTS, 'sku');

            $unitName = HeaderMap::value($row, HeaderMap::INGREDIENTS, 'unit') ?? 'шт';
            $unit     = $this->resolveUnit($organizationId, $unitName);

            $payload = [
                'organization_id'  => $organizationId,
                'unit_id'          => $unit->id,
                'name'             => $name,
                'sku'              => $sku,
                'barcode'          => HeaderMap::value($row, HeaderMap::INGREDIENTS, 'barcode'),
                'current_cost'     => $this->parseDecimal(HeaderMap::value($row, HeaderMap::INGREDIENTS, 'cost_price')) ?? 0,
                'min_stock'        => $this->parseDecimal(HeaderMap::value($row, HeaderMap::INGREDIENTS, 'min_stock')) ?? 0,
                'shelf_life_days'  => $this->parseInt(HeaderMap::value($row, HeaderMap::INGREDIENTS, 'shelf_life_days')),
                'loss_percent'     => $this->parseDecimal(HeaderMap::value($row, HeaderMap::INGREDIENTS, 'loss_percent')) ?? 0,
                'is_active'        => true,
            ];

            $ingredient = $this->findExisting($organizationId, $externalId, $sku, $name);

            if ($ingredient) {
                $ingredient->fill($payload);
                $ingredient->external_source = self::SOURCE;
                if ($externalId !== null) {
                    $ingredient->external_id = $externalId;
                }
                $ingredient->save();
                $result->updated++;
            } else {
                $payload['external_source'] = self::SOURCE;
                $payload['external_id']     = $externalId;
                Ingredient::create($payload);
                $result->created++;
            }
        } catch (\Throwable $e) {
            $result->skipped++;
            $result->error($line, $e->getMessage());
        }
    }

    private function findExisting(int $organizationId, ?string $externalId, ?string $sku, string $name): ?Ingredient
    {
        if ($externalId !== null && $externalId !== '') {
            $found = Ingredient::where('organization_id', $organizationId)
                ->where('external_source', self::SOURCE)
                ->where('external_id', $externalId)
                ->first();
            if ($found) {
                return $found;
            }
        }

        if ($sku !== null && $sku !== '') {
            $found = Ingredient::where('organization_id', $organizationId)->where('sku', $sku)->first();
            if ($found) {
                return $found;
            }
        }

        return Ingredient::where('organization_id', $organizationId)->where('name', $name)->first();
    }

    private function resolveUnit(int $organizationId, string $name): Unit
    {
        $name  = trim($name);
        $short = mb_substr($name, 0, 10);

        return Unit::firstOrCreate(
            ['organization_id' => $organizationId, 'short_name' => $short],
            ['name' => $name, 'is_default' => false],
        );
    }

    private function parseDecimal(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $cleaned = str_replace([' ', "\xC2\xA0", ','], ['', '', '.'], $value);
        $cleaned = preg_replace('/[^0-9.\-]/', '', $cleaned) ?? '';
        return $cleaned === '' ? null : (float) $cleaned;
    }

    private function parseInt(?string $value): ?int
    {
        $float = $this->parseDecimal($value);
        return $float === null ? null : (int) $float;
    }
}
