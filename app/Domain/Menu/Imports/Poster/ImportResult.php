<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

class ImportResult
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    /** @var list<array{line:int, message:string}> */
    public array $errors = [];

    public function error(int $line, string $message): void
    {
        $this->errors[] = ['line' => $line, 'message' => $message];
    }

    public function total(): int
    {
        return $this->created + $this->updated + $this->skipped;
    }
}
