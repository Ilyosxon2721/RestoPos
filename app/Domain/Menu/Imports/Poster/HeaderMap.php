<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

class HeaderMap
{
    /** @var array<string, list<string>> */
    public const PRODUCTS = [
        'external_id' => ['id', 'product_id', 'идентификатор', 'код', 'poster_id'],
        'sku'         => ['sku', 'артикул', 'артикул товара', 'код товара'],
        'name'        => ['name', 'название', 'наименование', 'название блюда', 'товар'],
        'category'    => ['category', 'категория', 'группа', 'раздел'],
        'workshop'    => ['workshop', 'цех', 'цех приготовления'],
        'type'        => ['type', 'тип', 'тип товара'],
        'unit'        => ['unit', 'ед. изм.', 'ед изм', 'единица', 'единица измерения'],
        'price'       => ['price', 'цена', 'цена продажи', 'розничная цена'],
        'cost_price'  => ['cost_price', 'себестоимость', 'закупочная цена', 'себест.'],
        'weight'      => ['weight', 'вес', 'вес/объем', 'выход'],
        'calories'    => ['calories', 'калории', 'калорийность', 'ккал'],
        'barcode'     => ['barcode', 'штрихкод', 'штрих-код'],
        'is_visible'  => ['visible', 'видим', 'видимость', 'отображается', 'в меню'],
        'is_hidden'   => ['hidden', 'скрыт', 'скрыто', 'скрыто из меню'],
        'description' => ['description', 'описание', 'комментарий'],
    ];

    /** @var array<string, list<string>> */
    public const INGREDIENTS = [
        'external_id'      => ['id', 'ingredient_id', 'идентификатор', 'код'],
        'sku'              => ['sku', 'артикул'],
        'name'             => ['name', 'название', 'наименование'],
        'category'         => ['category', 'категория', 'группа'],
        'unit'             => ['unit', 'ед. изм.', 'ед изм', 'единица', 'единица измерения'],
        'cost_price'       => ['cost_price', 'себестоимость', 'закупочная цена', 'цена'],
        'min_stock'        => ['min_stock', 'минимальный остаток', 'мин. остаток'],
        'shelf_life_days'  => ['shelf_life', 'срок хранения', 'срок хранения, дней'],
        'loss_percent'     => ['loss', 'потери', '% потерь', 'процент потерь'],
        'barcode'          => ['barcode', 'штрихкод', 'штрих-код'],
    ];

    /** @var array<string, list<string>> */
    public const TECH_CARDS = [
        'product_external_id'    => ['product_id', 'id блюда', 'код блюда'],
        'product_sku'            => ['product_sku', 'артикул блюда', 'артикул'],
        'product_name'           => ['product_name', 'блюдо', 'название блюда'],
        'output_quantity'        => ['output', 'выход', 'выход блюда', 'output_quantity'],
        'ingredient_external_id' => ['ingredient_id', 'id ингредиента', 'код ингредиента'],
        'ingredient_sku'         => ['ingredient_sku', 'артикул ингредиента'],
        'ingredient_name'        => ['ingredient_name', 'ингредиент', 'название ингредиента'],
        'quantity'               => ['quantity', 'кол-во', 'количество', 'нетто', 'нетто, г'],
        'gross_quantity'         => ['gross', 'брутто', 'брутто, г'],
        'loss_percent'           => ['loss', 'потери', '% потерь'],
        'unit'                   => ['unit', 'ед. изм.', 'единица'],
    ];

    /**
     * @param array<string, string> $row
     * @param array<string, list<string>> $map
     */
    public static function value(array $row, array $map, string $field): ?string
    {
        $candidates = $map[$field] ?? [];
        foreach ($candidates as $key) {
            $key = mb_strtolower(str_replace('ё', 'е', $key));
            if (array_key_exists($key, $row) && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    }
}
