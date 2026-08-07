<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use DateTimeImmutable;

final readonly class FederatedRevocationRetryAssessment
{
    public function __construct(
        private bool $allowed,
        private FederatedRevocationRetryState $state,
        private ?string $reason = null
    ) {
    }

    public function allowed(): bool
    {
        return $this->allowed;
    }

    public function state(): FederatedRevocationRetryState
    {
        return $this->state;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    public function eligibleAt(DateTimeImmutable $now): bool
    {
        $next = $this->state->nextEligibleAt();

        return $this->allowed
            && ($next === null || $next <= $now);
    }
}
