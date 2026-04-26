<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Imports\Poster\ImportIngredientsAction;
use App\Domain\Menu\Imports\Poster\ImportProductsAction;
use App\Domain\Menu\Imports\Poster\ImportResult;
use App\Domain\Menu\Imports\Poster\ImportTechCardsAction;
use App\Domain\Organization\Models\Branch;
use App\Support\Traits\ResolvesLayout;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class Import extends Component
{
    use WithFileUploads, ResolvesLayout;

    #[Url(as: 'tab', keep: false)]
    public string $tab = 'ingredients';

    #[Validate(['file' => 'required|file|mimes:csv,txt|max:20480'])]
    public ?TemporaryUploadedFile $file = null;

    public bool $dryRun = true;

    public ?int $branchId = null;

    public Collection $branches;

    public ?array $result = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        if (!in_array($this->tab, ['ingredients', 'products', 'tech-cards'], true)) {
            $this->tab = 'ingredients';
        }

        $this->branches = Branch::query()
            ->where('organization_id', $this->organizationId())
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($this->branches->isNotEmpty()) {
            $this->branchId = (int) $this->branches->first()->id;
        }
    }

    public function selectTab(string $tab): void
    {
        if (!in_array($tab, ['ingredients', 'products', 'tech-cards'], true)) {
            return;
        }
        $this->tab = $tab;
        $this->reset(['file', 'result', 'errorMessage']);
        $this->resetValidation();
    }

    public function process(
        ImportIngredientsAction $ingredients,
        ImportProductsAction $products,
        ImportTechCardsAction $techCards,
    ): void {
        $this->validate();
        $this->result = null;
        $this->errorMessage = null;

        $path = $this->file->getRealPath();
        $organizationId = $this->organizationId();

        try {
            $importResult = match ($this->tab) {
                'ingredients' => $ingredients->execute($path, $organizationId, $this->dryRun),
                'products'    => $products->execute($path, $organizationId, $this->branchId, $this->dryRun),
                'tech-cards'  => $techCards->execute($path, $organizationId, $this->dryRun),
            };

            $this->result = $this->serializeResult($importResult);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function downloadSample(string $type): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $allowed = [
            'ingredients' => 'ingredients.sample.csv',
            'products'    => 'products.sample.csv',
            'tech-cards'  => 'tech_cards.sample.csv',
        ];

        abort_unless(isset($allowed[$type]), 404);

        $path = base_path('app/Domain/Menu/Imports/Poster/samples/' . $allowed[$type]);

        return response()->download($path);
    }

    public function render()
    {
        return view('livewire.menu.import')->layout($this->resolveLayout());
    }

    private function organizationId(): int
    {
        return (int) auth()->user()->organization_id;
    }

    private function serializeResult(ImportResult $r): array
    {
        return [
            'created'  => $r->created,
            'updated'  => $r->updated,
            'skipped'  => $r->skipped,
            'total'    => $r->total(),
            'errors'   => array_slice($r->errors, 0, 100),
            'errorCnt' => count($r->errors),
        ];
    }
}
