<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

use DateInterval;
use DateTimeInterface;
use Generator;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;

class XlsxReader
{
    public function __construct(
        private readonly string $path,
    ) {
        if (!is_file($this->path) || !is_readable($this->path)) {
            throw new RuntimeException("XLSX file not found or not readable: {$this->path}");
        }
    }

    /**
     * Iterate over rows of the first sheet as associative arrays keyed by normalized header.
     *
     * @return Generator<int, array<string, string>>
     */
    public function rows(): Generator
    {
        $reader = new Reader(new Options(SHOULD_PRESERVE_EMPTY_ROWS: true));
        $reader->open($this->path);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $headers = null;
                $line = 0;

                foreach ($sheet->getRowIterator() as $row) {
                    $line++;
                    $cells = array_map(
                        fn ($value) => $this->cellToString($value),
                        $row->toArray(),
                    );

                    if ($headers === null) {
                        $headers = $this->normalizeHeaders($cells);

                        continue;
                    }

                    if ($cells === [] || count(array_filter($cells, static fn (string $v) => $v !== '')) === 0) {
                        continue;
                    }

                    $cells = array_pad($cells, count($headers), '');
                    $cells = array_slice($cells, 0, count($headers));
                    $assoc = array_combine($headers, $cells);

                    yield $line => $assoc;
                }

                return;
            }
        } finally {
            $reader->close();
        }
    }

    private function cellToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if ($value instanceof DateInterval) {
            return $value->format('%h:%I:%S');
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value)) {
            return '';
        }

        return trim((string) $value);
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $i => $header) {
            $h = trim($header);
            $h = mb_strtolower($h);
            $h = str_replace(['ё'], ['е'], $h);
            $h = preg_replace('/\s+/u', ' ', $h) ?? $h;
            $result[$i] = $h;
        }

        return $result;
    }
}
