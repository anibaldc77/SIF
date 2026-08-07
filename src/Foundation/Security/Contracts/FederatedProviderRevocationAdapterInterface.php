<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCapability;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationOutcome;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;

interface FederatedProviderRevocationAdapterInterface
{
    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedProviderRevocationCapability $capability,
        FederatedRevocationReason $reason
    ): FederatedProviderRevocationOutcome;
}
