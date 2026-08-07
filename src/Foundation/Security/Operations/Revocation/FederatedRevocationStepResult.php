<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

final readonly class FederatedRevocationStepResult
{
    public function __construct(
        private FederatedRevocationStep $step,
        private bool $attempted,
        private bool $succeeded,
        private ?string $failureType = null
    ) {
    }

    public function step(): FederatedRevocationStep
    {
        return $this->step;
    }

    public function attempted(): bool
    {
        return $this->attempted;
    }

    public function succeeded(): bool
    {
        return $this->succeeded;
    }

    public function failureType(): ?string
    {
        return $this->failureType;
    }
}
