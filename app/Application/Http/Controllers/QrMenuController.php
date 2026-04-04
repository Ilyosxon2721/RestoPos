<?php

declare(strict_types=1);

namespace App\Application\Http\Controllers;

use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Menu\Models\QrMenu;
use Illuminate\View\View;

class QrMenuController extends Controller
{
    /**
     * Отображение публичного QR-меню.
     */
    public function show(string $slug): View
    {
        $qrMenu = QrMenu::query()
            ->withoutGlobalScope('organization')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $categories = Category::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $qrMenu->organization_id)
            ->where('is_visible', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_visible', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $qrMenu->organization_id)
            ->where('is_visible', true)
            ->where('is_available', true)
            ->where('in_stop_list', false)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category_id');

        return view('qr-menu.show', compact('qrMenu', 'categories', 'products'));
    }
}
