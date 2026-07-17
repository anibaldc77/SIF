<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata\Exception;

use RuntimeException;

final class DuplicateMetadataIdentifierException extends RuntimeException
{
    public function __construct(string $identifier, string $existingPath, string $incomingPath)
    {
        parent::__construct(sprintf(
            'Metadata identifier "%s" is declared by both "%s" and "%s".',
            $identifier,
            $existingPath,
            $incomingPath,
        ));
    }
}
