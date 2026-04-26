<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

use App\Domain\Menu\Models\Product;
use App\Domain\Warehouse\Models\Ingredient;
use App\Domain\Warehouse\Models\TechCard;
use App\Domain\Warehouse\Models\TechCardItem;
use Illuminate\Support\Facades\DB;

class ImportTechCardsAction
{
    private const SOURCE = 'poster';

    public function execute(string $csvPath, int $organizationId, bool $dryRun = false): ImportResult
    {
        $result = new ImportResult();

        $rowsByProduct = $this->groupByProduct(SpreadsheetReader::open($csvPath));

        $callback = function () use ($rowsByProduct, $organizationId, $result): void {
            foreach ($rowsByProduct as $productKey => $bundle) {
                $this->importTechCard($productKey, $bundle['rows'], $bundle['firstLine'], $organizationId, $result);
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
     * @param iterable<int, array<string, string>> $rows
     * @return array<string, array{firstLine:int, rows: list<array<string, string>>}>
     */
    private function groupByProduct(iterable $rows): array
    {
        $groups = [];
        foreach ($rows as $line => $row) {
            $key = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'product_external_id')
                ?? HeaderMap::value($row, HeaderMap::TECH_CARDS, 'product_sku')
                ?? HeaderMap::value($row, HeaderMap::TECH_CARDS, 'product_name');

            if ($key === null || $key === '') {
                continue;
            }

            if (!isset($groups[$key])) {
                $groups[$key] = ['firstLine' => $line, 'rows' => []];
            }
            $groups[$key]['rows'][] = $row;
        }
        return $groups;
    }

    /**
     * @param list<array<string, string>> $rows
     */
    private function importTechCard(string $productKey, array $rows, int $firstLine, int $organizationId, ImportResult $result): void
    {
        try {
            $first   = $rows[0];
            $product = $this->findProduct($organizationId, $first);

            if (!$product) {
                $result->skipped++;
                $result->error($firstLine, "Блюдо не найдено: {$productKey}. Сначала импортируйте товары.");
                return;
            }

            $output = $this->parseDecimal(HeaderMap::value($first, HeaderMap::TECH_CARDS, 'output_quantity')) ?? 1.0;

            $techCard = TechCard::firstOrNew(['product_id' => $product->id]);
            $isNew    = !$techCard->exists;

            $techCard->output_quantity = $output;
            $techCard->is_active       = true;
            if ($isNew) {
                $techCard->version = 1;
            }
            $techCard->save();

            $techCard->items()->delete();

            $sortOrder = 0;
            foreach ($rows as $row) {
                $ingredient = $this->findIngredient($organizationId, $row);
                $semiFinished = null;
                if (!$ingredient) {
                    $semiFinished = $this->findSemiFinished($organizationId, $row);
                }

                if (!$ingredient && !$semiFinished) {
                    $name = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'ingredient_name') ?? '?';
                    $result->error($firstLine, "Ингредиент не найден: {$name} (блюдо: {$productKey})");
                    continue;
                }

                $quantity = $this->parseDecimal(HeaderMap::value($row, HeaderMap::TECH_CARDS, 'quantity'));
                $gross    = $this->parseDecimal(HeaderMap::value($row, HeaderMap::TECH_CARDS, 'gross_quantity'));
                $loss     = $this->parseDecimal(HeaderMap::value($row, HeaderMap::TECH_CARDS, 'loss_percent')) ?? 0.0;

                if ($quantity === null && $gross !== null) {
                    $quantity = $loss > 0 ? $gross / (1 + $loss / 100) : $gross;
                } elseif ($quantity === null && $gross === null) {
                    $result->error($firstLine, "Не указано количество для ингредиента в {$productKey}");
                    continue;
                }

                TechCardItem::create([
                    'tech_card_id'     => $techCard->id,
                    'ingredient_id'    => $ingredient?->id,
                    'semi_finished_id' => $semiFinished?->id,
                    'quantity'         => $quantity,
                    'loss_percent'     => $loss,
                    'sort_order'       => $sortOrder++,
                ]);
            }

            $isNew ? $result->created++ : $result->updated++;
        } catch (\Throwable $e) {
            $result->skipped++;
            $result->error($firstLine, $e->getMessage());
        }
    }

    /**
     * @param array<string, string> $row
     */
    private function findProduct(int $organizationId, array $row): ?Product
    {
        $externalId = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'product_external_id');
        if ($externalId) {
            $found = Product::where('organization_id', $organizationId)
                ->where('external_source', self::SOURCE)
                ->where('external_id', $externalId)
                ->first();
            if ($found) {
                return $found;
            }
        }

        $sku = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'product_sku');
        if ($sku) {
            $found = Product::where('organization_id', $organizationId)->where('sku', $sku)->first();
            if ($found) {
                return $found;
            }
        }

        $name = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'product_name');
        if ($name) {
            return Product::where('organization_id', $organizationId)->where('name', $name)->first();
        }

        return null;
    }

    /**
     * @param array<string, string> $row
     */
    private function findIngredient(int $organizationId, array $row): ?Ingredient
    {
        $externalId = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'ingredient_external_id');
        if ($externalId) {
            $found = Ingredient::where('organization_id', $organizationId)
                ->where('external_source', self::SOURCE)
                ->where('external_id', $externalId)
                ->first();
            if ($found) {
                return $found;
            }
        }

        $sku = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'ingredient_sku');
        if ($sku) {
            $found = Ingredient::where('organization_id', $organizationId)->where('sku', $sku)->first();
            if ($found) {
                return $found;
            }
        }

        $name = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'ingredient_name');
        if ($name) {
            return Ingredient::where('organization_id', $organizationId)->where('name', $name)->first();
        }

        return null;
    }

    /**
     * @param array<string, string> $row
     */
    private function findSemiFinished(int $organizationId, array $row): ?Product
    {
        $name = HeaderMap::value($row, HeaderMap::TECH_CARDS, 'ingredient_name');
        if (!$name) {
            return null;
        }

        return Product::where('organization_id', $organizationId)
            ->where('name', $name)
            ->where('type', 'semi_finished')
            ->first();
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
}
