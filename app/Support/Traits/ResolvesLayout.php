<?php

declare(strict_types=1);

namespace App\Support\Traits;

trait ResolvesLayout
{
    private function resolveLayout(): string
    {
        $prefix = request()->segment(1);

        return match ($prefix) {
            'cabinet' => 'components.layouts.cabinet',
            'manager' => 'components.layouts.manager',
            'admin' => 'components.layouts.admin',
            'cashier' => 'components.layouts.cashier',
            'waiter' => 'components.layouts.waiter',
            'kitchen' => 'components.layouts.kitchen',
            'warehouse-panel' => 'components.layouts.warehouse-panel',
            default => 'components.layouts.app',
        };
    }
}
