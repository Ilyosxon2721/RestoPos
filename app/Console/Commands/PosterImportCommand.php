<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Menu\Imports\Poster\ImportIngredientsAction;
use App\Domain\Menu\Imports\Poster\ImportProductsAction;
use App\Domain\Menu\Imports\Poster\ImportResult;
use App\Domain\Menu\Imports\Poster\ImportTechCardsAction;
use Illuminate\Console\Command;

class PosterImportCommand extends Command
{
    protected $signature = 'poster:import
        {file : Путь к CSV-файлу}
        {--type=products : Тип импорта: products | ingredients | tech-cards}
        {--organization= : ID организации (обязателен)}
        {--branch= : ID филиала (нужен для products, чтобы создавать цеха)}
        {--dry-run : Прогон без записи в БД}';

    protected $description = 'Импорт данных из CSV (формат Poster POS): товары, ингредиенты, технологические карты';

    public function handle(
        ImportProductsAction $products,
        ImportIngredientsAction $ingredients,
        ImportTechCardsAction $techCards,
    ): int {
        $file = (string) $this->argument('file');
        if (!is_file($file)) {
            $this->error("Файл не найден: {$file}");
            return self::FAILURE;
        }

        $organizationId = $this->option('organization');
        if (!$organizationId || !ctype_digit((string) $organizationId)) {
            $this->error('--organization=<id> обязателен');
            return self::FAILURE;
        }
        $organizationId = (int) $organizationId;

        $branchOption = $this->option('branch');
        $branchId = ($branchOption && ctype_digit((string) $branchOption)) ? (int) $branchOption : null;

        $dryRun = (bool) $this->option('dry-run');
        $type   = (string) $this->option('type');

        $this->info(sprintf(
            'Импорт «%s» из %s (organization=%d%s%s)',
            $type,
            $file,
            $organizationId,
            $branchId ? ", branch={$branchId}" : '',
            $dryRun ? ', DRY-RUN' : '',
        ));

        $result = match ($type) {
            'products'   => $products->execute($file, $organizationId, $branchId, $dryRun),
            'ingredients' => $ingredients->execute($file, $organizationId, $dryRun),
            'tech-cards', 'techcards' => $techCards->execute($file, $organizationId, $dryRun),
            default      => null,
        };

        if ($result === null) {
            $this->error("Неизвестный --type={$type}. Допустимо: products, ingredients, tech-cards");
            return self::FAILURE;
        }

        $this->printSummary($result, $dryRun);

        return $result->errors === [] || $result->total() > 0 ? self::SUCCESS : self::FAILURE;
    }

    private function printSummary(ImportResult $result, bool $dryRun): void
    {
        $this->newLine();
        $this->table(
            ['Создано', 'Обновлено', 'Пропущено', 'Всего', 'Ошибок'],
            [[
                $result->created,
                $result->updated,
                $result->skipped,
                $result->total(),
                count($result->errors),
            ]],
        );

        if ($result->errors !== []) {
            $this->warn('Ошибки:');
            foreach (array_slice($result->errors, 0, 50) as $err) {
                $this->line(sprintf('  строка %d: %s', $err['line'], $err['message']));
            }
            if (count($result->errors) > 50) {
                $this->line(sprintf('  ... и ещё %d', count($result->errors) - 50));
            }
        }

        if ($dryRun) {
            $this->comment('DRY-RUN: изменения не сохранены.');
        }
    }
}
