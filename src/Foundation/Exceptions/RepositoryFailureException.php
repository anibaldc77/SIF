<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Persistence\PersistenceFailureKind;
use Throwable;

final class RepositoryFailureException extends PersistenceException
{
    public function __construct(
        string $message,
        ?string $operation = null,
        ?Throwable $cause = null,
    ) {
        parent::__construct(
            message: $message,
            kind: PersistenceFailureKind::Repository,
            operation: $operation,
            cause: $cause,
        );
    }
}
