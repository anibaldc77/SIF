<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Persistence\PersistenceFailureKind;
use Throwable;

final class MappingFailureException extends PersistenceException
{
    public function __construct(
        string $message,
        ?string $operation = null,
        ?Throwable $cause = null,
    ) {
        parent::__construct(
            message: $message,
            kind: PersistenceFailureKind::Mapping,
            operation: $operation,
            cause: $cause,
        );
    }
}
