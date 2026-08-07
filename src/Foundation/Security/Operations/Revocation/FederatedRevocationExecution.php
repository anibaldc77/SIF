<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

final readonly class FederatedRevocationExecution
{
    /**
     * @param list<FederatedRevocationStepResult> $steps
     */
    public function __construct(
        private FederatedRevocationRequest $request,
        private array $steps
    ) {
    }

    public function request(): FederatedRevocationRequest
    {
        return $this->request;
    }

    /** @return list<FederatedRevocationStepResult> */
    public function steps(): array
    {
        return $this->steps;
    }

    public function succeeded(): bool
    {
        foreach ($this->steps as $step) {
            if ($step->attempted() && !$step->succeeded()) {
                return false;
            }
        }

        return true;
    }

    public function result(): FederatedRevocationResult
    {
        $local = false;
        $provider = false;
        $link = false;

        foreach ($this->steps as $step) {
            if (!$step->succeeded()) {
                continue;
            }

            match ($step->step()) {
                FederatedRevocationStep::LocalSessions => $local = true,
                FederatedRevocationStep::ProviderCredentials => $provider = true,
                FederatedRevocationStep::IdentityLink => $link = true,
            };
        }

        return new FederatedRevocationResult(
            $local,
            $provider,
            $link
        );
    }
}
