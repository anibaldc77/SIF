<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use DateTimeImmutable;

final readonly class FederatedRevocationRetryState
{
    public function __construct(
        private int $attempts,
        private ?DateTimeImmutable $nextEligibleAt
    ) {
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function nextEligibleAt(): ?DateTimeImmutable
    {
        return $this->nextEligibleAt;
    }
}
