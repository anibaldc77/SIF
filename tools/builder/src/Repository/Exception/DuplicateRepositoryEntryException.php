<?php

declare(strict_types=1);

namespace Sif\Builder\Repository\Exception;

use RuntimeException;

final class DuplicateRepositoryEntryException extends RuntimeException
{
    public function __construct(string $identifier, string $existingPath, string $incomingPath)
    {
        parent::__construct(sprintf(
            'Repository entry identifier "%s" is declared by both "%s" and "%s".',
            $identifier,
            $existingPath,
            $incomingPath,
        ));
    }
}
