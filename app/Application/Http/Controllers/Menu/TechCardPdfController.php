<?php

declare(strict_types=1);

namespace App\Application\Http\Controllers\Menu;

use App\Application\Http\Controllers\Controller;
use App\Domain\Menu\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

final class TechCardPdfController extends Controller
{
    public function __invoke(int $product): Response
    {
        $product = Product::with([
            'category',
            'workshop',
            'tax',
            'techCard.outputUnit',
            'techCard.items.ingredient.unit',
            'techCard.items.semiFinished',
            'techCard.items.preparationMethod',
            'techCard.items.unit',
        ])->findOrFail($product);

        abort_unless(
            $product->organization_id === auth()->user()->organization_id,
            403,
            'Forbidden'
        );

        $pdf = Pdf::loadView('pdf.tech-card', ['product' => $product]);

        return $pdf->stream("tech-card-{$product->id}.pdf");
    }
}
