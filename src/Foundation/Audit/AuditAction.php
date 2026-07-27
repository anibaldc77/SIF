<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Exceptions\InvalidAuditActionException;

final readonly class AuditAction
{
    private const PATTERN = '/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/';

    public function __construct(
        private string $value,
    ) {
        if (trim($this->value) === '') {
            throw new InvalidAuditActionException('Audit action cannot be empty.');
        }

        if (preg_match(self::PATTERN, $this->value) !== 1) {
            throw new InvalidAuditActionException(
                sprintf('Invalid audit action "%s".', $this->value),
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
