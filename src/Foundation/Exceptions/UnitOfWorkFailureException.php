<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Persistence\PersistenceFailureKind;
use Throwable;

final class UnitOfWorkFailureException extends PersistenceException
{
    public function __construct(
        string $message,
        ?string $operation = null,
        ?Throwable $cause = null,
    ) {
        parent::__construct(
            message: $message,
            kind: PersistenceFailureKind::UnitOfWork,
            operation: $operation,
            cause: $cause,
        );
    }
}
