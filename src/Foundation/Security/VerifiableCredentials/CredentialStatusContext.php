<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use DateTimeImmutable;

final readonly class CredentialStatusContext
{
    public function __construct(
        private DateTimeImmutable $evaluatedAt,
        private bool $requireStatusCheck = true,
        private bool $requireFreshStatus = true,
        private int $maximumStatusAgeSeconds = 300
    ) {
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function requireStatusCheck(): bool
    {
        return $this->requireStatusCheck;
    }

    public function requireFreshStatus(): bool
    {
        return $this->requireFreshStatus;
    }

    public function maximumStatusAgeSeconds(): int
    {
        return $this->maximumStatusAgeSeconds;
    }
}
