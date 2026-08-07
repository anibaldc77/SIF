<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

final readonly class FederatedProviderRevocationAssessment
{
    public function __construct(
        private FederatedProviderRevocationOutcome $outcome,
        private bool $retryable,
        private bool $terminal
    ) {
    }

    public function outcome(): FederatedProviderRevocationOutcome
    {
        return $this->outcome;
    }

    public function retryable(): bool
    {
        return $this->retryable;
    }

    public function terminal(): bool
    {
        return $this->terminal;
    }
}
