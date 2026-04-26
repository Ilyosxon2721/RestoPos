<?php

declare(strict_types=1);

namespace App\Domain\Menu\Imports\Poster;

class RollbackForDryRun extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Dry run — rolled back');
    }
}
