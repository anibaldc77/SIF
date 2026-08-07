<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

use Sif\Foundation\Security\Contracts\FederatedProviderRevocationAdapterInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderRevocationCapabilityProviderInterface;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;

final readonly class FederatedProviderRevocationService
{
    public function __construct(
        private FederatedProviderRevocationCapabilityProviderInterface $capabilityProvider,
        private FederatedProviderRevocationAdapterInterface $adapter
    ) {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedProviderRevocationCapability $capability,
        FederatedRevocationReason $reason
    ): FederatedProviderRevocationOutcome {
        $capabilities = $this->capabilityProvider->capabilitiesFor($federatedIdentity);

        if (!$capabilities->supports($capability)) {
            return FederatedProviderRevocationOutcome::failure(
                new FederatedRemoteFailure(
                    FederatedRemoteFailureKind::Unsupported,
                    'provider.capability_unsupported'
                )
            );
        }

        return $this->adapter->revoke(
            $federatedIdentity,
            $capability,
            $reason
        );
    }
}
