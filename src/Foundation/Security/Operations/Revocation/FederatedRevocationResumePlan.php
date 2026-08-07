<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

final readonly class FederatedRevocationResumePlan
{
    /**
     * @param list<FederatedRevocationStep> $remainingSteps
     */
    public function __construct(
        private array $remainingSteps
    ) {
    }

    /** @return list<FederatedRevocationStep> */
    public function remainingSteps(): array
    {
        return $this->remainingSteps;
    }

    public function complete(): bool
    {
        return $this->remainingSteps === [];
    }
}
