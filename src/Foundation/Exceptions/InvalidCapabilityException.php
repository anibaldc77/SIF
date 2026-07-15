<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

/** Raised when a capability identifier violates Foundation naming rules. */
final class InvalidCapabilityException extends FoundationException
{
    public static function empty(): self
    {
        return new self('Capability identifier cannot be empty.');
    }

    public static function invalid(string $capability): self
    {
        return new self(sprintf('Invalid capability identifier "%s".', $capability));
    }
}
