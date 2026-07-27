<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Exceptions\InvalidContextIdException;

/**
 * Opaque, non-empty execution-context identifier.
 */
final readonly class ContextId
{
    public function __construct(private string $value)
    {
        if (trim($value) === '') {
            throw new InvalidContextIdException('Context identifier must not be empty.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
