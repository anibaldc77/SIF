<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

final readonly class FederatedProviderRevocationPolicy
{
    public function __construct(
        private bool $allowUnsupportedAsTerminal = false
    ) {
    }

    public function allowUnsupportedAsTerminal(): bool
    {
        return $this->allowUnsupportedAsTerminal;
    }

    public function retryable(
        FederatedProviderRevocationOutcome $outcome
    ): bool {
        return !$outcome->succeeded()
            && $outcome->retryable();
    }

    public function terminal(
        FederatedProviderRevocationOutcome $outcome
    ): bool {
        if ($outcome->succeeded()) {
            return true;
        }

        $failure = $outcome->failureDetail();

        if ($failure === null) {
            return true;
        }

        if (
            $failure->kind()
            === FederatedRemoteFailureKind::Unsupported
        ) {
            return $this->allowUnsupportedAsTerminal;
        }

        return !$failure->retryable();
    }
}
