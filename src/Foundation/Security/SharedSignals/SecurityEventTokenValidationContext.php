<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class SecurityEventTokenValidationContext
{
    public function __construct(
        private string $expectedIssuer,
        private string $expectedAudience,
        private DateTimeImmutable $evaluatedAt,
        private int $allowedClockSkewSeconds = 60
    ) {
        if (
            trim($this->expectedIssuer) === ''
            || trim($this->expectedAudience) === ''
            || $this->allowedClockSkewSeconds < 0
        ) {
            throw new InvalidArgumentException(
                'Security event token validation context is invalid.'
            );
        }
    }

    public function expectedIssuer(): string
    {
        return $this->expectedIssuer;
    }

    public function expectedAudience(): string
    {
        return $this->expectedAudience;
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function allowedClockSkewSeconds(): int
    {
        return $this->allowedClockSkewSeconds;
    }
}
