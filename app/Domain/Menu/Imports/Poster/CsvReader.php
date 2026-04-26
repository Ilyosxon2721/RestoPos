<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

use Generator;
use RuntimeException;

class CsvReader
{
    public function __construct(
        private readonly string $path,
    ) {
        if (!is_file($this->path) || !is_readable($this->path)) {
            throw new RuntimeException("CSV file not found or not readable: {$this->path}");
        }
    }

    /**
     * Iterate over rows as associative arrays keyed by normalized header.
     *
     * @return Generator<int, array<string, string>>
     */
    public function rows(): Generator
    {
        $handle = fopen($this->path, 'rb');
        if ($handle === false) {
            throw new RuntimeException("Cannot open CSV: {$this->path}");
        }

        try {
            $delimiter = $this->detectDelimiter();
            $headers = fgetcsv($handle, 0, $delimiter, '"', '\\');
            if ($headers === false) {
                return;
            }

            $headers = $this->normalizeHeaders($headers);
            $line = 1;

            while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
                $line++;
                if ($row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) {
                    continue;
                }

                $row = array_pad($row, count($headers), '');
                $row = array_slice($row, 0, count($headers));
                $assoc = array_combine($headers, array_map('trim', array_map('strval', $row)));

                yield $line => $assoc;
            }
        } finally {
            fclose($handle);
        }
    }

    private function detectDelimiter(): string
    {
        $handle = fopen($this->path, 'rb');
        if ($handle === false) {
            return ',';
        }

        $sample = (string) fread($handle, 4096);
        fclose($handle);

        $sample = $this->stripBom($sample);
        $firstLine = strtok($sample, "\n\r") ?: '';

        $candidates = [';' => 0, ',' => 0, "\t" => 0, '|' => 0];
        foreach ($candidates as $delim => $_) {
            $candidates[$delim] = substr_count($firstLine, $delim);
        }

        arsort($candidates);
        $top = (string) array_key_first($candidates);

        return $candidates[$top] > 0 ? $top : ',';
    }

    /**
     * @param array<int, string|null> $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $i => $header) {
            $h = $this->stripBom((string) $header);
            $h = trim($h);
            $h = mb_strtolower($h);
            $h = str_replace(['ё'], ['е'], $h);
            $h = preg_replace('/\s+/u', ' ', $h) ?? $h;
            $result[$i] = $h;
        }

        return $result;
    }

    private function stripBom(string $s): string
    {
        if (str_starts_with($s, "\xEF\xBB\xBF")) {
            return substr($s, 3);
        }
        return $s;
    }
}
