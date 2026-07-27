<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use RuntimeException;
use Sif\Foundation\Contracts\PersistenceFailureInterface;
use Sif\Foundation\Persistence\PersistenceFailureKind;
use Throwable;

class PersistenceException extends RuntimeException implements
    PersistenceFailureInterface
{
    public function __construct(
        string $message,
        private readonly PersistenceFailureKind $kind = PersistenceFailureKind::Unknown,
        private readonly ?string $operation = null,
        private readonly ?Throwable $cause = null,
    ) {
        parent::__construct(
            message: $message,
            previous: $cause,
        );
    }

    public function kind(): PersistenceFailureKind
    {
        return $this->kind;
    }

    public function operation(): ?string
    {
        return $this->operation;
    }

    public function cause(): ?Throwable
    {
        return $this->cause;
    }
}
