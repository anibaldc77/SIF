<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Exceptions\InvalidAuditIdException;

final readonly class AuditId
{
    public function __construct(
        private string $value,
    ) {
        if (trim($this->value) === '') {
            throw new InvalidAuditIdException('Audit identifier cannot be empty.');
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
