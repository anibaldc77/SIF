<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

final readonly class FederatedProviderRevocationOutcome
{
    private function __construct(
        private bool $succeeded,
        private ?FederatedRemoteFailure $failure
    ) {
    }

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(FederatedRemoteFailure $failure): self
    {
        return new self(false, $failure);
    }

    public function succeeded(): bool
    {
        return $this->succeeded;
    }

    public function failureDetail(): ?FederatedRemoteFailure
    {
        return $this->failure;
    }

    public function retryable(): bool
    {
        return $this->failure?->retryable() ?? false;
    }
}
