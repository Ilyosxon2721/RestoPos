<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Menu\Models\Unit;
use App\Domain\Menu\Models\Workshop;
use App\Support\Enums\ProductType;
use Illuminate\Support\Facades\DB;

class ImportProductsAction
{
    private const SOURCE = 'poster';

    public function execute(
        string $csvPath,
        int $organizationId,
        ?int $branchId = null,
        bool $dryRun = false,
    ): ImportResult {
        $result = new ImportResult();

        $callback = function () use ($csvPath, $organizationId, $branchId, $result): void {
            foreach (SpreadsheetReader::open($csvPath) as $line => $row) {
                $this->importRow($row, $line, $organizationId, $branchId, $result);
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
    private function importRow(array $row, int $line, int $organizationId, ?int $branchId, ImportResult $result): void
    {
        $name = HeaderMap::value($row, HeaderMap::PRODUCTS, 'name');
        if ($name === null || $name === '') {
            $result->skipped++;
            $result->error($line, 'Пустое название — строка пропущена');
            return;
        }

        try {
            $externalId = HeaderMap::value($row, HeaderMap::PRODUCTS, 'external_id');
            $sku        = HeaderMap::value($row, HeaderMap::PRODUCTS, 'sku');

            $categoryName = HeaderMap::value($row, HeaderMap::PRODUCTS, 'category');
            $categoryId   = $categoryName ? $this->resolveCategory($organizationId, $categoryName)->id : null;

            $workshopName = HeaderMap::value($row, HeaderMap::PRODUCTS, 'workshop');
            $workshopId   = ($workshopName && $branchId)
                ? $this->resolveWorkshop($branchId, $workshopName)->id
                : null;

            $unitName = HeaderMap::value($row, HeaderMap::PRODUCTS, 'unit');
            $unitId   = $unitName ? $this->resolveUnit($organizationId, $unitName)->id : null;

            $payload = [
                'organization_id' => $organizationId,
                'category_id'     => $categoryId,
                'workshop_id'     => $workshopId,
                'unit_id'         => $unitId,
                'type'            => $this->mapType(HeaderMap::value($row, HeaderMap::PRODUCTS, 'type'))->value,
                'name'            => $name,
                'sku'             => $sku,
                'barcode'         => HeaderMap::value($row, HeaderMap::PRODUCTS, 'barcode'),
                'description'     => HeaderMap::value($row, HeaderMap::PRODUCTS, 'description'),
                'price'           => $this->parseDecimal(HeaderMap::value($row, HeaderMap::PRODUCTS, 'price')) ?? 0,
                'cost_price'      => $this->parseDecimal(HeaderMap::value($row, HeaderMap::PRODUCTS, 'cost_price')) ?? 0,
                'weight'          => $this->parseDecimal(HeaderMap::value($row, HeaderMap::PRODUCTS, 'weight')),
                'calories'        => $this->parseInt(HeaderMap::value($row, HeaderMap::PRODUCTS, 'calories')),
                'is_visible'      => $this->parseVisibility($row),
            ];

            $product = $this->findExisting($organizationId, $externalId, $sku);

            if ($product) {
                $product->fill($payload);
                $product->external_source = self::SOURCE;
                if ($externalId !== null) {
                    $product->external_id = $externalId;
                }
                $product->save();
                $result->updated++;
            } else {
                $payload['external_source'] = self::SOURCE;
                $payload['external_id']     = $externalId;
                Product::create($payload);
                $result->created++;
            }
        } catch (\Throwable $e) {
            $result->skipped++;
            $result->error($line, $e->getMessage());
        }
    }

    private function findExisting(int $organizationId, ?string $externalId, ?string $sku): ?Product
    {
        if ($externalId !== null && $externalId !== '') {
            $product = Product::where('organization_id', $organizationId)
                ->where('external_source', self::SOURCE)
                ->where('external_id', $externalId)
                ->first();
            if ($product) {
                return $product;
            }
        }

        if ($sku !== null && $sku !== '') {
            return Product::where('organization_id', $organizationId)
                ->where('sku', $sku)
                ->first();
        }

        return null;
    }

    private function resolveCategory(int $organizationId, string $name): Category
    {
        return Category::firstOrCreate(
            ['organization_id' => $organizationId, 'name' => $name],
            ['is_visible' => true, 'sort_order' => 0],
        );
    }

    private function resolveWorkshop(int $branchId, string $name): Workshop
    {
        return Workshop::firstOrCreate(
            ['branch_id' => $branchId, 'name' => $name],
            ['is_active' => true, 'sort_order' => 0],
        );
    }

    private function resolveUnit(int $organizationId, string $name): Unit
    {
        $name = trim($name);
        $short = mb_substr($name, 0, 10);

        return Unit::firstOrCreate(
            ['organization_id' => $organizationId, 'short_name' => $short],
            ['name' => $name, 'is_default' => false],
        );
    }

    private function mapType(?string $raw): ProductType
    {
        $raw = mb_strtolower(trim((string) $raw));
        return match (true) {
            str_contains($raw, 'заготов'), str_contains($raw, 'полуфабрикат'), str_contains($raw, 'semi') => ProductType::SEMI_FINISHED,
            str_contains($raw, 'товар'), str_contains($raw, 'product') => ProductType::PRODUCT,
            str_contains($raw, 'напит'), str_contains($raw, 'drink') => ProductType::DRINK,
            str_contains($raw, 'услуг'), str_contains($raw, 'service') => ProductType::SERVICE,
            default => ProductType::DISH,
        };
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

    /**
     * @param array<string, string> $row
     */
    private function parseVisibility(array $row): bool
    {
        $hidden = HeaderMap::value($row, HeaderMap::PRODUCTS, 'is_hidden');
        if ($hidden !== null) {
            return !$this->isTruthy($hidden);
        }
        $visible = HeaderMap::value($row, HeaderMap::PRODUCTS, 'is_visible');
        if ($visible !== null) {
            return $this->isTruthy($visible);
        }
        return true;
    }

    private function isTruthy(string $value): bool
    {
        $v = mb_strtolower(trim($value));
        return in_array($v, ['1', 'true', 'yes', 'да', 'y', 'true', 'on'], true);
    }
}
