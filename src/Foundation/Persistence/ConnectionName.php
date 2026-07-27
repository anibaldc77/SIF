<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Exceptions\InvalidConnectionNameException;

final readonly class ConnectionName
{
    public function __construct(
        private string $value,
    ) {
        if (trim($this->value) === '') {
            throw new InvalidConnectionNameException(
                'Connection name cannot be empty.',
            );
        }
    }

    public static function default(): self
    {
        return new self('default');
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
