<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

final readonly class FederatedRevocationResumePlanner
{
    public function plan(
        FederatedRevocationRequest $request,
        ?FederatedRevocationExecution $previous
    ): FederatedRevocationResumePlan {
        $planned = $this->plannedSteps($request->scope());

        if ($previous === null) {
            return new FederatedRevocationResumePlan($planned);
        }

        $completed = [];

        foreach ($previous->steps() as $step) {
            if ($step->succeeded()) {
                $completed[$step->step()->value] = true;
            }
        }

        $remaining = array_values(
            array_filter(
                $planned,
                static fn (FederatedRevocationStep $step): bool =>
                    !isset($completed[$step->value])
            )
        );

        return new FederatedRevocationResumePlan($remaining);
    }

    /**
     * @return list<FederatedRevocationStep>
     */
    private function plannedSteps(
        FederatedRevocationScope $scope
    ): array {
        return match ($scope) {
            FederatedRevocationScope::LocalSessions => [
                FederatedRevocationStep::LocalSessions,
            ],
            FederatedRevocationScope::ProviderCredentials => [
                FederatedRevocationStep::ProviderCredentials,
            ],
            FederatedRevocationScope::IdentityLink => [
                FederatedRevocationStep::IdentityLink,
            ],
            FederatedRevocationScope::All => [
                FederatedRevocationStep::LocalSessions,
                FederatedRevocationStep::ProviderCredentials,
                FederatedRevocationStep::IdentityLink,
            ],
        };
    }
}
