<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final readonly class AuthenticationEvidence
{
    private DateTimeImmutable $authenticatedAt;

    public function __construct(
        private AuthenticationMethod $method,
        private AuthenticationLevel $level,
        DateTimeImmutable $authenticatedAt
    ) {
        $this->authenticatedAt = $authenticatedAt->setTimezone(new DateTimeZone('UTC'));
    }

    public function method(): AuthenticationMethod
    {
        return $this->method;
    }

    public function level(): AuthenticationLevel
    {
        return $this->level;
    }

    public function authenticatedAt(): DateTimeImmutable
    {
        return $this->authenticatedAt;
    }

    /** @return array{method: string, level: int, authenticated_at: string} */
    public function toArray(): array
    {
        return [
            'method' => $this->method->value(),
            'level' => $this->level->value(),
            'authenticated_at' => $this->authenticatedAt->format(DateTimeInterface::RFC3339_EXTENDED),
        ];
    }
}
