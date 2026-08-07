<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;

final readonly class FederatedProviderRevocationCoordinator
{
    public function __construct(
        private FederatedProviderRevocationService $service,
        private FederatedProviderRevocationPolicy $policy
    ) {
    }

    public function execute(
        OidcFederatedIdentity $federatedIdentity,
        FederatedProviderRevocationCapability $capability,
        FederatedRevocationReason $reason
    ): FederatedProviderRevocationAssessment {
        $outcome = $this->service->revoke(
            $federatedIdentity,
            $capability,
            $reason
        );

        return new FederatedProviderRevocationAssessment(
            $outcome,
            $this->policy->retryable($outcome),
            $this->policy->terminal($outcome)
        );
    }
}
