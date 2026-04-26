<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

use Generator;
use RuntimeException;

final class SpreadsheetReader
{
    /**
     * Open a spreadsheet file (.csv or .xlsx) and iterate rows as associative arrays
     * keyed by normalized header. Format is detected by file extension.
     *
     * @return Generator<int, array<string, string>>
     */
    public static function open(string $path): Generator
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Spreadsheet file not found or not readable: {$path}");
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'xlsx' => (new XlsxReader($path))->rows(),
            'csv', 'txt', '' => (new CsvReader($path))->rows(),
            default => throw new RuntimeException("Unsupported spreadsheet format: .{$extension}"),
        };
    }
}
