<?php

declare(strict_types=1);

namespace Sif\Foundation\Capability\Exceptions;

final class DuplicateCapabilityException extends CapabilityException
{
    public static function forIdentifier(string $identifier): self
    {
        return new self(sprintf(
            'Capability "%s" is already registered.',
            $identifier,
        ));
    }
}
