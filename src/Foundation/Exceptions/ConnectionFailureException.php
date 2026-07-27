<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Persistence\PersistenceFailureKind;
use Throwable;

final class ConnectionFailureException extends PersistenceException
{
    public function __construct(
        string $message,
        ?string $operation = null,
        ?Throwable $cause = null,
    ) {
        parent::__construct(
            message: $message,
            kind: PersistenceFailureKind::Connection,
            operation: $operation,
            cause: $cause,
        );
    }
}
