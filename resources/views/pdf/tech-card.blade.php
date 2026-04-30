<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тех. карта — {{ $product->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .muted { color: #666; }
        .meta { width: 100%; margin: 12px 0 18px; }
        .meta td { padding: 3px 6px; vertical-align: top; }
        .meta td:first-child { color: #666; width: 25%; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th, table.items td { border: 1px solid #d1d5db; padding: 5px 8px; }
        table.items th { background: #f3f4f6; text-align: left; font-weight: 600; }
        table.items td.num { text-align: right; font-family: monospace; }
        tfoot td { font-weight: 600; background: #f9fafb; }
        .badge { background: #eef2ff; color: #4338ca; padding: 1px 6px; border-radius: 4px; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Тех. карта: {{ $product->name }}</h1>
    <div class="muted">Версия {{ $product->techCard?->version ?? 1 }} от {{ optional($product->techCard?->updated_at)->format('d.m.Y') ?? '—' }}</div>

    <table class="meta">
        <tr><td>Категория</td><td>{{ $product->category?->name ?? '—' }}</td></tr>
        <tr><td>Цех приготовления</td><td>{{ $product->workshop?->name ?? '—' }}</td></tr>
        <tr><td>Налог</td><td>{{ $product->tax?->name ?? '—' }} {{ $product->tax ? '('.$product->tax->rate.'%)' : '' }}</td></tr>
        <tr><td>Цена</td><td>{{ number_format((float) $product->price, 2, ',', ' ') }} СУМ</td></tr>
        <tr><td>Себестоимость</td><td>{{ number_format((float) $product->cost_price, 2, ',', ' ') }} СУМ</td></tr>
        <tr><td>Выход</td><td>{{ $product->techCard ? number_format((float) $product->techCard->output_quantity, 3, ',', ' ').' '.($product->techCard->outputUnit?->short_name ?? '') : '—' }}</td></tr>
        @if ($product->is_weighable) <tr><td></td><td><span class="badge">Весовая</span></td></tr> @endif
        @if ($product->excluded_from_discounts) <tr><td></td><td><span class="badge">Не участвует в скидках</span></td></tr> @endif
    </table>

    <h3 style="margin-bottom: 4px;">Состав</h3>
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Продукт</th>
                <th>Метод</th>
                <th class="num">Нетто</th>
                <th class="num">Потери %</th>
                <th class="num">Брутто</th>
                <th class="num">Себестоимость</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach (($product->techCard?->items ?? collect()) as $i => $item)
                @php
                    $name = $item->ingredient?->name ?? $item->semiFinished?->name ?? '—';
                    $unit = $item->ingredient?->unit?->short_name ?? $item->unit?->short_name ?? '';
                    $cost = $item->cost_amount;
                    $total += $cost;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $name }}</td>
                    <td>{{ $item->preparationMethod?->name ?? '—' }}</td>
                    <td class="num">{{ number_format((float) $item->quantity, 4, ',', ' ') }} {{ $unit }}</td>
                    <td class="num">{{ number_format((float) $item->loss_percent, 2, ',', ' ') }}</td>
                    <td class="num">{{ number_format($item->gross_quantity, 4, ',', ' ') }} {{ $unit }}</td>
                    <td class="num">{{ number_format($cost, 2, ',', ' ') }} СУМ</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right;">Итого:</td>
                <td class="num">{{ number_format($total, 2, ',', ' ') }} СУМ</td>
            </tr>
        </tfoot>
    </table>

    @if ($product->techCard?->cooking_instructions)
        <h3 style="margin-top: 14px; margin-bottom: 4px;">Технология приготовления</h3>
        <div style="white-space: pre-wrap;">{{ $product->techCard->cooking_instructions }}</div>
    @endif
</body>
</html>
