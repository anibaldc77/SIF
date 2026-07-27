<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Exceptions\InvalidScopeIdentifierException;

final readonly class ScopeIdentifier
{
    public function __construct(
        private string $value,
    ) {
        if (trim($this->value) === '') {
            throw new InvalidScopeIdentifierException(
                'Scope identifier cannot be empty.',
            );
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
