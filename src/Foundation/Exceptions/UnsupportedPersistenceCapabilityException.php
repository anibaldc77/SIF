<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\PersistenceFailureKind;

final class UnsupportedPersistenceCapabilityException extends PersistenceException
{
    public function __construct(
        private readonly PersistenceCapability $capability,
        private readonly string $providerType,
    ) {
        parent::__construct(
            message: sprintf(
                'Persistence provider "%s" does not support capability "%s".',
                $this->providerType,
                $this->capability->value,
            ),
            kind: PersistenceFailureKind::UnsupportedCapability,
            operation: $this->capability->value,
        );
    }

    public function capability(): PersistenceCapability
    {
        return $this->capability;
    }

    public function providerType(): string
    {
        return $this->providerType;
    }
}
