<?php

namespace App\Domain\Menu\Actions;

use App\Domain\Menu\Models\Category;
use Illuminate\Support\Str;

class CreateCategoryAction
{
    public function execute(array $data): Category
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Set sort order if not provided
        if (!isset($data['sort_order'])) {
            $maxOrder = Category::where('organization_id', $data['organization_id'])
                ->where('parent_id', $data['parent_id'] ?? null)
                ->max('sort_order');
            $data['sort_order'] = ($maxOrder ?? 0) + 1;
        }

        return Category::create($data);
    }
}
