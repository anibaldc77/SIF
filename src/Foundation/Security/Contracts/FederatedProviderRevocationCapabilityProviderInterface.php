<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCapabilities;

interface FederatedProviderRevocationCapabilityProviderInterface
{
    public function capabilitiesFor(
        OidcFederatedIdentity $federatedIdentity
    ): FederatedProviderRevocationCapabilities;
}
