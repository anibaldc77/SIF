<?php

declare(strict_types=1);

namespace Sif\Foundation\Capability\Exceptions;

final class InvalidCapabilityIdentifierException extends CapabilityException
{
    public static function empty(): self
    {
        return new self('A capability identifier cannot be empty.');
    }
}
